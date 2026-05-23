<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include "bootstrap.php";
include "db_connect.php";
require_once __DIR__ . '/includes/patrons.php';

$patronId = (int)($_GET['patron_id'] ?? 0);
$bookIdRaw = normalize_book_id($_GET['bookId'] ?? '');
$bookIds = array_filter(array_map('normalize_book_id', explode(',', $bookIdRaw)));
$bookIdView = implode(', ', $bookIds);

$patron = patron_get($mysqli, $patronId);
if (!$patron || empty($patron['active'])) {
  header('Location: index.php?error=' . urlencode('Please select a valid active patron.'));
  exit;
}

$patronName = $patron['patronName'];
$contactInfo = $patron['contactInfo'];
?>
<title>HTOR BLS - New Entry</title>
</head>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 500px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
<?php
echo "<h2>Creating a new entry:</h2><br><h4>Patron: " . h(patron_display_label($patron)) . "<br>Book ID(s): " . h($bookIdView) . "</h4>";

$sql = "INSERT INTO librarylog (patron_id, patronName, contactInfo, bookId, issueDate, dueDate, fineAmount) VALUES (?, ?, ?, ?, CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 1 WEEK), '0.00')";

foreach ($bookIds as $value) {
  if ($value === '') {
    continue;
  }
  if (db_execute($mysqli, $sql, 'isss', [$patronId, $patronName, $contactInfo, $value])) {
    echo "<div class='alert alert-success' role='alert'>Successfully added <b>" . h($value) . "</b> to the log.</div>";
  } elseif (db_mysqli_error_code($mysqli) === 1062) {
    echo "<div class='alert alert-danger' role='alert'>Error: Duplicate entry, <b>" . h($value) . "</b> has not been added to the log.</div>";
  } elseif (db_mysqli_error_code($mysqli) === 1452) {
    echo "<div class='alert alert-danger' role='alert'>Error: Book ID does not exist, <b>" . h($value) . "</b> has not been added to the log. Check if the Book ID is correct. Otherwise, create a New Book entry on the home page and then resubmit this form.</div>";
  } else {
    echo "<div class='alert alert-danger' role='alert'>Error: Could not add <b>" . h($value) . "</b> to the log.</div>";
  }
}
?>
<button onclick="window.location.href='./library_log.php';" id="showlog" name="showlog" class="btn btn-orange">Show Log</button>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
</div>
</body>
