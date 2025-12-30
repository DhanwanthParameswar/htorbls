<?php
include "bootstrap.php";
include "db_connect.php";
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
$sql = "UPDATE librarylog SET fineAmount = CEIL(GREATEST(DATEDIFF(CURRENT_DATE, `dueDate`), 0)/7)*0.25";
$result = $mysqli->query($sql);
?>
<title>HTOR BLS - Book ID Tool</title>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 750px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
      <h1 class="text-center pb-2 display-4">Book ID Tool</h1>
<?php
$bookId = strtoupper(preg_replace('/\s+/', '', trim(addslashes($_GET["bookId"]))));

echo "<h4>Entry for <b>$bookId</b></h4>";

$sql = "SELECT patronName, contactInfo, issueDate, dueDate, fineAmount FROM librarylog WHERE bookId = '$bookId'";
$result = $mysqli->query($sql);
$sql = "SELECT bookName, bookCategory, additionalNotes FROM booklist WHERE bookId = '$bookId'";
$result2 = $mysqli->query($sql);

if ($result->num_rows > 0 && $result2->num_rows > 0) {
  // output data of each row
    echo "<div class='alert alert-danger' role='alert'>Status: <b>Checked Out</b></div>";
    $row = $result->fetch_assoc();
    $row2 = $result2->fetch_assoc();
    if($row["dueDate"] === date("Y-m-d")) {
      $dueToday = " <b>(Due Today)</b>";
      $fineToday = $row["fineAmount"] + 0.25;
      $fineToday = " <b>($$fineToday)</b>";
    }
    else {
      $dueToday = "";
      $fineToday = "";
    }

    $datetest=$row["dueDate"];
    $date1=date_create($datetest);
    $date2=date_create(date("Y-m-d"));
    $dateDiff=date_diff($date1,$date2);
    $dateDiff=(int)$dateDiff->format("%r%a");
    if($dateDiff % 7 == 0 && $dateDiff >= 0) {
      $fineToday = $row["fineAmount"] + 0.25;
      $fineToday = " <b>($$fineToday)</b>";
    }
    else {
      $fineToday = "";
    }

    echo "
                  <div class='mb-3 text-center' id='image-preview-div'>
  <label id='imgNew' for='exampleInputFile'><b>Image:</b></label>
  <br>
  <img id='previewImg' src='http://library.htor.org/images/books/$bookId.jpeg?rand=" .rand(). "' style='max-height: 500px display: none' class='rounded img-fluid'>
</div>

    <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Patron Name</li>
      <li class='list-group-item'>" .$row['patronName']. "</li>
    </ul>
    <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Contact Info</li>
      <li class='list-group-item'>" .$row['contactInfo']. "</li>
    </ul>
    <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Issue Date</li>
      <li class='list-group-item'>" .$row['issueDate']. "</li>
    </ul>
    <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Due Date</li>
      <li class='list-group-item'>" .$row['dueDate']. $dueToday. "</li>
    </ul>
    <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Fine Amount</li>
      <li class='list-group-item'>" ."$" .$row['fineAmount']. $fineToday. "</li>
    </ul>
    <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Book Name</li>
      <li class='list-group-item'>" .$row2['bookName']. "</li>
    </ul>
    <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Book Category</li>
      <li class='list-group-item'>" .$row2['bookCategory']. "</li>
    </ul>
     <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Book Additional Notes</li>
      <li class='list-group-item'>" .$row2['additionalNotes']. "</li>
    </ul>";
    echo "
    <br>
      <div class='btn-group' role='group'>

    <form action='book_id_tool_return.php'>
    <button id='return' name='return' class='btn btn-orange' value='$bookId'>Return</button>
  </form>

      &nbsp;
      &nbsp;

  <form action='book_id_tool_renew.php'>
    <button id='renew' name='renew' class='btn btn-orange' value='$bookId'>Renew</button>
  </form>

      </div>
    ";
} elseif ($result2->num_rows > 0) {
    echo "<div class='alert alert-success' role='alert'>Status: <b>Available</b></div>";
    $row2 = $result2->fetch_assoc();
    echo "
                      <div class='mb-3 text-center' id='image-preview-div'>
  <label id='imgNew' for='exampleInputFile'><b>Image:</b></label>
  <br>
  <img id='previewImg' src='http://library.htor.org/images/books/$bookId.jpeg?rand=" .rand(). "' style='max-height: 500px display: none' class='rounded img-fluid'>
</div>
    <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Book Name</li>
      <li class='list-group-item'>" .$row2['bookName']. "</li>
    </ul>
    <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Book Category</li>
      <li class='list-group-item'>" .$row2['bookCategory']. "</li>
    </ul>
    <ul class='list-group list-group-horizontal'>
      <li class='list-group-item'>Book Additional Notes</li>
      <li class='list-group-item'>" .$row2['additionalNotes']. "</li>
    </ul>";
}

else {
  echo "<h4>Does Not Exist</h4>";
}

?>



<hr>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
</div>
</body>
<?php
}
else {
  header("Location: login.php");
}
?>