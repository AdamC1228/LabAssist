<?php
#Begin Session
session_start();


#Import needed files

require_once "logic/portal-base-logic.php";
require_once "logic/common/commonFunctions.php";
require_once "page/portal-base-page.php";
require_once "page/default-layout-page.php";
require_once "navigation.php";

$error="";

#Check to see if the users are valid
verifyUser();

verifyUserLevelAccess($_SESSION['username'],basename($_SERVER['PHP_SELF']));

#Since the user is not logged in nor completed a successful login requiest, render the form.

$idno=$_SESSION['useridno'];
$username=$_SESSION['username'];

$html=<<<eof
<h2>Home</h2> 
<div class="group flex">
    <div class='flex flexGrow'>
        <div class="marginTop10 padingTop10 paddingBottom10 paddingLeft10 paddingRight10">
        <p> Hello <em><b>{$username}</b></em>! Our system is currently operating at <em><u>optimal levels</u></em>. So please, kick back and relax while we do our best to assist you in your class needs.</p>
        <p>In case you were wondering, you can access the kiosk mode with your WVU Tech student ID number. If you dont know it, then relax because we have you covered.</p>
        <p><em>Your ID number is:</em> <b>{$idno}</b></p>
        </div>
    </div>
</div>
eof;

printHeader();
printStartBody();
printPortalHead();
printNavBar(getUserInfo(),createNavigation());
printContent($html);
printEndBody();

?>
