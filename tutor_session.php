
 <?php
	#Begin Session
	session_start();
	
	
	#Import needed files

	require_once "logic/tutor_session-logic.php";
    require_once "page/tutor_session-page.php";
	require_once "logic/common/commonFunctions.php";


	$error="";
	
    #Check to see if the users are valid
    verifyKiosk();
    
    #Check to see if the users have permission
    verifyUserIdLevelAccess($_SESSION['sidno'],basename($_SERVER['PHP_SELF']));
 
    #Sanity Check panned out so lets logic.
	
    $htmlCode=generateClockinClockoutForm();
    
	#If they submitted a login attempt, process it now
	if(isset($_POST['log-submit']))
	{
		$error = attemptClockinClockout();
		
		//Process error codes:
		if ($error == 1)
		{
            //Successful clockin has occured!
            unset($_SESSION['kiosk']);
            
            //Is it a clockin or clockout
            $status=$_POST["status"];
            
            //Alert user to successful login
            $htmlCode.="<script>alert(\"Successfully $status. \\n \\nAbout to be redirected back to login.\");</script";
            
            //Redirect user
            header("refresh: 0; url=login.php");
		}
		else if ($error == -1)
		{
            $error = "Please select a Department.";
		}
		else if ($error == -2)
		{
            $error = "Please select a Class.";
            if(isSet($_POST["section"]))
            {
                unset($_POST['section']);   //Bug fix to prevent section from appearing when no class is available.
            }
		}
		else if ($error == -3)
		{
            $error = "Please select a Section.";
		}
		
		else if ($error == -999)
		{
            $error = "Internal database error. Contact System Administrator.";
		}
		else if ($error == -10)
		{
            $error = "An error occured attempting to log out. Contact System Administrator.";
		}

		
	}
	else if (isset($_POST['log-cancel']))
	{
        session_unset();
        header("location: login.php");
        exit();
	}

	
	
    #Now Render the form
	printHeader();
	printStartBody();
	#printNavigation();
	printForm($htmlCode,$error,generateButtons());
	printEndBody();
 
?>
