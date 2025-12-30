<?php
include "bootstrap.php";
include "db_connect.php";
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
?>
<title>HTOR BLS - Edit Entry</title>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 750px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
    <h1 class="text-center pb-2 display-4">Edit Entry</h1>
    <h6 class="text-center text-muted">Note: Fines are based off of Dates.</h6>
<?php
$bookId = addslashes($_GET["edit"]);

$sql = "SELECT id, patronName, contactInfo, bookId, issueDate, dueDate, fineAmount FROM librarylog WHERE bookId = '$bookId'";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $id = $row["id"];
  	$patronName = $row["patronName"];
  	$contactInfo = $row["contactInfo"];
  	$issueDate = $row["issueDate"];
  	$dueDate = $row["dueDate"];
  }
  echo "

<form class='form-horizontal text-start needs-validation' action='edit_complete.php' novalidate>

<div class='form-floating mb-3'>
  <input id='bookId' name='bookId' type='text' placeholder='Enter Book ID' class='form-control input-md' required='' value='$bookId'>
  <label for='bookId'>Book ID</label>
</div>

<div class='form-floating mb-3'>
  <input id='patronName' name='patronName' type='text' placeholder='Enter Name' class='form-control input-md' required='' value='$patronName'>
  <label for='patronName'>Name</label>
</div>

<div class='form-floating mb-3'>
  <input id='contactInfo' name='contactInfo' type='text' placeholder='e.g. Phone Number, Parent Name, Class' class='form-control input-md' required='' value='$contactInfo'>
  <label for='contactInfo'>Contact Info</label>
</div>

<div class='form-floating mb-3'>
  <input id='issueDate' name='issueDate' type='date' placeholder='Enter Date' class='form-control input-md' required='' value='$issueDate'>
  <label for='issueDate'>Issue Date</label>
</div>

<div class='form-floating mb-3'>
  <input id='dueDate' name='dueDate' type='date' placeholder='Enter Date' class='form-control input-md' required='' value='$dueDate'>
  <label for='dueDate'>Due Date</label>
</div>

<div class='form-group'>
  <button id='submit' name='submit' class='btn btn-orange' value='$id'>Edit</button>
</div>

</form>";

} else {
  echo "<h4>Does Not Exist</h4>";
}
?>
<hr>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>

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
</body>
<?php
}
else {
  header("Location: login.php");
}
?>