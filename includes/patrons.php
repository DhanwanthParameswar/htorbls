<?php

require_once __DIR__ . '/../db_helpers.php';

function patron_normalize_contact(string $contactInfo): string {
  $contactInfo = trim($contactInfo);
  $strippedNumber = preg_replace('/[^0-9]/', '', $contactInfo);

  if (strlen($strippedNumber) === 7 || strlen($strippedNumber) === 10) {
    if (strlen($strippedNumber) === 7) {
      $strippedNumber = '585' . $strippedNumber;
    }
    return sprintf('(%s) %s-%s',
      substr($strippedNumber, 0, 3),
      substr($strippedNumber, 3, 3),
      substr($strippedNumber, 6, 4)
    );
  }

  return $contactInfo;
}

function patron_phone_normalized(string $contactInfo): ?string {
  $digits = preg_replace('/[^0-9]/', '', $contactInfo);
  if (strlen($digits) === 7) {
    $digits = '585' . $digits;
  }
  if (strlen($digits) === 10) {
    return $digits;
  }
  return null;
}

function patron_validate_first_last_name(string $patronName): ?string {
  $normalized = normalize_patron_name($patronName);
  $parts = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);
  if (count($parts) < 2) {
    return 'Please enter both a first and last name.';
  }
  return null;
}

function patron_name_from_parts(string $firstName, string $lastName): string {
  return normalize_patron_name(trim($firstName) . ' ' . trim($lastName));
}

function patron_prepare_identity(string $patronName, string $contactInfo): array {
  $patronName = normalize_patron_name($patronName);
  $contactInfo = patron_normalize_contact($contactInfo);
  $phoneNormalized = patron_phone_normalized($contactInfo);
  return [$patronName, $contactInfo, $phoneNormalized];
}

function patron_find_by_identity(mysqli $mysqli, string $patronName, string $contactInfo): ?array {
  [$patronName, $contactInfo, $phoneNormalized] = patron_prepare_identity($patronName, $contactInfo);

  if ($phoneNormalized !== null) {
    return db_fetch_one($mysqli,
      'SELECT * FROM patrons WHERE patronName = ? AND phoneNormalized = ? LIMIT 1',
      'ss',
      [$patronName, $phoneNormalized]
    );
  }

  return db_fetch_one($mysqli,
    'SELECT * FROM patrons WHERE patronName = ? AND contactInfo = ? LIMIT 1',
    'ss',
    [$patronName, $contactInfo]
  );
}

function patron_get(mysqli $mysqli, int $id): ?array {
  if ($id <= 0) {
    return null;
  }
  return db_fetch_one($mysqli, 'SELECT * FROM patrons WHERE id = ?', 'i', [$id]);
}

function patron_search_digits_like(string $query): ?string {
  $digits = preg_replace('/[^0-9]/', '', $query);
  if (strlen($digits) < 3) {
    return null;
  }
  return '%' . $digits . '%';
}

function patron_search(mysqli $mysqli, string $query, int $limit = 15): array {
  $query = trim($query);
  if ($query === '') {
    return [];
  }
  $like = '%' . $query . '%';
  $phoneLike = patron_search_digits_like($query);

  if ($phoneLike !== null) {
    $result = db_select($mysqli,
      'SELECT id, patronName, contactInfo, active
       FROM patrons
       WHERE active = 1 AND (
         patronName LIKE ?
         OR contactInfo LIKE ?
         OR phoneNormalized LIKE ?
       )
       ORDER BY patronName ASC
       LIMIT ?',
      'sssi',
      [$like, $like, $phoneLike, $limit]
    );
  } else {
    $result = db_select($mysqli,
      'SELECT id, patronName, contactInfo, active
       FROM patrons
       WHERE active = 1 AND (patronName LIKE ? OR contactInfo LIKE ?)
       ORDER BY patronName ASC
       LIMIT ?',
      'ssi',
      [$like, $like, $limit]
    );
  }

  if (!$result) {
    return [];
  }
  return $result->fetch_all(MYSQLI_ASSOC);
}

