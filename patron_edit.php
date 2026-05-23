<?php
require_once __DIR__ . '/includes/auth.php';
bls_require_auth();
include 'bootstrap.php';
include 'db_connect.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/patrons.php';

$isNew = isset($_GET['new']);
$patronId = (int)($_GET['id'] ?? 0);
$returnUrl = $_GET['return'] ?? 'patrons.php';
if ($returnUrl !== 'index.php' && $returnUrl !== 'patrons.php') {
  $returnUrl = 'patrons.php';
}

$errors = [];
$patron = [
  'patronName' => '',
  'firstName' => '',
  'lastName' => '',
  'contactInfo' => '',
  'notes' => '',
  'active' => 1,
];

if (!$isNew && $patronId > 0) {
  $loaded = patron_get($mysqli, $patronId);
  if ($loaded) {
    $patron = $loaded;
  } else {
    $errors[] = 'Patron not found.';
    $isNew = true;
    $patronId = 0;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $patronId = (int)($_POST['id'] ?? 0);
  $isNew = $patronId <= 0;
  $firstName = trim($_POST['firstName'] ?? '');
  $lastName = trim($_POST['lastName'] ?? '');
  $patronName = $_POST['patronName'] ?? '';
  $contactInfo = $_POST['contactInfo'] ?? '';
  $notes = trim($_POST['notes'] ?? '');
  $active = isset($_POST['active']);

  if ($isNew) {
    if ($firstName === '') {
      $errors[] = 'First name is required.';
    }
    if ($lastName === '') {
      $errors[] = 'Last name is required.';
    }
    if (empty($errors)) {
      $patronName = patron_name_from_parts($firstName, $lastName);
      $nameError = patron_validate_first_last_name($patronName);
      if ($nameError !== null) {
        $errors[] = $nameError;
      }
    }
  } else {
    if (trim($patronName) === '') {
      $errors[] = 'Patron name is required.';
    }
  }
  if (trim($contactInfo) === '') {
    $errors[] = 'Contact info is required.';
  }

  if (empty($errors)) {
    if ($isNew) {
      $newId = patron_create($mysqli, $patronName, $contactInfo, $notes);
      if ($newId) {
        if ($returnUrl === 'index.php') {
          header('Location: index.php?patron_id=' . $newId);
        } else {
          header('Location: patron.php?id=' . $newId);
        }
        exit;
      }
      $errors[] = 'Could not create patron.';
    } else {
      if (patron_update($mysqli, $patronId, $patronName, $contactInfo, $notes, $active)) {
        header('Location: patron.php?id=' . $patronId);
        exit;
      }
      $errors[] = 'Could not update patron.';
    }
  }

  $patron = [
    'patronName' => $patronName,
    'firstName' => $firstName,
    'lastName' => $lastName,
    'contactInfo' => $contactInfo,
    'notes' => $notes,
    'active' => $active ? 1 : 0,
  ];
}

$title = $isNew ? 'Add Patron' : 'Edit Patron';
?>
<title>HTOR BLS - <?= h($title) ?></title>
</head>
<body style="width: 100%; min-height: 100vh; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
  <div class="container text-center" style="width: 600px; background: #fff; border-radius: 10px; padding: 33px 55px; box-shadow: 0 5px 10px rgba(0,0,0,0.1);">
    <h1 class="display-5"><?= h($title) ?></h1>
    <?php bls_render_subpage_toolbar('patrons'); ?>
    <hr>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert-danger"><?= h($err) ?></div>
    <?php endforeach; ?>

    <form method="post" class="text-start needs-validation" novalidate>
      <?php if (!$isNew): ?>
        <input type="hidden" name="id" value="<?= (int)$patronId ?>">
      <?php endif; ?>
      <?php if ($isNew): ?>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="firstName" name="firstName" required autocomplete="given-name" value="<?= h($patron['firstName'] ?? '') ?>">
        <label for="firstName">First Name</label>
      </div>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="lastName" name="lastName" required autocomplete="family-name" value="<?= h($patron['lastName'] ?? '') ?>">
        <label for="lastName">Last Name</label>
      </div>
      <?php else: ?>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="patronName" name="patronName" required value="<?= h($patron['patronName'] ?? '') ?>">
        <label for="patronName">Patron Name</label>
      </div>
      <?php endif; ?>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="contactInfo" name="contactInfo" required
               placeholder="Phone, parent name, class, etc."
               value="<?= h($patron['contactInfo'] ?? '') ?>">
        <label for="contactInfo">Contact Info</label>
      </div>
      <div class="form-floating mb-3">
        <textarea class="form-control" id="notes" name="notes" style="height: 100px"><?= h($patron['notes'] ?? '') ?></textarea>
        <label for="notes">Notes (optional)</label>
      </div>
      <?php if (!$isNew): ?>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="active" name="active" <?= !empty($patron['active']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="active">Active (can be selected at checkout)</label>
        </div>
      <?php endif; ?>
      <button type="submit" class="btn btn-orange"><?= $isNew ? 'Create Patron' : 'Save Changes' ?></button>
      <a href="<?= h($returnUrl) ?>" class="btn btn-outline-secondary">Cancel</a>
    </form>
  </div>
</body>
