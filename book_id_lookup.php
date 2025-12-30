<?php
include "bootstrap.php";
include "db_connect.php";
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
?>
<title>HTOR BLS - Book ID Lookup</title>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 750px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
      <h1 class="text-center pb-2 display-4">Book ID Lookup</h1>
<?php
$prefix = strtoupper(preg_replace('/\s+/', '', trim(addslashes($_GET["prefix"]))));

$sql = "SELECT bookId FROM booklist WHERE bookId LIKE '$prefix-%'";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    $bookNumbers = [];
    $maxBookNumber = 0;

    // Extract the numeric part (middle section) of each bookId
    while ($row = $result->fetch_assoc()) {
        $bookId = $row['bookId'];
        $parts = explode('-', $bookId);
        
        if (is_numeric($parts[1])) {
            $bookNumber = intval($parts[1]);
            $bookNumbers[] = $bookNumber;
            
            if ($bookNumber > $maxBookNumber) {
                $maxBookNumber = $bookNumber;
            }
        }
    }

    // Sort the book numbers and check for gaps (holes)
    sort($bookNumbers);
    $holes = [];
    for ($i = 1; $i <= $maxBookNumber; $i++) {
        if (!in_array($i, $bookNumbers)) {
            $holes[] = $i;
        }
    }

    // Display the results
    echo "<h4>Book ID Prefix: <b>$prefix</b></h4>";

    // Display any holes in the book ID sequence
    if (!empty($holes)) {
        echo "<h5><b>NOTICE:</b> Missing Book IDs (Holes):</h5>";
        echo "<ul>";
        foreach ($holes as $hole) {
            echo "<li>$prefix-$hole-1</li>";
        }
        echo "</ul>";
    }

    // Display the next available book ID
    $nextBookNumber = $maxBookNumber + 1;
    echo "<h4>Next available Book ID: <b>$prefix-$nextBookNumber-1</b></h4>";
} else {
    echo "<h4>No books found for prefix <b>$prefix</b></h4>";
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