<?php
session_start();
include 'db_connect.php';

if (isset($_POST['username']) && isset($_POST['password'])) {
	
	$username = $_POST['username'];
	$password = $_POST['password'];

	if (empty($username)) {
		header("Location: login.php?error=Username is required");
	}else if (empty($password)){
		header("Location: login.php?error=Password is required&username=$username");
	}else {
		$sql = "SELECT * FROM users WHERE username='$username'";
		$result = $mysqli->query($sql) or die(mysqli_error($mysqli));

		if ($result->num_rows == 1) {
			$user = $result->fetch_assoc();

			$user_id = $user['id'];
			$user_username = $user['username'];
			$user_password = $user['password'];

			if ($username === $user_username) {
				if (password_verify($password, $user_password)) {
					$_SESSION['user_id'] = $user_id;
					$_SESSION['user_username'] = $user_username;
					header("Location: index.php");
				}
				else {
					header("Location: login.php?error=Incorrect Username or Password&username=$username");
				}
			}
			else {
				header("Location: login.php?error=Incorrect Username or Password&username=$username");
			}
		}
		else {
			header("Location: login.php?error=Incorrect Username or Password&username=$username");
		}
	}
}
?>