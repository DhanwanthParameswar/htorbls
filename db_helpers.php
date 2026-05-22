<?php

function h($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function normalize_book_id($bookId) {
  return strtoupper(preg_replace('/\s+/', '', trim((string)$bookId)));
}

function normalize_patron_name($patronName) {
  return ucwords(strtolower(trim((string)$patronName)));
}

function db_select(mysqli $mysqli, string $sql, string $types = '', array $params = []) {
  if ($types === '') {
    return $mysqli->query($sql);
  }

  $stmt = $mysqli->prepare($sql);
  if (!$stmt) {
    return false;
  }

  $stmt->bind_param($types, ...$params);
  if (!$stmt->execute()) {
    $stmt->close();
    return false;
  }

  $result = $stmt->get_result();
  $stmt->close();
  return $result;
}

function db_execute(mysqli $mysqli, string $sql, string $types = '', array $params = []) {
  if ($types === '') {
    return $mysqli->query($sql) !== false;
  }

  $stmt = $mysqli->prepare($sql);
  if (!$stmt) {
    return false;
  }

  $stmt->bind_param($types, ...$params);
  $ok = $stmt->execute();
  $stmt->close();
  return $ok;
}

function db_scalar(mysqli $mysqli, string $sql, string $types = '', array $params = []) {
  $result = db_select($mysqli, $sql, $types, $params);
  if (!$result) {
    return null;
  }
  $row = $result->fetch_row();
  return $row ? $row[0] : null;
}

function db_fetch_one(mysqli $mysqli, string $sql, string $types = '', array $params = []) {
  $result = db_select($mysqli, $sql, $types, $params);
  if (!$result) {
    return null;
  }
  return $result->fetch_assoc() ?: null;
}

function db_mysqli_error_code(mysqli $mysqli) {
  return $mysqli->errno;
}
