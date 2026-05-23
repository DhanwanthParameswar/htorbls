<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include "bootstrap.php";
include "db_connect.php";
require_once __DIR__ . '/includes/patrons.php';
?>
<title>HTOR BLS - Edit Entry</title>
</head>
<body style="width: 100%; min-height: 100vh; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 750px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
    <h1 class="text-center pb-2 display-4">Edit Entry</h1>
    <h6 class="text-center text-muted">Note: Fines are based off of Dates.</h6>
<?php
$bookId = normalize_book_id($_GET["edit"] ?? '');

$row = db_fetch_one($mysqli,
  "SELECT l.id, l.patron_id, l.patronName, l.contactInfo, l.bookId, l.issueDate, l.dueDate
   FROM librarylog l WHERE l.bookId = ?",
  's',
  [$bookId]
);

if ($row) {
  $id = (int)$row['id'];
  $patronId = (int)($row['patron_id'] ?? 0);
  $selectedPatron = $patronId > 0 ? patron_get($mysqli, $patronId) : null;
  if (!$selectedPatron) {
    $selectedPatron = ['id' => 0, 'patronName' => $row['patronName'], 'contactInfo' => $row['contactInfo']];
  }
  $issueDate = $row['issueDate'];
  $dueDate = $row['dueDate'];
  $bookId = $row['bookId'];
?>
<form class="form-horizontal text-start needs-validation" action="edit_complete.php" novalidate>
<input type="hidden" name="log_id" value="<?= (int)$id ?>">

<div class="form-floating mb-3">
  <input id="bookId" name="bookId" type="text" class="form-control input-md" required value="<?= h($bookId) ?>">
  <label for="bookId">Book ID</label>
</div>

<?php patron_render_picker('patronSearch', 'patron_id', $patronId > 0 ? $patronId : null, $selectedPatron); ?>

<div class="form-floating mb-3">
  <input id="issueDate" name="issueDate" type="date" class="form-control input-md" required value="<?= h($issueDate) ?>">
  <label for="issueDate">Issue Date</label>
</div>

<div class="form-floating mb-3">
  <input id="dueDate" name="dueDate" type="date" class="form-control input-md" required value="<?= h($dueDate) ?>">
  <label for="dueDate">Due Date</label>
</div>

<div class="form-group">
  <button id="submit" name="submit" class="btn btn-orange" value="<?= (int)$id ?>">Edit</button>
</div>
</form>
<?php patron_picker_script(); ?>

<?php
} else {
  echo "<h4>Does Not Exist</h4>";
}
?>
<hr>
<button onclick="window.location.href='./index.php';" class="btn btn-orange">Return Home</button>
</div>
</body>
