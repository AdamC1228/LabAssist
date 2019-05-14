<?php
#Begin Session
session_start();


#Import needed files

require_once "logic/login-logic.php";
require_once "page/login-page.php";
require_once "logic/common/commonFunctions.php";

verifyUser();

$error="";

#If they submitted a login attempt, process it now
if(isset($_POST['pass-submit']))
{
	$error = attemptPassLogin();
}
else if(isset($_POST['kiosk-submit']))
{
	$error = attemptKioskLogin();
}


#Since the user is not logged in nor completed a successful login requiest, render the form.

printHeader();
printStartBody();
#printNavigation();
printForm($error);
printEndBody();

?>
