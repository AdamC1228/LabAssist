<?php

#BAD! BAD!!! Do not do!
#header("Content-type: text/csv");
#header("Content-disposition: attachment; filename=report.csv");
#readfile($_GET['file']);







#Begin Session
session_start();


#Needed includes
require_once "logic/common/commonFunctions.php";

#Verify userlevel access
verifyUser();
verifyUserLevelAccess($_SESSION['username'],basename($_SERVER['PHP_SELF']));


if(!isSet($_SESSION['reportFile']) && empty($_SESSION['reportFile']))
{
	header("Location: reports.php");
}



#Tell the web brower we are about to send crap.
header("Content-Description: File Transfer");
header("Content-type: text/csv");
header("Content-disposition: attachment; filename=report.csv");
header("Expires:0");
header('Cache-Control: must-revalidate');
header('Pragma:public');
header('Content-Length: ' . filesize($_SESSION['reportFile']) . '"');


#Use session to determine the file to serve up to the user. 
readfile($_SESSION['reportFile']);

//Clear the session variable after use. 
unset($_SESSION['reportFile']);






?>
