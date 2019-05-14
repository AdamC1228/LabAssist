<?php
#Begin Session
session_start();


#Import needed files

require_once "logic/portal-base-logic.php";
require_once "logic/common/commonFunctions.php";
require_once "page/portal-base-page.php";
require_once "navigation.php";

#Page Specific imports
require_once "logic/reports-logic.php";
require_once "page/reports-page.php";

$header =<<<header
	<link rel='stylesheet' href='bower_components/chartist/dist/chartist.min.css'>
	<link rel='stylesheet' href='styles/charts.css'>
header;



$error = "";
$html = "";
$reportSelected="";

#Check to see if the users are valid
verifyUser();
verifyUserLevelAccess($_SESSION['username'],basename($_SERVER['PHP_SELF']));

$html.=printReportHeader();

if(isset($_GET['showReport']) && !empty($_GET['showReport']))
{
	#Print the header where the user can select the appropriate report
	if(isSet($_GET['selectedReport']) && !empty($_GET['selectedReport']))
	{
		$reportSelected=$_GET['selectedReport'];
	}
	else
	{
		$reportSelected='0';
	}

	$html.=printReport($reportSelected);    
}


#Render the form
printCustomHeader($header);
printStartBody();
printPortalHead();
printNavBar(getUserInfo(),createNavigation());
printContent($html);
printEndBody();

?>
