<?php
/**
 * Fuzzy-merge duplicate patron records (same person, typos / short vs full name).
 *
 *   php scripts/merge_duplicate_patrons.php           # dry run (default)
 *   php scripts/merge_duplicate_patrons.php --execute   # apply merges
 */
$root = dirname(__DIR__);
require_once $root . '/db_connect.php';
require_once $root . '/includes/patrons.php';

$execute = in_array('--execute', $argv ?? [], true);

function patron_name_key(string $name): string {
  $name = strtolower(preg_replace('/[^a-z\s]/u', '', normalize_patron_name($name)));
  return preg_replace('/\s+/', ' ', trim($name));
}

function patron_first_token(string $name): string {
  $parts = explode(' ', patron_name_key($name));
  return $parts[0] ?? '';
}

function patron_name_similarity(string $a, string $b): float {
  $a = patron_name_key($a);
  $b = patron_name_key($b);
  if ($a === $b) {
    return 100.0;
  }
  similar_text($a, $b, $pct);
  return $pct;
}

function patron_name_is_extension(string $shortName, string $longName): bool {
  $s = patron_name_key($shortName);
  $l = patron_name_key($longName);
  if ($s === '' || $l === '') {
    return false;
  }
  if ($s === $l) {
    return true;
  }
  return str_starts_with($l, $s . ' ') || str_starts_with($l, $s);
}

function patron_first_names_similar(string $a, string $b): bool {
  $fa = patron_first_token($a);
  $fb = patron_first_token($b);
  if ($fa === $fb) {
    return true;
  }
  if ($fa === '' || $fb === '') {
    return false;
  }
  similar_text($fa, $fb, $pct);
  if ($pct >= 82) {
    return true;
  }
  if (strlen($fa) >= 3 && strlen($fb) >= 3 && levenshtein($fa, $fb) <= 2) {
    return true;
  }
  $short = strlen($fa) <= strlen($fb) ? $fa : $fb;
  $long = strlen($fa) > strlen($fb) ? $fa : $fb;
  if (strlen($short) >= 3 && str_starts_with($long, $short)) {
    return true;
  }
  return false;
}

/** Conservative: same phone + likely same person (not siblings on shared line). */
function patron_should_fuzzy_merge(array $a, array $b): bool {
  $phoneMatch = !empty($a['phoneNormalized'])
    && !empty($b['phoneNormalized'])
    && $a['phoneNormalized'] === $b['phoneNormalized'];

  $nameSim = patron_name_similarity($a['patronName'], $b['patronName']);
  $contactA = patron_normalize_contact($a['contactInfo']);
  $contactB = patron_normalize_contact($b['contactInfo']);

  if (!$phoneMatch) {
    return $nameSim >= 99 && $contactA === $contactB && $contactA !== '';
  }

  if ($nameSim >= 92) {
    return true;
  }

  if (
    (patron_name_is_extension($a['patronName'], $b['patronName'])
      || patron_name_is_extension($b['patronName'], $a['patronName']))
    && patron_first_names_similar($a['patronName'], $b['patronName'])
  ) {
    return true;
  }

  if ($nameSim >= 85 && patron_first_names_similar($a['patronName'], $b['patronName'])) {
    return true;
  }

  if ($contactA === $contactB && $nameSim >= 99) {
    return true;
  }

  return false;
}

class PatronUnionFind {
  private array $parent = [];

  public function find(int $id): int {
    if (!isset($this->parent[$id])) {
      $this->parent[$id] = $id;
    }
    if ($this->parent[$id] !== $id) {
      $this->parent[$id] = $this->find($this->parent[$id]);
    }
    return $this->parent[$id];
  }

  public function union(int $a, int $b): void {
    $ra = $this->find($a);
    $rb = $this->find($b);
    if ($ra !== $rb) {
      $this->parent[$rb] = $ra;
    }
  }
}

function patron_load_all_with_counts(mysqli $mysqli): array {
  $patrons = [];
  $result = $mysqli->query(
    'SELECT p.*,
      (SELECT COUNT(*) FROM librarylog l WHERE l.patron_id = p.id) AS log_c,
      (SELECT COUNT(*) FROM libraryarchive a WHERE a.patron_id = p.id) AS arc_c
     FROM patrons p ORDER BY p.id'
  );
  while ($row = $result->fetch_assoc()) {
    $row['checkout_c'] = (int)$row['log_c'] + (int)$row['arc_c'];
    $patrons[(int)$row['id']] = $row;
  }
  return $patrons;
}

