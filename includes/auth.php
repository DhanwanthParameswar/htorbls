<?php

function bls_session_start(): void {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
}

function bls_is_logged_in(): bool {
  bls_session_start();
  return isset($_SESSION['user_id'], $_SESSION['user_username']);
}

function bls_require_auth(): void {
  if (!bls_is_logged_in()) {
    header('Location: login.php');
    exit;
  }
}

function bls_redirect_if_logged_in(string $destination = 'index.php'): void {
  if (bls_is_logged_in()) {
    header('Location: ' . $destination);
    exit;
  }
}

function bls_current_user_id(): ?int {
  return bls_is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

function bls_current_username(): string {
  return bls_is_logged_in() ? (string)$_SESSION['user_username'] : '';
}
