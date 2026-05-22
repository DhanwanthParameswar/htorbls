<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include 'bootstrap.php';
include 'db_connect.php';

if (!isset($_FILES['file']['type'])) {
  exit;
}

$destination_directory = __DIR__ . '/upload/';
$validextensions = ['jpeg', 'jpg', 'png'];
$allowedMimes = ['image/png', 'image/jpg', 'image/jpeg'];

$temporary = explode('.', $_FILES['file']['name']);
$file_extension = strtolower(end($temporary));
$safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['file']['name']));

if (
  in_array($_FILES['file']['type'], $allowedMimes, true)
  && in_array($file_extension, $validextensions, true)
) {
  if ($_FILES['file']['error'] > 0) {
    echo '<div class="alert alert-danger" role="alert">Error: <strong>' . h((string)$_FILES['file']['error']) . '</strong></div>';
  } else {
    $targetPath = $destination_directory . $safeName;
    if (file_exists($targetPath)) {
      echo '<div class="alert alert-danger" role="alert">Error: File <strong>' . h($safeName) . '</strong> already exists.</div>';
    } elseif (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
      echo '<div class="alert alert-success" role="alert">';
      echo '<p>Image uploaded successfully</p>';
      echo '<p>File Name: <a href="upload/' . h($safeName) . '"><strong>' . h($safeName) . '</strong></a></p>';
      echo '</div>';
    } else {
      echo '<div class="alert alert-danger" role="alert">Upload failed.</div>';
    }
  }
} else {
  echo '<div class="alert alert-danger" role="alert">Invalid image format. Allowed formats: JPG, JPEG, PNG.</div>';
}

?>
