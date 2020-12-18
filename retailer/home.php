<!DOCTYPE html>
<html>
<head>
	<script src="../jquery-3.3.1.min.js"></script>
	<?php 
	include('../CommonPHPFunctions.php');

	if (!isRetailer()) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../login.php');
	}

	if (isset($_GET['logout'])) {
		session_destroy();
		unset($_SESSION['user']);
		header("location: ../login.php");
	}

	?>
	<title>PromoCash - Retailer Home Page</title>
	<link rel="stylesheet" type="text/css" href="../style.css">
	<style>
	.header {
		background: #003366;
	}
	button[name=register_btn] {
		background: #003366;
	}
	</style>
	
</head>
<body>
	<div class="header">
		<h2>Retailer - Home Page</h2>
	</div>
	<div class="content">
		<!-- notification message -->
		<?php if (isset($_SESSION['success'])) : ?>
			<div class="error success" >
				<h3>
					<?php 
						echo $_SESSION['success']; 
						unset($_SESSION['success']);
					?>
				</h3>
			</div>
		<?php endif ?>

		<!-- logged in user information -->
		<div class="profile_info">
			<img src="../images/reseller_profile.png"  >

			<div>
				<?php  if (isset($_SESSION['user'])) : ?>
					<strong><?php echo $_SESSION['user']['username']; ?></strong>

					<small>
						<i  style="color: #888;">(<?php echo ucfirst($_SESSION['user']['user_type']); ?>)</i> 
						<br>
						<a href="home.php?logout='1'" style="color: red;">logout</a>
						&nbsp; 
						<a href="retailer_panel.php"> + Pannello clienti</a>
						&nbsp; 
						<a href="retailer_coupon.php"> + Coupon per servizio</a>
					</small>
				<?php endif ?>
			</div>
		</div>
	</div>
</body>
</html>