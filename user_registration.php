
 <?php
	#Begin Session
	session_start();
	
	
	#Import needed files

	require_once "logic/user_registration-logic.php";
    require_once "page/user_registration-page.php";
	require_once "logic/common/commonFunctions.php";


	$error="";

	
    #Check to see if the users are valid
    verifyUser();
    
    #Restore user edits if initial submission failed.
	restoreForm();
 
    #Sanity Check panned out so lets logic.
	
    $htmlCode=generateRegistration();
    
	#If they submitted a login attempt, process it now
	if(isset($_POST['reg-submit']))
	{
		$error = attemptRegistration();
		
		//Process error codes:
		if ($error == 1)
		{
            //Successful registration has occured!
            
            
            //Send user on their way
            if(isset($_SESSION["registerSid"]) && !empty($_SESSION["registerSid"]))
            {
                //User now can access kiosk
                $_SESSION['kiosk']= true;
                
                //Clean up session
                unset($_SESSION["registerSid"]);
                unset($_SESSION["userAttr"]);
                
                //Send user on their way
                header("location: tutor_session.php");
                exit();
            }
            else
            {
                //Clean up the session
                unset($_SESSION['register']);
                unset($_SESSION["userAttr"]);
                
                //Send the user on their way
                header("location: portal.php");
                exit();
            }
            

		}
		else if ($error == -1)
		{
            $error = "All fields are required";
		}
		else if ($error == -2)
		{
            $error = "Please enter a valid ID number";
		}
		else if ($error == -3)
		{
            $error = "Please enter a valid email address";
		}
		else if ($error == -999)
		{
            $error = "Internal database error. Contact System Administrator.";
		}

		
	}
	else if (isset($_POST['reg-cancel']))
	{
        session_unset();
        header("location: portal.php");
        exit();
	}

	
	
    #Now Render the form
	printHeader();
	printStartBody();
	#printNavigation();
	printForm($htmlCode,$error);
	printEndBody();
 
?>
