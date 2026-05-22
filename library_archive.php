<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include "db_connect.php";
include "bootstrap.php";
require_once __DIR__ . '/includes/layout.php';
$sql = "UPDATE librarylog SET fineAmount = CEIL(GREATEST(DATEDIFF(CURRENT_DATE, `dueDate`), 0)/7)*0.25";
$result = $mysqli->query($sql);
?>
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.css">
<title>HTOR BLS - Library Archive</title>
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
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
	  <div class="container text-center" style="width: 1200px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
	  	<h1 class="text-center display-4">Library Archive</h1>
      <small class='text-muted pb-2'>Note: Entries 6 Months after their return date are not shown.</small>
      <br>
      <br>

	<?php bls_render_subpage_toolbar('archive'); ?>

	<table id="example" class="table table-striped table-bordered table-hover" style="width:100%">
	<thead>
		<tr>
			<th>Patron Name</th>
			<th>Contact Info</th>
			<th>Book ID</th>
			<th>Issue Date</th>
			<th>Due Date</th>
			<th>Return Date</th>
      <th>Fine Amount Paid</th>
			<th>Quick Tools</th>
		</tr>
	</thead>
	<tbody>
<?php
if(array_key_exists('delete', $_POST)) {
	$bookId = normalize_book_id($_POST["delete"] ?? '');
	if ($bookId !== '' && db_execute($mysqli, "DELETE FROM libraryarchive WHERE bookId = ?", 's', [$bookId])) {
    echo "<div class='alert alert-danger' role='alert'>Item <b>" . h($bookId) . "</b> Deleted from Archive</div>";
  }
}
elseif (array_key_exists('add', $_POST)) {
  $bookId = normalize_book_id($_POST["add"] ?? '');
  $row = db_fetch_one($mysqli,
    "SELECT patronName, contactInfo, issueDate, dueDate FROM libraryarchive WHERE bookId = ?",
    's',
    [$bookId]
  );
  if ($row) {
    if (db_execute($mysqli,
      "INSERT INTO librarylog (patronName, contactInfo, bookId, issueDate, dueDate, fineAmount) VALUES (?, ?, ?, ?, ?, '0.00')",
      'sssss',
      [$row['patronName'], $row['contactInfo'], $bookId, $row['issueDate'], $row['dueDate']]
    )) {
      db_execute($mysqli, "DELETE FROM libraryarchive WHERE bookId = ?", 's', [$bookId]);
      echo "<div class='alert alert-success' role='alert'>Item <b>" . h($bookId) . "</b> Added to Log</div>";
    }
  }
}

$sql = "SELECT patronName, contactInfo, bookId, issueDate, dueDate, returnDate, fineAmountPaid FROM libraryarchive WHERE `returnDate` >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)";
$result = $mysqli->query($sql);

if(!empty($result)) {
if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $bookId = $row["bookId"];
    echo "<tr><td>". $row["patronName"] ."</td><td>". $row["contactInfo"] ."</td><td>". $bookId ."</td><td>". $row["issueDate"] ."</td><td>". $row["dueDate"] ."</td><td>". $row["returnDate"] ."</td><td> $" .$row["fineAmountPaid"] ."</td><td>
        <div class='btn-group' role='group'>

      <form method='post'>
        <button id='add' name='add' class='btn btn-success btn-sm' value='$bookId'><i class='bi bi-plus-circle-fill'></i></button>
            </form>

            &nbsp;

      <form method='post'>
      <button id='delete' name='delete' class='btn btn-danger btn-sm' value='$bookId'><i class='bi bi-trash-fill'></i></button>
      </form>

      </div>" ."</td></tr>";
  }
}
}
echo "</tbody>";
echo "</table>";
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
