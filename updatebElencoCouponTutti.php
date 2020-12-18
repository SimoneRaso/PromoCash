<?php
session_start();
if($_POST['value']){
	unset($_SESSION['bElencoCouponTutti']);
	$_SESSION['bElencoCouponTutti'] = $_POST['value'];
}
else
	$_SESSION['bElencoCouponTutti'] = "Error";

echo $_SESSION['bElencoCouponTutti'];

//echo json_encode(array('data' => $_SESSION['bElencoCouponTuttiSoloAttivi']));
?>