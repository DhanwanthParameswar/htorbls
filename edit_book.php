<?php
include "bootstrap.php";
include "db_connect.php";
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
if(array_key_exists('submit', $_POST)) {
?>
<title>HTOR BLS - Book Edit Results</title>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div id="page" class="container text-center" style="width: 750px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
<?php
$bookName = trim(addslashes($_POST["bookName"]));
$bookCategory = trim(addslashes($_POST["bookCategory"]));
$additionalNotes = trim(addslashes($_POST["additionalNotes"]));
$bookId = strtoupper(preg_replace('/\s+/', '', trim(addslashes($_POST["bookId"]))));
$bookIdOld = addslashes($_POST["bookIdOld"]);
$id = addslashes($_POST["id"]);
// search the database for the search parameter
echo "<h2>Book Edit Results</h2>";

  $sql = "SELECT * FROM booklist WHERE bookId = '$bookId'";
  $result = $mysqli->query($sql);
  $check = false;
  if($result->num_rows > 0 && $bookId != $bookIdOld){
    $check = true;
  }
  if ($check){
    echo "<div class='alert alert-danger' role='alert'>Error: Book ID is being used by another book, book not edited.</div>";
  }
  else{
    $sql = "UPDATE booklist SET bookId = '$bookId', bookName = '$bookName', bookCategory = '$bookCategory', additionalNotes = '$additionalNotes' WHERE id = '$id'";
    $result = $mysqli->query($sql);

    echo "<div class=\"alert alert-success\" role=\"alert\">Book edited successfully</div>";
  }

if ( isset($_FILES["file"]["type"]) && $_FILES["file"]["type"] != ""  && !$check )
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
          $oldFilename = "/var/www/library.htor.org/images/books/" . $bookIdOld . ".jpeg";
          if(file_exists($oldFilename)){
            unlink($oldFilename);
          }

          $sourcePath = $_FILES["file"]["tmp_name"];
          $targetPath = $destination_directory . $bookId . ".jpeg";
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
else if (!$check && $bookId != $bookIdOld) {
    // Handle renaming the existing image file when the book ID is changed
    $oldImagePath = "/var/www/library.htor.org/images/books/" . $bookIdOld . ".jpeg";
    $newImagePath = "/var/www/library.htor.org/images/books/" . $bookId . ".jpeg";

    // Check if an image exists for the old book ID
    if (file_exists($oldImagePath)) {
        if (rename($oldImagePath, $newImagePath)) {
            echo "<div class=\"alert alert-success\" role=\"alert\">Book ID updated successfully, and associated image renamed.</div>";
        } else {
            echo "<div class=\"alert alert-warning\" role=\"alert\">Book ID updated, but failed to rename the associated image.</div>";
        }
    } else {
        echo "<div class=\"alert alert-info\" role=\"alert\">Book ID updated. No existing image found for renaming.</div>";
    }
}

?>
<button onclick="window.location.href='./book_list.php';" id="showbooklist" name="showbooklist" class="btn btn-orange">Show Book List</button>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
</div>
</body>
<?php
}
else {
?>
<style type="text/css">
  .btn-outline-secondary:active {
    color: black;
    border: var(--bs-border-width) solid var(--bs-border-color);
  }
  .btn-outline-secondary, .btn-outline-secondary:focus {
    background-color: #F8F9FA;
    color: #212529;
    box-shadow: none;
    border: var(--bs-border-width) solid var(--bs-border-color);
    --bs-border-radius: 0.375rem;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    font-size: 1rem;
    font-weight: 400;
    padding: .375rem .75rem;
    -webkit-transition: none !important;
    -moz-transition: none !important;
    -o-transition: none !important;
    transition: none !important;
  }
  .btn-outline-secondary:hover {
    background-color: #E9ECEF;
    border: var(--bs-border-width) solid var(--bs-border-color);
    color: #212529;
    -webkit-transition: none !important;
    -moz-transition: none !important;
    -o-transition: none !important;
    transition: none !important;
  }
</style>
<title>HTOR BLS - Edit Book</title>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div id="page" class="container text-center" style="width: 750px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
    <h1 class="text-center pb-2 display-4">Edit Book</h1>
<?php
$bookId = addslashes($_GET["edit"]);

$sql = "SELECT id, bookName, bookCategory, additionalNotes FROM booklist WHERE bookId = '$bookId'";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $id = $row["id"];
  	$bookName = $row["bookName"];
  	$bookCategory = $row["bookCategory"];
  	$additionalNotes = $row["additionalNotes"];
  }

$random = rand();

$categoryQuery = "SELECT DISTINCT bookCategory FROM booklist WHERE bookCategory IS NOT NULL AND bookCategory != '' ORDER BY bookCategory";
$categoryResult = $mysqli->query($categoryQuery);

$datalistOptions = "";
while($category = $categoryResult->fetch_assoc()) {
    $datalistOptions .= "<option value='" . htmlspecialchars($category['bookCategory']) . "'>";
}

  echo "
