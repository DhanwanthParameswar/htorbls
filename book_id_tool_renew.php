<?php
include "bootstrap.php";
include "db_connect.php";
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
$bookId = addslashes($_GET["renew"]);
?>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 400px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">

<?php
$sql = "CREATE TEMPORARY TABLE tmp_value AS SELECT dueDate FROM librarylog WHERE bookId = '$bookId'";
$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
$sql = "UPDATE librarylog SET dueDate = DATE_ADD((SELECT dueDate FROM tmp_value), INTERVAL 1 WEEK) WHERE bookId = '$bookId'";
$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
?>
<h4>Item Renewed</h4>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
</div>
</body>
<?php
}
else {
  header("Location: login.php");
}
?>