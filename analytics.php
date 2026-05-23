<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include "db_connect.php";
include "bootstrap.php";
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/patrons.php';

$range = isset($_GET['range']) ? $_GET['range'] : '6m';
if (!in_array($range, ['6m', '12m', 'all'], true)) {
  $range = '6m';
}

$rangeLabels = [
  '6m' => 'Last 6 months',
  '12m' => 'Last 12 months',
  'all' => 'All time',
];
$rangeLabel = $rangeLabels[$range];

$rangeSql = '';
if ($range === '6m') {
  $rangeSql = "returnDate >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)";
  $archiveIssueSql = "issueDate >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)";
} elseif ($range === '12m') {
  $rangeSql = "returnDate >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)";
  $archiveIssueSql = "issueDate >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)";
} else {
  $rangeSql = "1=1";
  $archiveIssueSql = "1=1";
}

$totalBooks = (int)db_scalar($mysqli, "SELECT COUNT(*) FROM booklist");
$checkedOut = (int)db_scalar($mysqli, "SELECT COUNT(*) FROM librarylog");
$available = (int)db_scalar($mysqli,
  "SELECT COUNT(*) FROM booklist b LEFT JOIN librarylog l ON b.bookId = l.bookId WHERE l.bookId IS NULL"
);
$overdue = (int)db_scalar($mysqli, "SELECT COUNT(*) FROM librarylog WHERE dueDate < CURRENT_DATE");
$dueToday = (int)db_scalar($mysqli, "SELECT COUNT(*) FROM librarylog WHERE dueDate = CURRENT_DATE");
$outstandingFines = (float)db_scalar($mysqli,
  "SELECT COALESCE(SUM(CEIL(GREATEST(DATEDIFF(CURRENT_DATE, dueDate), 0)/7)*0.25), 0) FROM librarylog"
);
$returnsPeriod = (int)db_scalar($mysqli, "SELECT COUNT(*) FROM libraryarchive WHERE $rangeSql");
$finesCollected = (float)db_scalar($mysqli,
  "SELECT COALESCE(SUM(fineAmountPaid), 0) FROM libraryarchive WHERE $rangeSql"
);

$returnsLabels = [];
$returnsCounts = [];
$sql = "SELECT DATE_FORMAT(returnDate, '%Y-%m') AS month, COUNT(*) AS cnt
  FROM libraryarchive
  WHERE $rangeSql
  GROUP BY month
  ORDER BY month";
$result = $mysqli->query($sql);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $returnsLabels[] = $row['month'];
    $returnsCounts[] = (int)$row['cnt'];
  }
}

$categoryLabels = [];
$categoryCounts = [];
$sql = "SELECT COALESCE(NULLIF(TRIM(bookCategory), ''), 'Uncategorized') AS cat, COUNT(*) AS cnt
  FROM booklist
  GROUP BY cat
  ORDER BY cnt DESC";
$result = $mysqli->query($sql);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $categoryLabels[] = $row['cat'];
    $categoryCounts[] = (int)$row['cnt'];
  }
}

$topBooks = [];
$sql = "SELECT c.bookId, COALESCE(b.bookName, c.bookId) AS bookName,
  COALESCE(b.bookCategory, '') AS bookCategory, c.checkout_count
  FROM (
    SELECT bookId, COUNT(*) AS checkout_count
    FROM (
      SELECT bookId FROM libraryarchive WHERE $archiveIssueSql
      UNION ALL
      SELECT bookId FROM librarylog
    ) AS all_checkouts
    GROUP BY bookId
    ORDER BY checkout_count DESC
    LIMIT 10
  ) AS c
  LEFT JOIN booklist b ON c.bookId = b.bookId
  ORDER BY c.checkout_count DESC";
$result = $mysqli->query($sql);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $topBooks[] = $row;
  }
}

$topPatrons = [];
$sql = "SELECT p.id AS patron_id, p.patronName, p.contactInfo, COUNT(*) AS checkout_count
  FROM (
    SELECT patron_id FROM libraryarchive WHERE $archiveIssueSql AND patron_id IS NOT NULL
    UNION ALL
    SELECT patron_id FROM librarylog WHERE patron_id IS NOT NULL
  ) AS all_checkouts
  INNER JOIN patrons p ON p.id = all_checkouts.patron_id
  GROUP BY p.id, p.patronName, p.contactInfo
  ORDER BY checkout_count DESC
  LIMIT 10";
