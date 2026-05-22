<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include "bootstrap.php";
include "db_connect.php";
?>

<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
    <div class="container text-center" style="width: 750px; background: #fff; border-radius: 10px; overflow: hidden; padding: 33px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
<?php
$bookId = normalize_book_id($_GET["return"] ?? '');

function return_book(mysqli $mysqli, string $bookId, string $fineAmountPaid) {
  $row = db_fetch_one($mysqli,
    "SELECT patronName, contactInfo, issueDate, dueDate FROM librarylog WHERE bookId = ?",
    's',
    [$bookId]
  );
  if (!$row) {
    return false;
  }

  if (!db_execute($mysqli,
    "INSERT INTO libraryarchive (patronName, contactInfo, bookId, issueDate, dueDate, returnDate, fineAmountPaid) VALUES (?, ?, ?, ?, ?, CURRENT_DATE, ?)",
    'sssssd',
    [$row['patronName'], $row['contactInfo'], $bookId, $row['issueDate'], $row['dueDate'], $fineAmountPaid]
  )) {
    return false;
  }

  return db_execute($mysqli, "DELETE FROM librarylog WHERE bookId = ?", 's', [$bookId]);
}

if (array_key_exists('confirm', $_POST)) {
  $paidRaw = $_POST['paid_amount'] ?? '0';
  $fineAmountPaid = is_numeric($paidRaw) ? number_format((float)$paidRaw, 2, '.', '') : '0.00';

  if (return_book($mysqli, $bookId, $fineAmountPaid)) {
    echo "<h4>Item Returned</h4>";
?>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
<?php
  }
} else {
  $row = db_fetch_one($mysqli, "SELECT fineAmount FROM librarylog WHERE bookId = ?", 's', [$bookId]);

  if ($row) {
    $fineCheck = (float)$row['fineAmount'];
    if ($fineCheck > 0) {
      $fineDisplay = number_format($fineCheck, 2, '.', '');
      echo "<h4>This item has an outstanding fine of: $" . h($fineDisplay) . " Edit the fine amount paid if necessary below.</h4>";
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
               value='" . h($fineDisplay) . "'
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
<button onclick="window.location.href='./book_id_tool.php?bookId=<?= urlencode($bookId) ?>';" id="showlog" name="showlog" class="btn btn-orange">Cancel</button>
<?php
    } elseif (return_book($mysqli, $bookId, '0.00')) {
      echo "<h4>Item Returned</h4>";
?>
<button onclick="window.location.href='./index.php';" id="showlog" name="showlog" class="btn btn-orange">Return Home</button>
<?php
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
        return false;
    }
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

function validateMoneyInput(input) {
    let value = input.value.replace(/[^\d.]/g, '');
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    if (parts.length > 1) {
        value = parts[0] + '.' + parts[1].slice(0, 2);
    }
    input.value = value;
}
</script>
</body>