function patron_create(mysqli $mysqli, string $patronName, string $contactInfo, string $notes = ''): ?int {
  if (patron_validate_first_last_name($patronName) !== null) {
    return null;
  }
  [$patronName, $contactInfo, $phoneNormalized] = patron_prepare_identity($patronName, $contactInfo);
  $existing = patron_find_by_identity($mysqli, $patronName, $contactInfo);
  if ($existing) {
    return (int)$existing['id'];
  }

  if (!db_execute($mysqli,
    'INSERT INTO patrons (patronName, contactInfo, phoneNormalized, notes) VALUES (?, ?, ?, ?)',
    'ssss',
    [$patronName, $contactInfo, $phoneNormalized ?? '', $notes]
  )) {
    return null;
  }
  return (int)$mysqli->insert_id;
}

function patron_update(mysqli $mysqli, int $id, string $patronName, string $contactInfo, string $notes, bool $active): bool {
  [$patronName, $contactInfo, $phoneNormalized] = patron_prepare_identity($patronName, $contactInfo);
  $phoneParam = $phoneNormalized ?? '';
  return db_execute($mysqli,
    'UPDATE patrons SET patronName = ?, contactInfo = ?, phoneNormalized = NULLIF(?, \'\'), notes = ?, active = ? WHERE id = ?',
    'ssssii',
    [$patronName, $contactInfo, $phoneParam, $notes, $active ? 1 : 0, $id]
  );
}

function patron_books_out_count(mysqli $mysqli, int $patronId): int {
  return (int)db_scalar($mysqli, 'SELECT COUNT(*) FROM librarylog WHERE patron_id = ?', 'i', [$patronId]);
}

