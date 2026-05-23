<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include "bootstrap.php";
include "db_connect.php";
require_once __DIR__ . '/includes/patrons.php';

$id = (int)($_GET["submit"] ?? 0);
$patronId = (int)($_GET["patron_id"] ?? 0);
$bookId = normalize_book_id($_GET["bookId"] ?? '');
$issueDate = $_GET["issueDate"] ?? '';
$dueDate = $_GET["dueDate"] ?? '';

$patron = patron_get($mysqli, $patronId);
if (!$patron || empty($patron['active'])) {
  header('Location: edit_entry.php?edit=' . urlencode($bookId));
  exit;
}

$patronName = $patron['patronName'];
$contactInfo = $patron['contactInfo'];
?>
<title>HTOR BLS - Edit Results</title>
</head>
<body style="width: 100%; min-height: 100vh; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 500px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">

<?php
echo "<h2>Edit Results</h2>";

if ($id > 0 && db_execute($mysqli,
  "UPDATE librarylog SET patron_id = ?, bookId = ?, patronName = ?, contactInfo = ?, issueDate = ?, dueDate = ? WHERE id = ?",
  'isssssi',
  [$patronId, $bookId, $patronName, $contactInfo, $issueDate, $dueDate, $id]
)) {
  echo "<div class='alert alert-success' role='alert'>Successfully edited entry!</div>";
} elseif (db_mysqli_error_code($mysqli) === 1062) {
  echo "<div class='alert alert-danger' role='alert'>Error: Duplicate Book ID exists in the log, entry has not been edited.</div>";
} elseif (db_mysqli_error_code($mysqli) === 1452) {
  echo "<div class='alert alert-danger' role='alert'>Error: Book ID does not exist, entry has not been edited.</div>";
} else {
  echo "<div class='alert alert-danger' role='alert'>Error: Entry could not be edited.</div>";
}

?>
<button onclick="window.location.href='./library_log.php';" class="btn btn-orange">Show Log</button>
<button onclick="window.location.href='./index.php';" class="btn btn-orange">Return Home</button>
</div>
</body>