<form id='edit-form' class='form-horizontal text-start needs-validation' method='post' action='#' enctype='multipart/form-data' novalidate>

<div class='mb-3 text-center' id='image-preview-div'>
  <label id='imgCurrent' for='exampleInputFile' style='display: inline'><b>Current Image:</b></label>
  <label id='imgNew' for='exampleInputFile' style='display: none'><b>New Image:</b></label>
  <br>
  <img id='previewImg' src='https://library.htor.org/images/books/$bookId.jpeg?rand=$random' style='max-height: 500px' class='rounded img-fluid'>
</div>

<div class='mb-3 form-group'>
  <label for='file'>Change Image</label>
  <div class='input-group'>
    <input type='file' name='file' id='fileInput' style='color: black' class='form-control input-md' onclick='fileClicked(event)' onchange='fileChanged(event)'>
    
    <div class='input-group-append'>
      <input class='btn btn-outline-secondary' type='button' value='Remove' name='remove-file' id='remove-file' onclick='fileRemove()'>
    </div>
  </div>
</div>

<div id='message'></div>

<br>

<div class='form-floating mb-3'>
  <input id='bookId' name='bookId' type='text' placeholder='Enter Book ID' class='form-control input-md' required='' value='$bookId'>
  <label for='bookId'>Book ID</label>
</div>

<div class='form-floating mb-3'>
  <input id='bookName' name='bookName' type='text' placeholder='Enter Book Name' class='form-control input-md' required='' value='$bookName'>
  <label for='bookName'>Book Name</label>
</div>

<div class='form-floating mb-3'>
  <input id='bookCategory' list='categories' name='bookCategory' type='text' placeholder='Book Category' class='form-control input-md' value='$bookCategory'>
  <datalist id='categories'>
    $datalistOptions
  </datalist>
  <small class='text-muted'>Optional - Choose an existing category from the dropdown menu when possible.</small>
  <label for='bookCategory'>Book Category</label>
</div>

<div class='form-floating mb-3'>
  <input id='additionalNotes' name='additionalNotes' type='text' placeholder='Additional Notes' class='form-control input-md' value='$additionalNotes'>
  <small class='text-muted'>Optional</small>
  <label for='additionalNotes'>Additional Notes</label>
</div>

<input type='hidden' id='id' name='id' value='$id'>

<input type='hidden' id='bookIdOld' name='bookIdOld' value='$bookId'>

<div class='form-group'>
  <input id='submit' name='submit' type='submit' class='btn btn-orange' value='Edit'>
</div>";


} else {
  echo "<h4>Does Not Exist</h4>";
}
?>
<hr>
<a href="./index.php" class="btn btn-orange">Return Home</a>

</div>
<script type="text/javascript">
  // Example starter JavaScript for disabling form submissions if there are invalid fields
(() => {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  const forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }

      form.classList.add('was-validated')
    }, false)
  })
})()
</script>
<script type="text/javascript">
    var clone = {};

    // FileClicked()
    function fileClicked(event) {
        var fileElement = event.target;
        if (fileElement.value != "") {
            clone[fileElement.id] = $(fileElement).clone(); //'Saving Clone'
        }
        //What ever else you want to do when File Chooser Clicked
        
    }

    // FileChanged()
    function fileChanged(event) {
        var fileElement = event.target;
        if (fileElement.value == "") {
            clone[fileElement.id].insertBefore(fileElement); //'Restoring Clone'
            $(fileElement).remove(); //'Removing Original'
        }
        //What ever else you want to do when File Chooser Changed
        $('#message').empty();
        const [file] = fileInput.files
        var match = ["image/jpeg", "image/png", "image/jpg"];
        if ( !( (file.type == match[0]) || (file.type == match[1]) || (file.type == match[2]) ) ){
          $('#message').html('<div class="alert alert-danger" role="alert">Unvalid image format. Allowed formats: JPG, JPEG, PNG.</div>');
          $('#submit').attr('disabled', '');
          $('#imgCurrent').css("display", "inline");
          $('#imgNew').css("display", "none");
          previewImg.src = 'http://library.htor.org/images/books/<?php echo $bookId ?>.jpeg?rand=<?php echo rand(); ?>';
          return false;
        }
        $('#submit').removeAttr("disabled");
        if (file) {
          previewImg.src = URL.createObjectURL(file)
        }
        $('#imgCurrent').css("display", "none");
        $('#imgNew').css("display", "inline");
        
    }

    // FileRemove()
    function fileRemove() {
        document.forms[0].elements[0].value = '';
        //What ever else you want to do when File Removed
        $('#imgCurrent').css("display", "inline");
        $('#imgNew').css("display", "none");
        previewImg.src = 'http://library.htor.org/images/books/<?php echo $bookId ?>.jpeg?rand=<?php echo rand(); ?>';
        $('#message').empty();
        $('#submit').removeAttr("disabled");
        
    }
</script>
</body>
<?php
}
}
else {
  header("Location: login.php");
}
?>