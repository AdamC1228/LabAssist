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
require_once "logic/manageschedules-logic.php";

/* Check to see if the users are valid. */
verifyUser();
verifyUserLevelAccess($_SESSION['username'], basename($_SERVER['PHP_SELF']));

$depart = getUsersDepartment($_SESSION['username']);

$html = "";

if(isset($_POST['tutorAvailability']) && !empty($_POST['tutorAvailability'])) {
	$prevValue = $_POST['tutorAvailability'];

	if($prevValue[0] === 'N') {
		$val = unregisterSchedule($prevValue, $depart);

		if($val) {
			$html .= "<script>alert(\"Tutor unregistered successfully!\");</script>";
		} else {
			$html .= "<script>alert(\"An error occured unregistering the tutor!\");</script>";
		}
	} else {
		$val = registerSchedule($prevValue, $depart);

		if($val) {
			$html .= "<script>alert(\"Tutor registered successfully!\");</script>";
		} else {
			$html .= "<script>alert(\"An error occured registering the tutor!\");</script>";
		}
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
