<?php
require_once __DIR__ . '/includes/auth.php';
bls_session_start();

session_unset();
session_destroy();

header('Location: login.php');
exit;
