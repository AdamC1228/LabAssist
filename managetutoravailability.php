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
require_once "logic/managetutoravailability-logic.php";


#Check to see if the users are valid
verifyUser();
verifyUserLevelAccess($_SESSION['username'], basename($_SERVER['PHP_SELF']));



//$depart = 'CS-IS';
$depart = getUsersDepartment(array($_SESSION['username']))[0]['deptid'];

$html = "";

if(isset($_POST['claimSlot']) && !empty($_POST['claimSlot'])) {
	$prevValue = $_POST['claimSlot'];

	$val = registerClaim($prevValue, $depart);
} else if(isset($_POST['unclaimSlot']) && !empty($_POST['unclaimSlot'])) {
	$prevValue = $_POST['unclaimSlot'];

	$val = registerUnclaim($prevValue, $depart);

	if(!$val) {
		$html .= "<script>alert(\"Could not unclaim timeslot\");</script>";
	}
}

$html .= generateScheduleTable($depart);

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
