<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include 'db_connect.php';
require_once __DIR__ . '/includes/patrons.php';

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$items = patron_search($mysqli, $q, 15);
$out = [];
foreach ($items as $row) {
  $out[] = [
    'id' => (int)$row['id'],
    'name' => patron_display_name($row),
    'contact' => trim($row['contactInfo'] ?? ''),
  ];
}
echo json_encode($out);
