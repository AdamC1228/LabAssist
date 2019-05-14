<?php
#Begin Session
session_start();


#Base imports
require_once "logic/common/commonFunctions.php";
require_once "page/portal-base-page.php";
require_once "logic/portal-base-logic.php";
require_once "page/default-layout-page.php";
require_once "navigation.php";


#Page Specific imports
require_once "logic/showschedules-logic.php";


#Check to see if the users are valid
verifyUser();
verifyUserLevelAccess($_SESSION['username'], basename($_SERVER['PHP_SELF']));



$depart = 'CS-IS';

$html = generateScheduleTable($depart);

/*
 * Begin page print
 */
printHeader();
printStartBody();
printPortalHead();
printNavBar(getUserInfo(), createNavigation());
printContent($html);
printEndBody();

?>
