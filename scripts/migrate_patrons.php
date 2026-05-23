<?php
/**
 * One-time patron migration. Run from repo root after backup:
 *   php scripts/migrate_patrons.php
 */
$root = dirname(__DIR__);
require_once $root . '/db_connect.php';
require_once $root . '/includes/patrons.php';

function column_exists(mysqli $mysqli, string $table, string $column): bool {
  $row = db_fetch_one($mysqli,
    'SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
    'ss',
    [$table, $column]
  );
  return $row && (int)$row['c'] > 0;
}

echo "HTOR BLS — Patron migration\n";

$tableExists = db_fetch_one($mysqli,
  "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'patrons'"
);
if (!$tableExists || (int)$tableExists['c'] === 0) {
  echo "ERROR: patrons table missing. Run as DB admin:\n";
  echo "  mysql library < docs/schema/patrons.sql\n";
  echo "  (and ALTER TABLE for patron_id columns if needed)\n";
  exit(1);
}
echo "Schema: patrons table present.\n";

foreach (['librarylog', 'libraryarchive'] as $table) {
  if (!column_exists($mysqli, $table, 'patron_id')) {
    if (!$mysqli->query("ALTER TABLE {$table} ADD COLUMN patron_id INT NULL, ADD INDEX idx_{$table}_patron_id (patron_id)")) {
      die("Failed to add patron_id to {$table}: " . $mysqli->error . "\n");
    }
    echo "Added patron_id to {$table}.\n";
  }
}

$identities = [];
$result = $mysqli->query(
  'SELECT DISTINCT patronName, contactInfo FROM (
     SELECT patronName, contactInfo FROM librarylog
     UNION
     SELECT patronName, contactInfo FROM libraryarchive
   ) AS u WHERE patronName IS NOT NULL AND patronName != \'\''
);
while ($row = $result->fetch_assoc()) {
  [$name, $contact] = patron_prepare_identity($row['patronName'], $row['contactInfo']);
  $key = $name . "\0" . ($contact);
  if (!isset($identities[$key])) {
    $identities[$key] = [$name, $contact];
  }
}

$patronMap = [];
$created = 0;
foreach ($identities as $key => [$name, $contact]) {
  $existing = patron_find_by_identity($mysqli, $name, $contact);
  if ($existing) {
    $patronMap[$key] = (int)$existing['id'];
  } else {
    $id = patron_create($mysqli, $name, $contact);
    if ($id) {
      $patronMap[$key] = $id;
      $created++;
    }
  }
}
echo "Patrons in registry: " . count($patronMap) . " (new: {$created})\n";

$logLinked = 0;
$result = $mysqli->query('SELECT id, patronName, contactInfo FROM librarylog');
while ($row = $result->fetch_assoc()) {
  [$name, $contact] = patron_prepare_identity($row['patronName'], $row['contactInfo']);
  $key = $name . "\0" . $contact;
  if (!isset($patronMap[$key])) {
    continue;
  }
  if (db_execute($mysqli, 'UPDATE librarylog SET patron_id = ? WHERE id = ?', 'ii', [$patronMap[$key], $row['id']])) {
    $logLinked++;
  }
}

$arcLinked = 0;
$result = $mysqli->query('SELECT id, patronName, contactInfo FROM libraryarchive');
while ($row = $result->fetch_assoc()) {
  [$name, $contact] = patron_prepare_identity($row['patronName'], $row['contactInfo']);
  $key = $name . "\0" . $contact;
  if (!isset($patronMap[$key])) {
    continue;
  }
  if (db_execute($mysqli, 'UPDATE libraryarchive SET patron_id = ? WHERE id = ?', 'ii', [$patronMap[$key], $row['id']])) {
    $arcLinked++;
  }
}

$unlinkedLog = (int)db_scalar($mysqli, 'SELECT COUNT(*) FROM librarylog WHERE patron_id IS NULL');
$unlinkedArc = (int)db_scalar($mysqli, 'SELECT COUNT(*) FROM libraryarchive WHERE patron_id IS NULL');

echo "librarylog rows linked: {$logLinked}\n";
echo "libraryarchive rows linked: {$arcLinked}\n";
echo "Unlinked log rows: {$unlinkedLog}\n";
echo "Unlinked archive rows: {$unlinkedArc}\n";
echo "Done.\n";
