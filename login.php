<?php
include "bootstrap.php";

session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_username'])) {
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>HTOR BLS - Login</title>
</head>
<body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
	  <div class="container text-center" style="width: 750px; background: #fff; border-radius: 10px; overflow: hidden; padding: 77px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
	  	<div class="row">
		<form class="container" action="auth.php" method="post" style="width: 35rem">
			<h1 class="text-center display-4"><img class="htorlogo" src="./images/htorlogo.svg" alt="HTOR Logo" width="100" height="100"> BLS - Login</h1>
			<a href="help.php" class="btn btn-orange ml-4 mb-4">Help/Support</a>
			<br>
			<br>
			<?php
			if (isset($_GET['error'])) {
			?>
			<div class="alert alert-danger" role="alert"><?=htmlspecialchars($_GET['error'])?></div>
			<?php
			}
			?>
		  <div class="form-floating mb-3">
		    <input type="username" name="username" value="<?php if(isset($_GET['username']))echo (htmlspecialchars($_GET['username'])) ?>" class="form-control" id="inputUsername" placeholder="Enter Username">
		    <label for="inputUsername">Username</label>
		  </div>
		  <div class="form-floating mb-3">
		    <input type="password" name="password" class="form-control" id="inputPassword" placeholder="Password">
		    <label for="inputPassword">Password</label>
		  </div>
		  <br>
		  <button type="submit" class="btn btn-orange">Login</button>
		</form>
	</div>
	  </div>
</body>
</html>
<?php
}
else {
  header("Location: index.php");
}
?>