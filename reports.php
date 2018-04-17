
 <?php
	#Begin Session
	session_start();
	
	
	#Import needed files

	require_once "logic/portal-base-logic.php";
	require_once "logic/common/commonFunctions.php";
	require_once "page/portal-base-page.php";
	require_once "page/default-layout-page.php";
	require_once "navigation.php";
	
    #Page Specific imports
	require_once "logic/reports-logic.php";

	$error="";
	
    #Check to see if the users are valid
    verifyUser();
    
    verifyUserLevelAccess($_SESSION['username'],basename($_SERVER['PHP_SELF']));
    
    
    $report=reportDailyUsage();
    var_dump($report);
    
 
	#Since the user is not logged in nor completed a successful login requiest, render the form.

	
	printHeader();
	printStartBody();
	printPortalHead();
	printNavBar(getUserInfo(),createNavigation());
	printContent("<p>Content Area</p> <br> <pre></pre>");
	printEndBody();
 
?>
