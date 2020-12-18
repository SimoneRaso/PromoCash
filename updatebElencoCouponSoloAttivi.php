<?php
session_start();
if($_POST['value']){
	unset($_SESSION['bElencoCouponSoloAttivi']);
	$_SESSION['bElencoCouponSoloAttivi'] = $_POST['value'];
}
else
	$_SESSION['bElencoCouponSoloAttivi'] = "Error";

echo $_SESSION['bElencoCouponSoloAttivi'];

//echo json_encode(array('data' => $_SESSION['bElencoCouponSoloAttivi']));
?>