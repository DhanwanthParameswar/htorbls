<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include 'bootstrap.php';
include 'db_connect.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/patrons.php';

$patronId = (int)($_GET['id'] ?? 0);
$patron = patron_get($mysqli, $patronId);
if (!$patron) {
  header('Location: patrons.php');
  exit;
}

$booksOut = patron_books_out($mysqli, $patronId);
$history = patron_history($mysqli, $patronId);
$totalCheckouts = patron_total_checkouts($mysqli, $patronId);
?>
<title>HTOR BLS - <?= h(patron_display_name($patron)) ?></title>
</head>
<body style="width: 100%; min-height: 100vh; display: flex; flex-wrap: wrap; justify-content: center; align-items: flex-start; padding: 15px; background: #F4CABC;">
  <div class="container text-center" style="width: 1100px; background: #fff; border-radius: 10px; padding: 33px 55px; box-shadow: 0 5px 10px rgba(0,0,0,0.1); margin: 15px 0;">
    <h1 class="display-5 pb-2"><?= h(patron_display_name($patron)) ?></h1>
    <?php if (empty($patron['active'])): ?>
      <div class="alert alert-warning" role="alert">Status: <b>Inactive</b></div>
    <?php else: ?>
      <div class="alert alert-success" role="alert">Status: <b>Active</b></div>
    <?php endif; ?>
    <?php bls_render_subpage_toolbar('patrons'); ?>
    <hr>

    <div class="mb-4">
      <ul class="list-group list-group-horizontal">
        <li class="list-group-item">Patron Name</li>
        <li class="list-group-item"><?= h($patron['patronName']) ?></li>
      </ul>
      <ul class="list-group list-group-horizontal">
        <li class="list-group-item">Contact Info</li>
        <li class="list-group-item"><?= h($patron['contactInfo']) ?></li>
      </ul>
      <?php if (trim($patron['notes'] ?? '') !== ''): ?>
        <ul class="list-group list-group-horizontal">
          <li class="list-group-item">Notes</li>
          <li class="list-group-item"><?= h($patron['notes']) ?></li>
        </ul>
      <?php endif; ?>
      <ul class="list-group list-group-horizontal">
        <li class="list-group-item">Total Checkouts</li>
        <li class="list-group-item"><?= (int)$totalCheckouts ?></li>
      </ul>
      <ul class="list-group list-group-horizontal">
        <li class="list-group-item">Books Currently Out</li>
        <li class="list-group-item"><?= count($booksOut) ?></li>
      </ul>
      <br>
      <div class="bls-quick-tools d-inline-flex" role="group">
        <a href="patron_edit.php?id=<?= (int)$patronId ?>" class="btn btn-outline-secondary btn-sm" title="Edit patron"><i class="bi bi-pencil-square"></i></a>
      </div>
    </div>

    <h4>Books currently out (<?= count($booksOut) ?>)</h4>
    <?php if (count($booksOut) === 0): ?>
      <p class="text-muted">No active checkouts.</p>
    <?php else: ?>
      <table class="table table-striped table-bordered table-sm">
        <thead><tr><th>Book ID</th><th>Issue Date</th><th>Due Date</th><th>Fine</th><th class="bls-quick-tools-cell">Quick Tools</th></tr></thead>
        <tbody>
          <?php foreach ($booksOut as $row): ?>
            <tr>
              <td><?= h($row['bookId']) ?></td>
              <td><?= h($row['issueDate']) ?></td>
              <td><?= h($row['dueDate']) ?></td>
              <td>$<?= h($row['fineAmount']) ?></td>
              <td class="bls-quick-tools-cell">
                <div class="bls-quick-tools" role="group">
                  <a href="book_id_tool.php?bookId=<?= urlencode($row['bookId']) ?>" class="btn btn-orange btn-sm" title="Book ID tool"><i class="bi bi-tools"></i></a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <h4 class="mt-4">Return history</h4>
    <?php if (count($history) === 0): ?>
      <p class="text-muted">No returns on record.</p>
    <?php else: ?>
      <table class="table table-striped table-bordered table-sm">
        <thead><tr><th>Book ID</th><th>Issue</th><th>Due</th><th>Returned</th><th>Fine paid</th></tr></thead>
        <tbody>
          <?php foreach ($history as $row): ?>
            <tr>
              <td><?= h($row['bookId']) ?></td>
              <td><?= h($row['issueDate']) ?></td>
              <td><?= h($row['dueDate']) ?></td>
              <td><?= h($row['returnDate']) ?></td>
              <td>$<?= h($row['fineAmountPaid']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
