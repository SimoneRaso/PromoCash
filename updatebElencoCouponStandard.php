<?php
session_start();
if($_POST['value']){
	unset($_SESSION['bElencoCouponStandard']);
	$_SESSION['bElencoCouponStandard'] = $_POST['value'];
}
else
	$_SESSION['bElencoCouponStandard'] = "Error";

echo $_SESSION['bElencoCouponStandard'];

//echo json_encode(array('data' => $_SESSION['bElencoCouponStandard']));
?>