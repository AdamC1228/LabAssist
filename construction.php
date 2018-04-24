<?php
#Begin Session
session_start();


#Import needed files
require_once "logic/construction-logic.php";
require_once "page/construction-page.php";

#Render page

printHeader();
printStartBody();
#printNavigation();
printForm(getCustomErrorMessage());
printEndBody();

?>
