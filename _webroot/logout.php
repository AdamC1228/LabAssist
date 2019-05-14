<?php
session_start();

//Clear session variables
session_unset();

//Clear post variables
$_POST = array();

//Send user to login page
header("location: login.php");

?>
