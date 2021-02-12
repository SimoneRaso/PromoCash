<!DOCTYPE html>
<html>
<head>
	<script src="../jquery-3.3.1.min.js"></script>
	<?php
	include('../CommonPHPFunctions.php');
	if (!isLoggedIn()) {
		$_SESSION['msg'] = "Prima devi fare il login";
		header('location: ../login.php');
	}
	?>
	<title>PromoCash - User Home Page</title>
	<link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>
	
	<div class="header">
		<h2>User - Home Page</h2>
		<h2><?php echo $_SESSION['user']['nomevisualizzato']?></h2>
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
			<img src="../images/user_profile.png"  >

			<div>
				<?php  if (isset($_SESSION['user'])) : ?>
					<strong><?php echo $_SESSION['user']['username']; ?></strong>

					<small>
						<i  style="color: #888;">(
							<?php if(isset($_SESSION['user']['user_type']))
								echo ucfirst($_SESSION['user']['user_type']); 
							else
								echo('cliente');
							?>)
						</i> 
						<br>
						<a href="../index.php?logout='1'" style="color: red;">logout</a>
						&nbsp;

                                                <?php
                                              	if (!is_true($_SESSION['user']['only_export_cli']))
                                                    echo '<a href="coupon.php"> + Coupon</a>';
                                                else
                                                    echo '<a href="coupon.php"> + Coupon / File CLI</a>';
                                                ?>

						&nbsp;
                                                
                                                <?php   
                                                if (!is_true($_SESSION['user']['only_export_cli'])){
                                                    echo '<a href="couponconsumati.php"> + Coupon Consumati</a>';
                                                    echo '&nbsp;';
                                                    echo '<a href="statistichecoupon.php"> + Statistiche coupon</a>';
                                                    echo '&nbsp;';
                                                }
                                                ?>
                                                
						<script>
						if(<?php var_export($is_sandbox); ?>)
							document.write('<a href="consumacoupon.php"> + Consuma Coupon (solo per Custom)</a>');
						</script>
					</small>
				<?php endif ?>
			</div>
		</div>
	</div>
</body>
</html>