function patron_books_out(mysqli $mysqli, int $patronId): array {
  $result = db_select($mysqli,
    'SELECT bookId, issueDate, dueDate, fineAmount FROM librarylog WHERE patron_id = ? ORDER BY dueDate ASC',
    'i',
    [$patronId]
  );
  return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function patron_history(mysqli $mysqli, int $patronId, int $limit = 100): array {
  $result = db_select($mysqli,
    'SELECT bookId, issueDate, dueDate, returnDate, fineAmountPaid
     FROM libraryarchive WHERE patron_id = ?
     ORDER BY returnDate DESC LIMIT ?',
    'ii',
    [$patronId, $limit]
  );
  return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function patron_total_checkouts(mysqli $mysqli, int $patronId): int {
  $active = (int)db_scalar($mysqli, 'SELECT COUNT(*) FROM librarylog WHERE patron_id = ?', 'i', [$patronId]);
  $archived = (int)db_scalar($mysqli, 'SELECT COUNT(*) FROM libraryarchive WHERE patron_id = ?', 'i', [$patronId]);
  return $active + $archived;
}

function patron_display_name(array $patron): string {
  return $patron['patronName'] ?? '';
}

function patron_display_label(array $patron): string {
  $contact = trim($patron['contactInfo'] ?? '');
  if ($contact !== '') {
    return patron_display_name($patron) . ' — ' . $contact;
  }
  return patron_display_name($patron);
}

function patron_log_display_join_sql(string $logAlias = 'l'): string {
  return "LEFT JOIN patrons p ON {$logAlias}.patron_id = p.id";
}

function patron_select_name_sql(string $logAlias = 'l'): string {
  return "COALESCE(p.patronName, {$logAlias}.patronName) AS patronName";
}

function patron_select_contact_sql(string $logAlias = 'l'): string {
  return "COALESCE(p.contactInfo, {$logAlias}.contactInfo) AS contactInfo";
}

function patron_render_picker(string $inputId = 'patronSearch', string $hiddenId = 'patron_id', ?int $selectedId = null, ?array $selectedPatron = null, string $newPatronReturn = 'patrons.php'): void {
  if ($newPatronReturn !== 'index.php' && $newPatronReturn !== 'patrons.php') {
    $newPatronReturn = 'patrons.php';
  }
  $selectedId = $selectedId ?? 0;
  $label = $selectedPatron ? patron_display_name($selectedPatron) : '';
  ?>
  <div class="patron-picker text-start" id="patronPicker">
    <input type="hidden" name="patron_id" id="<?= h($hiddenId) ?>" value="<?= $selectedId > 0 ? (int)$selectedId : '' ?>" required>
    <div class="form-floating mb-1">
      <input type="text" class="form-control input-md" id="<?= h($inputId) ?>" placeholder="Patron" autocomplete="off" value="<?= h($label) ?>">
      <label for="<?= h($inputId) ?>">Patron</label>
    </div>
    <div id="patronSearchResults" class="list-group patron-search-results" style="display:none;"></div>
    <div id="patronSelectedPreview" class="alert alert-secondary mt-2 mb-2 py-2" style="display:none;">
      <span id="patronSelectedLabel" class="small"><?= h($label) ?></span>
      <button type="button" class="btn btn-sm btn-outline-secondary float-end" id="patronClearBtn">Change</button>
    </div>
    <small class="text-muted d-block mb-3">Search by name or contact. Can't find them? <a href="patron_edit.php?new=1&amp;return=<?= h($newPatronReturn) ?>">Add new patron</a></small>
  </div>
  <?php
}

function patron_picker_script(): void {
  ?>
  <script>
  (function() {
    const searchInput = document.getElementById('patronSearch');
    const hiddenInput = document.getElementById('patron_id');
    const resultsBox = document.getElementById('patronSearchResults');
    const preview = document.getElementById('patronSelectedPreview');
    const previewLabel = document.getElementById('patronSelectedLabel');
    const clearBtn = document.getElementById('patronClearBtn');
    if (!searchInput || !hiddenInput) return;

    let debounceTimer = null;

    function selectPatron(id, name) {
      hiddenInput.value = id;
      searchInput.value = name;
      previewLabel.textContent = name;
      preview.style.display = 'none';
      resultsBox.style.display = 'none';
      searchInput.setCustomValidity('');
    }

    function clearPatron() {
      hiddenInput.value = '';
      searchInput.value = '';
      preview.style.display = 'none';
      searchInput.focus();
    }

    clearBtn?.addEventListener('click', clearPatron);

    searchInput.addEventListener('input', function() {
      hiddenInput.value = '';
      preview.style.display = 'none';
      const q = searchInput.value.trim();
      if (q.length < 1) {
        resultsBox.style.display = 'none';
        return;
      }
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function() {
        fetch('patron_search.php?q=' + encodeURIComponent(q))
          .then(r => r.json())
          .then(function(items) {
            resultsBox.innerHTML = '';
            if (!items.length) {
              resultsBox.innerHTML = '<div class="list-group-item text-muted">No patrons found</div>';
            } else {
              items.forEach(function(item) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action patron-search-result';
                const nameEl = document.createElement('span');
                nameEl.className = 'patron-search-result-name d-block';
                nameEl.textContent = item.name;
                btn.appendChild(nameEl);
                if (item.contact) {
                  const contactEl = document.createElement('span');
                  contactEl.className = 'patron-search-result-contact d-block text-muted';
                  contactEl.textContent = item.contact;
                  btn.appendChild(contactEl);
                }
                btn.addEventListener('click', function() { selectPatron(item.id, item.name); });
                resultsBox.appendChild(btn);
              });
            }
            resultsBox.style.display = 'block';
          });
      }, 250);
    });

    document.addEventListener('click', function(e) {
      if (!document.getElementById('patronPicker')?.contains(e.target)) {
        resultsBox.style.display = 'none';
      }
    });

    const form = searchInput.closest('form');
    form?.addEventListener('submit', function(e) {
      if (!hiddenInput.value) {
        searchInput.setCustomValidity('Please select a patron from the list.');
        searchInput.reportValidity();
        e.preventDefault();
      }
    });
  })();
  </script>
  <?php
}
