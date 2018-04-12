
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
	require_once "logic/manageusers-logic.php";

	
    #Check to see if the users are valid
    verifyUser();
    verifyUserLevelAccess($_SESSION['username'],basename($_SERVER['PHP_SELF']));
    
    /*
    *
    *   Begin High Level logic
    *
    */

    $html="";

    if(isset($_POST['submitEdit']) && !empty($_POST['submitEdit']))
    {
        $result=databaseSubmitEdits($_POST['submitEdit'],$_POST['role'],$_POST['department']);


        
        if(is_array($result))
        {

            $html.="<script>alert(\"Edit success!\");</script>";
        }
        else
        {
            $_POST['edit']=$_POST['submitEdit'];
            $html.="<script>alert(\"An error occured submitting the form. Please verify all values and try again.\");</script>";
        }
    }
    else if(isset($_POST['cancelEdit']) && !empty($_POST['cancelEdit']))
    {
        header('Location: manageusers.php');
    }
       
    
    
    //if its an edit user event show code for edit user event
    //otherwise show the main listing.
    if(isset($_POST['edit']) && !empty($_POST['edit']))
    {
        $_SESSION['editID']=$_POST['edit'];
        $html .= editUser($_SESSION['editID']);
    }
    else if (isset($_GET['searchSubmit']) && !empty($_GET['searchSubmit']))
    {
        $html .= searchResults();
    }
    else if (isset($_GET['searchReset']) && !empty($_GET['searchReset']))
    {
        header('Location: manageusers.php');
    }
    else
    {
        $html .= displayAll();
    }

    
    /*
    *
    *   Begin page print
    *
    */
	
	
	printHeader();
	printStartBody();
	printPortalHead();
	printNavBar(getUserInfo(),createNavigation());
	printContent($html);
	printEndBody();
?>
