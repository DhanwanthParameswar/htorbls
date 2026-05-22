<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include "bootstrap.php";
include "db_connect.php";
?>
<?php
$bookName = trim($_POST["bookName"] ?? '');
$bookCategory = trim($_POST["bookCategory"] ?? '');
$additionalNotes = trim($_POST["additionalNotes"] ?? '');
$bookId = normalize_book_id($_POST["bookId"] ?? '');
?>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 500px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">

<?php
// search the database for the search parameter
echo "<h2>Book Edit Results</h2>";

if ( isset($_FILES["file"]["type"]) && $_FILES["file"]["type"] != "" )
{
  $destination_directory = "/var/www/library.htor.org/images/books/";
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
          $targetPath = $destination_directory . $bookId . "." . substr($_FILES["file"]["type"], 6);
          $imgAddress = "http://library.htor.org/images/books/" . $bookId . "." . substr($_FILES["file"]["type"], 6);
          move_uploaded_file($sourcePath, $targetPath);

          echo "<div class=\"alert alert-success\" role=\"alert\">Image uploaded successful</div>";
      }
  }
  else
  {
    echo "<div class=\"alert alert-danger\" role=\"alert\">Unvalid image format. Allowed formats: JPG, JPEG, PNG.</div>";
  }
}

db_execute($mysqli,
  "UPDATE booklist SET bookName = ?, bookCategory = ?, additionalNotes = ? WHERE bookId = ?",
  'ssss',
  [$bookName, $bookCategory, $additionalNotes, $bookId]
);

?>
<button onclick="window.location.href='./book_list.php';" id="showbooklist" name="showbooklist" class="btn btn-orange">Show Book List</button>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
</div>
</body>