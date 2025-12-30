<?php
include "bootstrap.php";
include "db_connect.php";
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
?>
<title>HTOR BLS - New Book</title>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 500px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
<?php
$bookName = trim(addslashes($_POST["bookName"]));
$bookCategory = trim(addslashes($_POST["bookCategory"]));
$additionalNotes = trim(addslashes($_POST["additionalNotes"]));
$bookId = strtoupper(preg_replace('/\s+/', '', trim(addslashes($_POST["bookId"]))));

  echo "<h2>Creating a new entry:</h2><br><h4>Book Name: $bookName<br>Book Category: $bookCategory<br>Additional Notes: $additionalNotes<br>Book ID: $bookId</h4>";
  $sql = "SELECT * FROM booklist WHERE bookId = '$bookId'";
  $result = $mysqli->query($sql);
  $check = false;
  if($result->num_rows > 0){
    $check = true;
  }
  if ($check){
    echo "<div class='alert alert-danger' role='alert'>Error: Book ID Already Exists</div>";
  }
  else{
    $sql = "INSERT INTO booklist (bookId, bookName, bookCategory, additionalNotes) VALUES ('$bookId', '$bookName', '$bookCategory', '$additionalNotes')";
    $result = $mysqli->query($sql);
  }

if ( isset($_FILES["file"]["type"]) && $_FILES["file"]["type"] != "" && !$check )
{
<<<<<<< HEAD
  $destination_directory = "/var/www/library.htor.org/images/books/";
=======
  $destination_directory = "C:/wamp64/www/htorbls/images/books/";
>>>>>>> 53ddf2d6c3cb09f1628af6831206b9b0a8e0bc4e
  $validextensions = array("jpeg", "jpg", "png");
  $temporary = explode(".", $_FILES["file"]["name"]);
  $file_extension = end($temporary);

  // We need to check for image format and size again, because client-side code can be altered
  if ( (($_FILES["file"]["type"] == "image/png") ||
        ($_FILES["file"]["type"] == "image/jpg") ||
        ($_FILES["file"]["type"] == "image/jpeg")
       ) && in_array($file_extension, $validextensions))
  {
      if ( $_FILES["file"]["error"] > 0 )
      {
        echo "<div class=\"alert alert-danger\" role=\"alert\">Error: <strong>" . $_FILES["file"]["error"] . "</strong></div>";
      }
      else
      {
          $sourcePath = $_FILES["file"]["tmp_name"];
          $targetPath = $destination_directory . $bookId . ".jpeg";
          $imgAddress = "http://library.htor.org/images/books/" . $bookId . ".jpeg";
          $info = getimagesize($sourcePath);
          if ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($sourcePath);
          }
          else {
            $image = imagecreatefromjpeg($sourcePath);
          }
          $size = getimagesize($sourcePath);
          $nWidth = ceil(($size[0]*500)/$size[1]);
          $dst=imagecreatetruecolor($nWidth,500);
          imagecopyresampled($dst,$image,0,0,0,0,$nWidth,500,$size[0],$size[1]);
          $exif=@exif_read_data($sourcePath,'IFD0');
          if(is_array($exif)){
            if ($exif['Orientation'] == 3) {
              $dst=imagerotate($dst, 180, 0);
            }
            if ($exif['Orientation'] == 8) {
              $dst=imagerotate($dst, 90, 0);
            }
            if ($exif['Orientation'] == 6) {
              $dst=imagerotate($dst, -90, 0);
            }
          }
          imagejpeg($dst, $targetPath, 85);

          echo "<div class=\"alert alert-success\" role=\"alert\">Image uploaded successfully</div>";
      }
  }
  else
  {
    echo "<div class=\"alert alert-danger\" role=\"alert\">Unvalid image format. Allowed formats: JPG, JPEG, PNG.</div>";
  }
}
else
{
  echo "<div class=\"alert alert-danger\" role=\"alert\">Image not uploaded</div>";
}

?>
<button onclick="window.location.href='./book_list.php';" id="showlog" name="showlog" class="btn btn-orange">Show Book List</button>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
</div>
</body>
<?php
}
else {
  header("Location: login.php");
}
?>