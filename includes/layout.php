<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_helpers.php';

function bls_render_user_bar(): void {
  $username = bls_current_username();
  ?>
  <div class="row justify-content-center">
    <div class="col-auto d-flex align-items-center justify-content-center">
      <h4 class="mb-0"><i class="bi bi-person-fill"></i> <b><?= h($username) ?></b></h4>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href="logout.php" class="btn btn-danger ml-4">Logout</a>
      &nbsp;&nbsp;&nbsp;
      <a href="help.php" class="btn btn-orange ml-4">Help/Support</a>
    </div>
  </div>
  <?php
}

function bls_render_main_nav(?string $active = null): void {
  $items = [
    'log' => ['href' => 'library_log.php', 'label' => 'Log'],
    'archive' => ['href' => 'library_archive.php', 'label' => 'Archive'],
    'book_list' => ['href' => 'book_list.php', 'label' => 'Book List'],
    'patrons' => ['href' => 'patrons.php', 'label' => 'Patrons'],
    'analytics' => ['href' => 'analytics.php', 'label' => 'Analytics'],
  ];
  $first = true;
  foreach ($items as $key => $item) {
    if (!$first) {
      echo '&nbsp;';
    }
    $first = false;
    $class = ($active === $key) ? 'btn btn-orange active' : 'btn btn-orange';
    echo '<button onclick="window.location.href=\'./' . h($item['href']) . '\';" class="' . $class . '">' . h($item['label']) . '</button>';
  }
}

function bls_render_subpage_toolbar(?string $active = null): void {
  ?>
  <button onclick="window.location.href='./index.php';" class="btn btn-orange mb-3">Return Home</button>
  <?php
  if ($active !== 'patrons') {
    ?>
  <button onclick="window.location.href='./patrons.php';" class="btn btn-orange mb-3">Patrons</button>
    <?php
  }
  if ($active !== 'analytics') {
    ?>
  <button onclick="window.location.href='./analytics.php';" class="btn btn-orange mb-3">Analytics</button>
    <?php
  }
}
