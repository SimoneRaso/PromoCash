<?php
session_start();
if($_POST['value']){
	unset($_SESSION['bElencoCouponCustom']);
	$_SESSION['bElencoCouponCustom'] = $_POST['value'];
}
else
	$_SESSION['bElencoCouponCustom'] = "Error";

echo $_SESSION['bElencoCouponCustom'];

//echo json_encode(array('data' => $_SESSION['bElencoCouponCustom']));
?>