function patron_find_merge_clusters(array $patrons): array {
  $uf = new PatronUnionFind();
  $ids = array_keys($patrons);

  foreach ($ids as $i => $ida) {
    foreach ($ids as $j => $idb) {
      if ($j <= $i) {
        continue;
      }
      if (patron_should_fuzzy_merge($patrons[$ida], $patrons[$idb])) {
        $uf->union($ida, $idb);
      }
    }
  }

  $clusters = [];
  foreach ($ids as $id) {
    $root = $uf->find($id);
    $clusters[$root][] = $id;
  }

  return array_values(array_filter($clusters, static fn(array $c) => count($c) > 1));
}

function patron_pick_keeper(array $patrons, array $memberIds): int {
  usort($memberIds, static function (int $a, int $b) use ($patrons): int {
    $pa = $patrons[$a];
    $pb = $patrons[$b];
    if ($pa['checkout_c'] !== $pb['checkout_c']) {
      return $pb['checkout_c'] <=> $pa['checkout_c'];
    }
    if ((int)$pa['active'] !== (int)$pb['active']) {
      return (int)$pb['active'] <=> (int)$pa['active'];
    }
    $lenCmp = strlen($pa['patronName']) <=> strlen($pb['patronName']);
    if ($lenCmp !== 0) {
      return $lenCmp < 0 ? 1 : -1;
    }
    return $a <=> $b;
  });
  return $memberIds[0];
}

function patron_merge_notes(array $patrons, array $memberIds): string {
  $notes = [];
  foreach ($memberIds as $id) {
    $n = trim((string)($patrons[$id]['notes'] ?? ''));
    if ($n !== '' && !in_array($n, $notes, true)) {
      $notes[] = $n;
    }
  }
  return implode("\n---\n", $notes);
}

function patron_merge_into(mysqli $mysqli, int $keeperId, int $duplicateId, array $keeperRow): bool {
  if ($keeperId === $duplicateId) {
    return true;
  }

  $mysqli->begin_transaction();
  try {
    if (!db_execute($mysqli, 'UPDATE librarylog SET patron_id = ? WHERE patron_id = ?', 'ii', [$keeperId, $duplicateId])) {
      throw new RuntimeException('librarylog update failed');
    }
    if (!db_execute($mysqli, 'UPDATE libraryarchive SET patron_id = ? WHERE patron_id = ?', 'ii', [$keeperId, $duplicateId])) {
      throw new RuntimeException('libraryarchive update failed');
    }
    if (!db_execute($mysqli, 'DELETE FROM patrons WHERE id = ?', 'i', [$duplicateId])) {
      throw new RuntimeException('patron delete failed');
    }
    $mysqli->commit();
    return true;
  } catch (Throwable $e) {
    $mysqli->rollback();
    throw $e;
  }
}

echo "HTOR BLS — Fuzzy patron merge" . ($execute ? " (EXECUTE)\n" : " (dry run)\n");

$patrons = patron_load_all_with_counts($mysqli);
$clusters = patron_find_merge_clusters($patrons);

if (count($clusters) === 0) {
  echo "No duplicate clusters found.\n";
  exit(0);
}

$totalMerged = 0;
foreach ($clusters as $memberIds) {
  $keeperId = patron_pick_keeper($patrons, $memberIds);
  $dupIds = array_values(array_filter($memberIds, static fn(int $id) => $id !== $keeperId));

  $keeper = $patrons[$keeperId];
  $mergedNotes = patron_merge_notes($patrons, $memberIds);

  echo "\nCluster (keeper #{$keeperId}):\n";
  foreach ($memberIds as $id) {
    $p = $patrons[$id];
    $tag = $id === $keeperId ? 'KEEP' : 'merge→';
    echo "  {$tag} #{$id} {$p['patronName']} | {$p['contactInfo']} | checkouts={$p['checkout_c']}\n";
  }

  if ($execute) {
  [$patronName, $contactInfo, $phoneNormalized] = patron_prepare_identity(
      $keeper['patronName'],
      $keeper['contactInfo']
    );
    patron_update($mysqli, $keeperId, $patronName, $contactInfo, $mergedNotes, (bool)$keeper['active']);

    foreach ($dupIds as $dupId) {
      patron_merge_into($mysqli, $keeperId, $dupId, $keeper);
      $totalMerged++;
      echo "  Merged #{$dupId} into #{$keeperId}\n";
    }
  }
}

echo "\n";
if ($execute) {
  echo "Done. Merged {$totalMerged} duplicate patron(s) across " . count($clusters) . " cluster(s).\n";
  echo 'Remaining patrons: ' . (int)db_scalar($mysqli, 'SELECT COUNT(*) FROM patrons') . "\n";
} else {
  $dupCount = array_sum(array_map(static fn($c) => count($c) - 1, $clusters));
  echo "Would merge {$dupCount} duplicate(s) in " . count($clusters) . " cluster(s).\n";
  echo "Run: php scripts/merge_duplicate_patrons.php --execute\n";
}
