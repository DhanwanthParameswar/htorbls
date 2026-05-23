<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include 'db_connect.php';
include 'bootstrap.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/patrons.php';

$sql = "UPDATE librarylog SET fineAmount = CEIL(GREATEST(DATEDIFF(CURRENT_DATE, `dueDate`), 0)/7)*0.25";
$mysqli->query($sql);
?>
<?php require_once __DIR__ . '/includes/datatables_head.php'; ?>
<title>HTOR BLS - Patrons</title>
</head>
<body style="width: 100%; min-height: 100vh; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
  <div class="container text-center" style="width: 1200px; background: #fff; border-radius: 10px; padding: 33px 55px; box-shadow: 0 5px 10px rgba(0,0,0,0.1);">
    <h1 class="display-4">Patrons</h1>
    <?php bls_render_subpage_toolbar('patrons'); ?>
    <a href="patron_edit.php?new=1" class="btn btn-orange mb-3">Add Patron</a>
    <hr>
    <table id="patronsTable" class="table table-striped table-bordered table-hover" style="width:100%">
      <thead>
        <tr>
          <th>Name</th>
          <th>Contact</th>
          <th>Status</th>
          <th>Out</th>
          <th>Total</th>
          <th class="bls-quick-tools-cell">Quick Tools</th>
        </tr>
      </thead>
      <tbody>
<?php
$result = db_select($mysqli,
  'SELECT p.id, p.patronName, p.contactInfo, p.active,
    (SELECT COUNT(*) FROM librarylog l WHERE l.patron_id = p.id) AS booksOut,
    (SELECT COUNT(*) FROM librarylog l WHERE l.patron_id = p.id)
      + (SELECT COUNT(*) FROM libraryarchive a WHERE a.patron_id = p.id) AS totalCheckouts
   FROM patrons p
   ORDER BY p.patronName ASC'
);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $id = (int)$row['id'];
    $status = $row['active'] ? 'Active' : 'Inactive';
    echo '<tr>';
    echo '<td>' . h($row['patronName']) . '</td>';
    echo '<td>' . h($row['contactInfo']) . '</td>';
    echo '<td>' . h($status) . '</td>';
    echo '<td>' . (int)$row['booksOut'] . '</td>';
    echo '<td>' . (int)$row['totalCheckouts'] . '</td>';
    echo '<td class="bls-quick-tools-cell"><div class="bls-quick-tools" role="group">
      <a href="patron.php?id=' . $id . '" class="btn btn-orange btn-sm" title="View patron"><i class="bi bi-eye"></i></a>
      <a href="patron_edit.php?id=' . $id . '" class="btn btn-outline-secondary btn-sm" title="Edit patron"><i class="bi bi-pencil-square"></i></a>
    </div></td>';
    echo '</tr>';
  }
}
?>
      </tbody>
    </table>
  </div>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
<script>
$('#patronsTable').DataTable({
  order: [[0, 'asc']],
  columnDefs: [{ orderable: false, targets: 'bls-quick-tools-cell' }],
  layout: { topStart: { buttons: ['pageLength', 'copy', 'csv', 'excel', 'pdf', 'print'] } }
});
</script>
</body>