$result = $mysqli->query($sql);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $topPatrons[] = $row;
  }
}
?>
<title>HTOR BLS - Library Analytics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body style="width: 100%; min-height: 100vh; display: flex; flex-wrap: wrap; justify-content: center; align-items: flex-start; padding: 15px; background: #F4CABC;">
  <div class="container analytics-page text-center" style="width: 1200px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); margin: 15px 0;">
    <h1 class="text-center display-4">Library Analytics</h1>
    <p class="text-muted">Snapshot metrics reflect the current state. Period metrics use the selected range.</p>

    <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
      <?php bls_render_subpage_toolbar('analytics'); ?>
      <a href="analytics.php?range=6m" class="btn btn-outline-secondary<?= $range === '6m' ? ' active' : '' ?>">Last 6 months</a>
      <a href="analytics.php?range=12m" class="btn btn-outline-secondary<?= $range === '12m' ? ' active' : '' ?>">Last 12 months</a>
      <a href="analytics.php?range=all" class="btn btn-outline-secondary<?= $range === 'all' ? ' active' : '' ?>">All time</a>
    </div>

    <h5 class="text-start mt-4">Current snapshot</h5>
    <div class="row g-3 mb-4 text-start">
      <div class="col-6 col-md-4 col-lg-3">
        <div class="analytics-card card h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="analytics-card-label text-muted">Total books</div>
            <div class="analytics-card-value"><?= $totalBooks ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="analytics-card card h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="analytics-card-label text-muted">Checked out</div>
            <div class="analytics-card-value"><?= $checkedOut ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="analytics-card card h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="analytics-card-label text-muted">Available</div>
            <div class="analytics-card-value"><?= $available ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="analytics-card card h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="analytics-card-label text-muted">Overdue</div>
            <div class="analytics-card-value text-danger"><?= $overdue ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="analytics-card card h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="analytics-card-label text-muted">Due today</div>
            <div class="analytics-card-value"><?= $dueToday ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="analytics-card card h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="analytics-card-label text-muted">Outstanding fines</div>
            <div class="analytics-card-value">$<?= number_format($outstandingFines, 2) ?></div>
          </div>
        </div>
      </div>
    </div>

    <h5 class="text-start">Period: <?= h($rangeLabel) ?></h5>
    <div class="row g-3 mb-4 text-start">
      <div class="col-6 col-md-4">
        <div class="analytics-card card h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="analytics-card-label text-muted">Returns</div>
            <div class="analytics-card-value"><?= $returnsPeriod ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="analytics-card card h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="analytics-card-label text-muted">Fines collected</div>
            <div class="analytics-card-value">$<?= number_format($finesCollected, 2) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4 text-start">
      <div class="col-lg-6">
        <h5>Returns over time</h5>
        <?php if (count($returnsLabels) === 0): ?>
          <p class="text-muted">No returns in this period.</p>
        <?php else: ?>
          <div class="analytics-chart-wrap">
            <canvas id="returnsChart"></canvas>
          </div>
        <?php endif; ?>
      </div>
      <div class="col-lg-6">
        <h5>Collection by category</h5>
        <?php if (count($categoryLabels) === 0): ?>
          <p class="text-muted">No books in catalog.</p>
        <?php else: ?>
          <div class="analytics-chart-wrap">
            <canvas id="categoryChart"></canvas>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row g-4 text-start">
      <div class="col-lg-6">
        <h5>Most circulated books</h5>
        <table class="table table-striped table-bordered table-hover table-sm">
          <thead>
            <tr>
              <th>Book ID</th>
              <th>Title</th>
              <th>Category</th>
              <th>Checkouts</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($topBooks) === 0): ?>
              <tr><td colspan="4" class="text-muted text-center">No checkout data in this period.</td></tr>
            <?php else: ?>
              <?php foreach ($topBooks as $row): ?>
                <tr>
                  <td><?= h($row['bookId']) ?></td>
                  <td><?= h($row['bookName']) ?></td>
                  <td><?= h($row['bookCategory']) ?></td>
                  <td><?= (int)$row['checkout_count'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="col-lg-6">
        <h5>Most active patrons</h5>
        <table class="table table-striped table-bordered table-hover table-sm">
          <thead>
            <tr>
              <th>Patron</th>
              <th>Contact</th>
              <th>Checkouts</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($topPatrons) === 0): ?>
              <tr><td colspan="3" class="text-muted text-center">No patron data in this period.</td></tr>
            <?php else: ?>
              <?php foreach ($topPatrons as $row): ?>
                <tr>
                  <td><a href="patron.php?id=<?= (int)$row['patron_id'] ?>"><?= h($row['patronName']) ?></a></td>
                  <td><?= h($row['contactInfo']) ?></td>
                  <td><?= (int)$row['checkout_count'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>

<script>
const chartColors = ['#E6501E', '#E8977D', '#F4CABC', '#CC471B', '#B33E17', '#212529', '#6c757d', '#adb5bd'];

<?php if (count($returnsLabels) > 0): ?>
new Chart(document.getElementById('returnsChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($returnsLabels) ?>,
    datasets: [{
      label: 'Returns',
      data: <?= json_encode($returnsCounts) ?>,
      backgroundColor: '#E6501E',
      borderColor: '#CC471B',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
  }
});
<?php endif; ?>

<?php if (count($categoryLabels) > 0): ?>
new Chart(document.getElementById('categoryChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($categoryLabels) ?>,
    datasets: [{
      data: <?= json_encode($categoryCounts) ?>,
      backgroundColor: chartColors.slice(0, <?= count($categoryLabels) ?>)
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' } }
  }
});
<?php endif; ?>
</script>
