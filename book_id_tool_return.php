<?php
include "bootstrap.php";
include "db_connect.php";
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
?>

<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 750px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
<?php
$bookId = addslashes($_GET["return"]);
if(array_key_exists('confirm', $_POST)) {
  $sql = "SELECT patronName, contactInfo, bookId, issueDate, dueDate, fineAmount FROM librarylog WHERE bookId = '$bookId'";
  $result = $mysqli->query($sql);

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
      $patronName = $row['patronName'];
      $contactInfo = $row['contactInfo'];
      $issueDate = $row['issueDate'];
      $dueDate = $row['dueDate'];
      $fineAmountPaid = $_POST['paid_amount'];
        $sql = "INSERT INTO libraryarchive (patronName, contactInfo, bookId, issueDate, dueDate, returnDate, fineAmountPaid) VALUES ('$patronName', '$contactInfo', '$bookId', '$issueDate', '$dueDate', CURRENT_DATE, '$fineAmountPaid')";
        $result = $mysqli->query($sql) or die(mysqli_error($mysqli));
      	$sql = "DELETE FROM librarylog WHERE bookId = '$bookId'";
        $result = $mysqli->query($sql) or die(mysqli_error($mysqli));
        echo "<h4>Item Returned</h4>";
?>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
<?php
      }
  }
else {
$sql = "SELECT fineAmount FROM librarylog WHERE bookId = '$bookId'";
$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $fineCheck = $row["fineAmount"];
  }
  if($fineCheck > "0")
  {
  	echo "<h4>This item has an outstanding fine of: $$fineCheck Edit the fine amount paid if necessary below.</h4>";
  	echo "
    <form method='post'>
    <div class='input-group' style='max-width: 200px; margin: 0 auto 1rem;'>
        <div class='input-group-prepend'>
            <span class='input-group-text'>$</span>
        </div>
        <input type='text' 
               class='form-control' 
               name='paid_amount'
               id='paid_amount'
               required
               pattern='^\d*\.?\d{0,2}$'
               placeholder='0.00'
               value='$fineCheck'
               aria-label='Amount (to the nearest dollar)'
               oninput='validateMoneyInput(this)'
               onkeypress='return isNumberKey(event)'>
        <div class='invalid-feedback'>
            Please enter the payment amount
        </div>
    </div>

    <button id='confirm' name='confirm' class='btn btn-orange' value='yes'>Confirm and Return</button>
    </form>
  	";
?>
<button onclick="window.location.href='./book_id_tool.php?bookId=<?php echo $bookId ?>';" id="showlog" name="showlog" class="btn btn-orange">Cancel</button>
<?php
  }
  else
  {
    $sql = "SELECT patronName, contactInfo, bookId, issueDate, dueDate, fineAmount FROM librarylog WHERE bookId = '$bookId'";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $patronName = $row['patronName'];
      $contactInfo = $row['contactInfo'];
      $issueDate = $row['issueDate'];
      $dueDate = $row['dueDate'];
        $sql = "INSERT INTO libraryarchive (patronName, contactInfo, bookId, issueDate, dueDate, returnDate, fineAmountPaid) VALUES ('$patronName', '$contactInfo', '$bookId', '$issueDate', '$dueDate', CURRENT_DATE, 0)";
          $result = $mysqli->query($sql);
          $sql = "DELETE FROM librarylog WHERE bookId = '$bookId'";
          $result = $mysqli->query($sql);
          echo "<h4>Item Returned</h4>";
          ?>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
<?php
    }
  }
} else {
  echo "<h4>Error: Item Doesn't Exist Anymore</h4>";
}
}
?>
</div>
<script>
function isNumberKey(evt) {
    const charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 46 && evt.target.value.indexOf('.') !== -1) {
        return false; // Prevent multiple decimal points
    }
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false; // Only allow numbers and decimal point
    }
    return true;
}

function validateMoneyInput(input) {
    // Remove any non-numeric characters except decimal point
    let value = input.value.replace(/[^\d.]/g, '');
    
    // Ensure only one decimal point
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limit to two decimal places
    if (parts.length > 1) {
        value = parts[0] + '.' + parts[1].slice(0, 2);
    }
    
    input.value = value;
}

// Bootstrap form validation
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
