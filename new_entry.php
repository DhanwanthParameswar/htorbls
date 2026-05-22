<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include "bootstrap.php";
include "db_connect.php";
?>
<title>HTOR BLS - New Entry</title>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 500px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
<?php
$patronName = normalize_patron_name($_GET["patronName"] ?? '');
$contactInfo = trim($_GET["contactInfo"] ?? '');
$bookIdRaw = normalize_book_id($_GET["bookId"] ?? '');
$bookIds = array_filter(array_map('normalize_book_id', explode(',', $bookIdRaw)));
$bookIdView = implode(", ", $bookIds);

$strippedNumber = preg_replace('/[^0-9]/', '', $contactInfo);

if (strlen($strippedNumber) == 7 || strlen($strippedNumber) == 10) {
    if (strlen($strippedNumber) == 7) {
        $strippedNumber = '585' . $strippedNumber;
    }

    $contactInfo = sprintf("(%s) %s-%s",
        substr($strippedNumber, 0, 3),
        substr($strippedNumber, 3, 3),
        substr($strippedNumber, 6, 4)
    );
}

echo "<h2>Creating a new entry:</h2><br><h4>Name: " . h($patronName) . "<br>Contact Info: " . h($contactInfo) . "<br>Book ID(s): " . h($bookIdView) . "</h4>";

$sql = "INSERT INTO librarylog (patronName, contactInfo, bookId, issueDate, dueDate, fineAmount) VALUES (?, ?, ?, CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 1 WEEK), '0.00')";

foreach ($bookIds as $value) {
  if ($value === '') {
    continue;
  }
  if (db_execute($mysqli, $sql, 'sss', [$patronName, $contactInfo, $value])) {
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
