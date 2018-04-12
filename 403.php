 <?php
	#Begin Session
	session_start();
	
	
	#Import needed files
	require_once "logic/403-logic.php";
	require_once "page/403-page.php";

	#Render page
	header("HTTP/1.1 403 Forbidden");
	printHeader();
	printStartBody();
	#printNavigation();
	printForm(getCustomErrorMessage());
	printEndBody();
 
?>
