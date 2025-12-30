<?php
include "db_connect.php";
include "bootstrap.php";
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_username'])) {
$sql = "UPDATE librarylog SET fineAmount = CEIL(GREATEST(DATEDIFF(CURRENT_DATE, `dueDate`), 0)/7)*0.25";
$result = $mysqli->query($sql);
?>
<head>
  
  <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.css">

<title>HTOR BLS - Library Log</title>
<style type="text/css">
  .buttons-html5, .buttons-print, .buttons-page-length {
    padding: 5px;
    margin: 5px;
    box-shadow: none;
    border-radius: 5px !important;
    border: 1px solid #dee2e6;
    background-color: #fff;
    color: #000;
  }
</style>
</head>
  <div class="modal fade" id="reg-modal" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title w-100" id="modal-title">Confirm</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this record? Only delete if the Book ID is wrong or as a last resort. Otherwise, remove the record by returning the item in the Book ID Tool.</p>
          <form name='deleteform' class='text-center' id='deleteform' method='post'>
            <button id='delete' name='delete' class='btn btn-danger' value='idHolder'>Delete Record</button>
          </form>
        </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="renew-reg-modal" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title w-100" id="modal-title">Confirm</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <p>Are you sure you want to renew all books? You can also revert accidental renewals.</p>
          <form name='renewallform' class='text-center' id='renewallform' method='post'>
            <button id="revert" name="revert" class="btn btn-warning mb-3" value='confirmation'>Revert Renewal</button>
            &nbsp;&nbsp;
            <button id="renewall" name="renewall" class="btn btn-orange mb-3" value='confirmation'>Renew All</button>
          </form>
        </form>
        </div>
      </div>
    </div>
  </div>

<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
	  <div class="container text-center" style="width: 1200px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
	  	<h1 class="text-center pb-2 display-4">Library Log</h1>

	<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange mb-3">Return Home</button>

	<hr>

	<button class="btn btn-orange mb-3" data-bs-toggle='modal' data-bs-target='#renew-reg-modal'>Renew All (For Holiday Weeks)</button>

	<table id="example" class="table table-striped table-bordered table-hover" style="width:100%">
	<thead>
		<tr>
			<th>Patron Name</th>
			<th>Contact Info</th>
			<th>Book ID</th>
			<th>Issue Date</th>
			<th>Due Date</th>
			<th>Fine Amount</th>
			<th>Quick Tools</th>
		</tr>
	</thead>
	<tbody>
<?php
if(array_key_exists('renewall', $_POST)) {
	$sql = "UPDATE librarylog SET dueDate = DATE_ADD(dueDate, INTERVAL 1 WEEK)";
	$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
  echo "<div class='alert alert-success' role='alert'>All books renewed by 1 week</div>";
}

if(array_key_exists('revert', $_POST)) {
  $sql = "UPDATE librarylog SET dueDate = DATE_ADD(dueDate, INTERVAL -1 WEEK)";
  $result = $mysqli->query($sql) or die(mysqli_error($mysqli));
  echo "<div class='alert alert-success' role='alert'>All due dates reverted by 1 week</div>";
}

if(array_key_exists('delete', $_POST)) {
	$bookId = addslashes($_POST["delete"]);
	$sql = "DELETE FROM librarylog WHERE bookId = '$bookId'";
  $result = $mysqli->query($sql) or die(mysqli_error($mysqli));
  echo "<div class='alert alert-danger' role='alert'>Item <b>$bookId</b> Deleted from Log</div>";
}

$sql = "SELECT patronName, contactInfo, bookId, issueDate, dueDate, fineAmount FROM librarylog";
$result = $mysqli->query($sql);

if(!empty($result)) {
if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
  	$bookId = $row["bookId"];
  	if($row["dueDate"] === date("Y-m-d")) {
  		$dueToday = " <b>(Due Today)</b>";
  	}
  	else {
  		$dueToday = "";
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

    echo "<tr><td>". $row["patronName"] ."</td><td>". $row["contactInfo"] ."</td><td>". $row["bookId"] ."</td><td>". $row["issueDate"] ."</td><td>" .$row["dueDate"] . $dueToday ."</td><td>". "$" . $row["fineAmount"] . $fineToday ."</td><td>". "
    	<div class='btn-group' role='group'>

      <form action='book_id_tool.php'>
    	<button id='bookId' name='bookId' class='btn btn-orange btn-sm' value='$bookId'><i class='bi bi-tools'></i></button>
			</form>

			&nbsp;

      <form action='edit_entry.php'>
    	<button id='edit' name='edit' class='btn btn-orange btn-sm' value='$bookId'><i class='bi bi-pencil-square'></i></button>
			</form>

			&nbsp;

      <button class='delete-click btn btn-danger btn-sm' data-id='$bookId' data-bs-toggle='modal' data-bs-target='#reg-modal'><i class='bi bi-trash-fill'></i></button>

      </div>" ."</td></tr>";
  }
}
}
echo "</tbody>";
echo "</table>";
echo "</div>";
?>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
<script type="text/javascript">
$(document).on("click", ".delete-click", function () {
     var eventId = $(this).data('id');
     document.deleteform.delete.value = eventId;
});

$('#example').DataTable( {
  columnDefs: [
    { orderable: false, targets: 4 }
  ],
  order: [[1, 'asc']],
      layout: {
        topStart: {
            buttons: ['pageLength', 'copy', 'csv', 'excel', 'pdf', 'print']
        }
    }
});
</script>
</body>

 <?php
}
else {
  header("Location: login.php");
}
?>