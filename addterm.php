<?php
#Begin Session
session_start();


#Import needed files

#Base imports
require_once "logic/common/commonFunctions.php";
require_once "page/portal-base-page.php";
require_once "logic/portal-base-logic.php";
require_once "page/default-layout-page.php";
require_once "navigation.php";



#Page Specific imports
require_once "logic/addterm-logic.php";

$error="";

#Check to see if the users are valid
verifyUser();

verifyUserLevelAccess($_SESSION['username'],basename($_SERVER['PHP_SELF']));

#Since the user is not logged in nor completed a successful login requiest, render the form.

$html="";


if(isset($_POST['submitEdit']) && !empty($_POST['submitEdit']))
{
	$result=attemptAddTerm();	

	if(is_array($result))
	{
		$html.="<script>alert(\"Term added.\");</script>";
	}
	else if($result=-2)
	{
		$html.="<script>alert(\"Term may only contain digits.  Please try again!\");</script>";
	}
	else
	{
		$html.="<script>alert(\"An error occured creating the term. Please make sure to all fields are completed !\");</script>";
	}
}
else if(isset($_POST['cancelEdit'])&& !empty($_POST['cancelEdit']))
{
	header('Location: manageterms.php');
}


$html.=createForm();




printHeader();
printStartBody();
printPortalHead();
printNavBar(getUserInfo(),createNavigation());
printContent($html);
printEndBody();

?>
