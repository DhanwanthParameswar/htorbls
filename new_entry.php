<?php
include "bootstrap.php";
include "db_connect.php";
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
?>
<title>HTOR BLS - New Entry</title>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 500px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
<?php
$patronName = ucwords(strtolower(trim(addslashes($_GET["patronName"]))));
$contactInfo = trim(addslashes($_GET["contactInfo"]));
$bookId = strtoupper(preg_replace('/\s+/', '', trim(addslashes($_GET["bookId"]))));
$bookId = explode(",", $bookId);
$bookIdView = implode(", ", $bookId);

$strippedNumber = preg_replace('/[^0-9]/', '', $contactInfo);

if (strlen($strippedNumber) == 7 || strlen($strippedNumber) == 10) {
    // If 7 digits, add default area code 585
    if (strlen($strippedNumber) == 7) {
        $strippedNumber = '585' . $strippedNumber;
    }
    
    // Format the number as (###) ###-####
    $contactInfo = sprintf("(%s) %s-%s",
        substr($strippedNumber, 0, 3),
        substr($strippedNumber, 3, 3),    // Fixed: removed 'senumber'
        substr($strippedNumber, 6, 4)
    );
}

// search the database for the search parameter
echo "<h2>Creating a new entry:</h2><br><h4>Name: $patronName<br>Contact Info: $contactInfo<br>Book ID(s): $bookIdView</h4>";

foreach($bookId as $value) {
	$sql = "INSERT INTO librarylog (patronName, contactInfo, bookId, issueDate, dueDate, fineAmount) VALUES ('$patronName', '$contactInfo', '$value', CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 1 WEEK), '0.00')";
	try {
		$result = $mysqli->query($sql);
		echo "<div class='alert alert-success' role='alert'>Successfully added <b>$value</b> to the log.</div>";
	} catch(Exception $e) {
		if (strpos($e, 'Duplicate entry') !== false) {
      echo "<div class='alert alert-danger' role='alert'>Error: Duplicate entry, <b>$value</b> has not been added to the log.</div>";
    } elseif (strpos($e, 'Cannot add or update a child row: a foreign key constraint fails') !== false) {
      echo "<div class='alert alert-danger' role='alert'>Error: Book ID does not exist, <b>$value</b> has not been added to the log. Check if the Book ID is correct. Otherwise, create a New Book entry on the home page and then resubmit this form.</div>";
    } else {
      echo "<div class='alert alert-danger' role='alert'>Error: $e</div>";
    }
	}

}
?>
<button onclick="window.location.href='./library_log.php';" id="showlog" name="showlog" class="btn btn-orange">Show Log</button>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
</div>
</body>
<?php
}
else {
  header("Location: login.php");
}
?>