
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
	require_once "logic/forum-logic.php";

	
    #Check to see if the users are valid
    verifyUser();
    verifyUserLevelAccess($_SESSION['username'],basename($_SERVER['PHP_SELF']));
    
    /*
    *
    *   Begin High Level logic
    *
    */
    $html="";

    //Handle The solution checkbox
    if(isset($_POST['solvedCheck']) && !empty($_POST['solvedCheck']))
    {
        if($_POST['solvedCheck']==100)
        {
            updateQuestionStatus('answered',$_GET['viewThread']);
        }
        else if($_POST['solvedCheck']==-100)
        {
            updateQuestionStatus('awaiting_response',$_GET['viewThread']);
        }
    }
    
    //Handle the Thread responses
    if(isset($_POST['formattedReply']) && !empty($_POST['formattedReply']))
    {
        $is_Success=createResponse($_GET['viewThread'],$_SESSION['useridno'],$_POST['formattedReply']);
        
        if($is_Success==1)
        {
            $location = basename($_SERVER['REQUEST_URI']);
            unset($_POST);
            header("Location: {$location}");
            exit();
        }
        else
        {
            $html.="<script>alert(\"Unable to create reply. Please try again.\");</script>";
        }
    }    
    
    //If board selected, show threads for that board.
    if(isset($_GET['viewBoard']) && !empty($_GET['viewBoard']))
    {
        if(isset($_GET['viewThread']) && !empty($_GET['viewThread']))
        {
            $html.=showThread($_GET['viewThread']);
        }
        else
        {
            if((isset($_POST['formattedThread']) && !empty($_POST['formattedThread']))&&(isset($_POST['threadTitle']) && !empty($_POST['threadTitle'])))
            {
                $questionArray=array($_POST['classSelect'],$_POST['threadTitle'],$_SESSION['useridno']);
                $threadPostArray=array($_SESSION['useridno'],$_POST['formattedThread']);
                $is_Success=createThread($questionArray,$threadPostArray);
                
                if($is_Success==1)
                {
                    unset($_POST);
                    $location = "forum.php?viewBoard={$_GET['viewBoard']}";
                    header("Location: {$location}");
                    exit();
                }
                else
                {
                    $html.="<script>alert(\"Unable to create new thread. Please try again.\");</script>";
                }
            }
            
            //Show board or create thread in board.
            if(isset($_GET['newThread']) && !empty($_GET['newThread']))
            {
                $html.=createNewThread();
            }
            else
            {
                $html.=showBoard($_GET['viewBoard']);
            }
        }
    }
    //Otherwise show listing of boards.
    else
    {
        //Show Thread Listing
        $html.=showBoardListing();
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
