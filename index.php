<?php
//include('./CommonFunctions.php');

if (!isset($_SESSION)){
//if (!isLoggedIn()) {
	$_SESSION['msg'] = "Prima devi fare il login";
	header('location: login.php');
}
else
{
	session_unset();// remove all session variables
	session_destroy();// destroy the session 
	header('location: login.php');
}
?>
