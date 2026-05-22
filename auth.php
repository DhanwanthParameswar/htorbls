<?php
require_once __DIR__ . '/includes/auth.php';
bls_session_start();
include 'db_connect.php';

if (isset($_POST['username']) && isset($_POST['password'])) {

	$username = trim($_POST['username']);
	$password = $_POST['password'];
	$usernameParam = urlencode($username);

	if (empty($username)) {
		header("Location: login.php?error=Username is required");
		exit;
	}
	if (empty($password)) {
		header("Location: login.php?error=Password is required&username=$usernameParam");
		exit;
	}

	$result = db_select($mysqli, "SELECT id, username, password FROM users WHERE username = ?", 's', [$username]);

	if ($result && $result->num_rows === 1) {
		$user = $result->fetch_assoc();

		if (password_verify($password, $user['password'])) {
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['user_username'] = $user['username'];
			header("Location: index.php");
			exit;
		}
	}

	header("Location: login.php?error=Incorrect Username or Password&username=$usernameParam");
	exit;
}

?>
