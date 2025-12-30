<?php
session_start();
include "bootstrap.php";
include "db_connect.php";

if (isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
?>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HTOR BLS</title>
  </head>
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
  <body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
      <div class="container text-center" style="width: 1000px; background: #fff; border-radius: 10px; overflow: hidden; padding: 77px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
        <h1 class="text-center pb-2 display-4"><img class="htorlogo" src="./images/htorlogo.svg" alt="HTOR Logo" width="100" height="100"> Balvihar Library System (BLS)</h1>
        <div class="row justify-content-center">
          <div class="col-auto d-flex align-items-center justify-content-center">
            <h4 class="mb-0"><i class="bi bi-person-fill"></i> <b><?=$_SESSION['user_username']?></b></h4>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href="logout.php" class="btn btn-danger ml-4">Logout</a>
            &nbsp;&nbsp;&nbsp;
            <a href="help.php" class="btn btn-orange ml-4">Help/Support</a>
          </div>
        </div>
        <hr>
        <button onclick="window.location.href='./library_log.php';" id="showlog" name="showlog" class="btn btn-orange">Log</button>
        &nbsp;
        <button onclick="window.location.href='./library_archive.php';" id="showarchive" name="showarchive" class="btn btn-orange">Archive</button>
        &nbsp;
        <button onclick="window.location.href='./book_list.php';" id="booklist" name="booklist" class="btn btn-orange">Book List</button>
        <hr>

<ul class="nav nav-pills nav-fill mb-3" id="pills-tab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-newentry" type="button" role="tab" aria-controls="pills-newentry" aria-selected="true">New Entry</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-bookidtool" type="button" role="tab" aria-controls="pills-bookidtool" aria-selected="false">Book ID Tool</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-newbook" type="button" role="tab" aria-controls="pills-newbook" aria-selected="false">New Book</button>
  </li>
</ul>
<div class="tab-content" id="pills-tabContent">
  <div class="tab-pane fade show active" id="pills-newentry" role="tabpanel" aria-labelledby="pills-newentry" tabindex="0">
            <form class="form-horizontal text-start needs-validation" action="new_entry.php" novalidate>
          <div class="form-floating mb-3">
            <input id="patronName" name="patronName" type="text" placeholder="Enter Name" class="form-control input-md" required="">
            <label for="patronName">Patron Name</label>
          </div>
          <div class="form-floating mb-3">
            <input id="contactInfo" name="contactInfo" type="text" placeholder="e.g. Phone Number, Parent Name, Class" class="form-control input-md" required="">
            <label for="contactInfo">Contact Info</label>
          </div>
          <div class="form-floating mb-3">
            <input id="bookId" name="bookId" type="text" placeholder="Enter Book ID(s)" class="form-control input-md" required="">
            <small class="text-muted">You can enter multiple Book ID's by separating them with a comma ","</small>
            <label for="bookId">Enter Book ID(s)</label>
          </div>
          <div class="form-group">
            <label for="submit"></label>
            <button id="submit" name="submit" class="btn btn-orange">Create</button>
          </div>
        </form>
  </div>
  <div class="tab-pane fade" id="pills-bookidtool" role="tabpanel" aria-labelledby="pills-bookidtool" tabindex="0">
        <form class="form-horizontal text-start needs-validation" action="book_id_tool.php" novalidate>
          <div class="form-floating mb-3">
            <input id="bookId" name="bookId" type="text" placeholder="Enter Book ID" class="form-control input-md" required="">
            <label for="bookId">Enter a Book ID</label>
          </div>
          <div class="form-group">
            <label for="submit"></label>
            <button id="submit" name="submit" class="btn btn-orange">Check</button>
          </div>
        </form>
        <br>
        <hr>
        <br>
        <h3>Last Book ID Lookup</h3>
        <br>
        <form class="form-horizontal text-start needs-validation" action="book_id_lookup.php" novalidate>
          <div class="form-floating mb-3">
            <input id="prefix" name="prefix" type="text" placeholder="Enter Book ID Prefix" class="form-control input-md" required="">
            <label for="bookId">Enter a Book ID Prefix</label>
            <small class="text-muted">The prefix characters of a Book ID are the first alphabetic characters in a Book ID before the hyphen. Ex. <b>ABC</b>-123-1</small>
          </div>
          <div class="form-group">
            <label for="submit"></label>
            <button id="submit" name="submit" class="btn btn-orange">Lookup</button>
          </div>
        </form>
  </div>
  <div class="tab-pane fade" id="pills-newbook" role="tabpanel" aria-labelledby="pills-newbook" tabindex="0">
            <form class="form-horizontal text-start needs-validation" method="post" action="new_book.php" enctype='multipart/form-data'novalidate>
              <div class='mb-3 text-center' id='image-preview-div'>
  <label id='imgNew' for='exampleInputFile' style='display: none'><b>Image:</b></label>
  <br>
  <img id='previewImg' src='none' style='max-height: 500px; display: none' class='rounded img-fluid'>
</div>

<div class='mb-3 form-group'>
  <label for='file'>Add Image</label>
  <div class='input-group'>
    <input type='file' name='file' id='fileInput' style='color: black' class='form-control input-md' onclick='fileClicked(event)' onchange='fileChanged(event)' required=''>
    
    <div class='input-group-append'>
      <input class='btn btn-outline-secondary' type='button' value='Remove' name='remove-file' id='remove-file' onclick='fileRemove()'>
    </div>
  </div>
</div>

<div id='message'></div>

<br>
          <div class="form-floating mb-3">
            <input id="bookId" name="bookId" type="text" placeholder="Enter Book ID" class="form-control input-md" required="">
            <label for="bookId">Book ID</label>
          </div>
          <div class="form-floating mb-3">
            <input id="bookName" name="bookName" type="text" placeholder="Enter Book Name" class="form-control input-md" required="">
            <label for="bookName">Book Name</label>
          </div>
          <div class="form-floating mb-3">
            <input id="bookCategory" list='categories' name="bookCategory" type="text" placeholder="Enter Book Category" class="form-control input-md">
            <datalist id='categories'>
            <?php
            $categoryQuery = "SELECT DISTINCT bookCategory FROM booklist WHERE bookCategory IS NOT NULL AND bookCategory != '' ORDER BY bookCategory";
            $categoryResult = $mysqli->query($categoryQuery);
            
            while($category = $categoryResult->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($category['bookCategory']) . "'>";
            }
            ?>
            </datalist>
            <small class="text-muted">Optional - Choose an existing category from the dropdown menu when possible.</small>
            <label for="bookCategory">Book Category</label>
          </div>
          <div class="form-floating mb-3">
            <input id="additionalNotes" name="additionalNotes" type="text" placeholder="Enter Additional Notes" class="form-control input-md">
            <small class="text-muted">Optional</small>
            <label for="additionalNotes">Additional Notes</label>
          </div>
          <div class="form-group">
            <label for="submit"></label>
            <input id='submitBook' name='submit' type='submit' class='btn btn-orange' value='Create'>
          </div>
        </form>
  </div>
</div>
      </div>
    </main>
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
          $('#submitBook').attr('disabled', '');
          $('#imgNew').css("display", "none");
          $('#previewImg').css("display", "none");
          return false;
        }
        $('#submitBook').removeAttr("disabled");
        if (file) {
          previewImg.src = URL.createObjectURL(file);
        }
        $('#imgNew').css("display", "inline");
        $('#previewImg').css("display", "inline");
    }

    // FileRemove()
    function fileRemove() {
        document.forms[3].elements[0].value = '';
        //What ever else you want to do when File Removed
          $('#imgNew').css("display", "none");
          $('#previewImg').css("display", "none")
        $('#message').empty();
        $('#submitBook').removeAttr("disabled");
        
    }
</script>
  </body>
</html>
<?php
$mysqli->close();
}
else {
  header("Location: login.php");
}
?>