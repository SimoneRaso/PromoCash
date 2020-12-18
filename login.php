<!DOCTYPE html>
<html>
<head>
	<script src="jquery-3.3.1.min.js"></script>
	<?php include('CommonPHPFunctions.php') ?>
	<title>PromoCash - Login </title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
	<div class="header">
		<h2>Login</h2>
	</div>
	<form id="frmLogin" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >

		<?php echo display_error(); ?>

		<div class="input-group">
			<label>Username</label>
			<input type="text" name="username" >
		</div>
		<div class="input-group">
			<label>Password</label>
			<input type="password" name="password">
		</div>
		<div class="input-group">
			<button type="submit" class="btn" name="login_btn">Login</button>
		</div>
	</form>
</body>
</html>