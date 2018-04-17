<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";


function showBoardListing()
{
	$html= "   <div class='flex flexVerticalCenter marginBottom10 '>";
	$html.=         forumNavigation('0');
	$html.= "   </div>";
	$html.= paginatedBoardListing();

	return $html;
}

function showBoard($department)
{
	$formURL=basename($_SERVER['REQUEST_URI']);
	$html="";
	$html.= "<script type='text/javascript' src='scripts/searchbar.js'></script>";
	$html.= "<div class='flex columnLayout2 alignCenterFlex marginBottom10'>";
	$html.= "   <div class='flex flexVerticalCenter'>";
	$html.=         forumNavigation('0');
	$html.= "   </div>";
	$html.= "   <div id='createThread'>";
	$html.= "       <form method='GET' action='{$formURL}'>";
	$html.=             preserveOldGETParams();
	$html.= "           <input type='submit' class='btnSmall' value='New Thread' name='newThread'/>";
	$html.= "       </form>";
	$html.= "   </div>";
	$html.= "</div>";
	//$html.= createSearchBar();
	$html.= paginatedThreadListing($department);

	return $html;
}

function showThread($quid)
{
	$html = "<script type='text/javascript' src='scripts/searchbar.js'></script>";
	$html.= "   <div class='flex flexVerticalCenter marginBottom10'>";
	$html.=         forumNavigation($quid);
	$html.= "   </div>";
	$html.= paginatedQuestionListing($quid);

	return $html;
}

function forumNavigation($quid)
{
	$html="";


	$html.= "<a href='forum.php'>Home</a> / ";

	if(isset($_GET['viewBoard']) && !empty($_GET['viewBoard']))
	{
		$html.= "<a href='forum.php?viewBoard={$_GET['viewBoard']}'>{$_GET['viewBoard']}</a> / ";

		if(isset($_GET['viewThread']) && !empty($_GET['viewThread']))
		{
			$questionName=getQuestionName(array($quid));
			$html.= "<a href='forum.php?viewBoard={$_GET['viewBoard']}&viewThread={$_GET['viewThread']}'>$questionName</a>";
		}
	}

	return $html;
}

function paginatedBoardListing()
{
	$html="";
	$paginationParams=getPaginationParameters();
	$html.=generateBoardTable(getBoardList($paginationParams));
	$html.=printBottomPagination($paginationParams,getPaginationCountBoard());

	return $html;
}

function paginatedThreadListing($department)
{
	$html="";
	$paginationParams=array_merge(array($department),getPaginationParameters());
	$html.=generateThreadTable(getThreadList($paginationParams));
	$html.=printBottomPagination($paginationParams,getPaginationCountThread(array($department)));

	return $html;
}

function paginatedQuestionListing($question)
{
	$html="";
	$paginationParams=getPaginationParameters();
	$html.=generateThread(getQuestionList(array_merge(array($question),$paginationParams)));
	$html.=replyToThreadTinyMCE();
	$html.=printBottomPagination($paginationParams,getPaginationCountQuestion(array($question)));

	return $html;
}

function createNewThread()
{
	$html ="";
	$html.=createThreadTinyMCE();

	return $html;
}

/*
 *
 *
 *  TINY MCE INTEGRATION
 *
 *
 *
 */

function createThreadTinyMCE()
{
	$html="";

	$formURL=basename($_SERVER['REQUEST_URI']);
	$classDropDown=genClass($_GET['viewBoard']);

	$html.=<<<EOF
    <script src='/libraries/tinymce/tinymce.min.js'></script>
    <script>
    tinymce.init({
	selector: '#body',
	theme: 'modern',
	height: 300,
	width: '100%',
	plugins: [
	'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
	'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
	'save table contextmenu directionality emoticons template paste textcolor'
	],
	content_css: 'css/content.css',
	toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons'
    });

    </script>
    <div class='responseBlock dropShadow'>
	<form class='responseBlockContainer' method='post' action='{$formURL}'>
	    <div class='responseBlockHeader'>
		<span class='lightText'><b>Create Thread</b></span>
	    </div><!--responseBlockHeader-->
	    <div class='threadBlockTitle'>
		<div class='threadBlockTitleText'>
		    <p>Title:</p>
		</div>
		<div class='threadBlockTitleInput'>
		    <input type='text' class='inputprimaryLarge' name='threadTitle' placeholder='Thread Title' maxlength=60/>
		</div>
		<div class='threadBlockClassText'>
		    <p>Class:</p>
		</div>
		<div class='threadBlockClassInput'>
		    {$classDropDown}
		</div>
	    </div><!--threadBlockTitle-->
	    <div class='responseBlockData'>
		    <textarea id="body" name="formattedThread"></textarea>
	    </div><!--responseBlockData-->
	    <div class='responseBlockSubmit'>
		    <input type='submit' class='btn' value='Create'/>
	    </div><!--responseBlockData-->
	</form><!--responseBlockContainer-->
    </div><!--responseBlock-->

EOF;

	return $html;
}

function replyToThreadTinyMCE()
{
	$html="";

	$formURL=basename($_SERVER['REQUEST_URI']);

	$html.=<<<EOF
    <script src='/libraries/tinymce/tinymce.min.js'></script>
    <script>
    tinymce.init({
	selector: '#response',
	theme: 'modern',
	height: 300,
	width: '100%',
	plugins: [
	'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
	'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
	'save table contextmenu directionality emoticons template paste textcolor'
	],
	content_css: 'css/content.css',
	toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons'
    });

    </script>
    <div class='responseBlock dropShadow'>
	<form class='responseBlockContainer' method='post' action='{$formURL}'>
	    <div class='responseBlockHeader'>
		<span class='lightText'><b>Quick Reply</b></span>
	    </div><!--responseBlockHeader-->
	    <div class='responseBlockData'>
		    <textarea id="response" name="formattedReply"></textarea>
	    </div><!--responseBlockData-->
	    <div class='responseBlockSubmit'>
		    <input type='submit' class='btn' value='Reply'/>
	    </div><!--responseBlockData-->
	</form><!--responseBlockContainer-->
    </div><!--responseBlock-->

EOF;

	return $html;
}


/*
 *
 *
 *
 *   Pagination Functionality
 *
 *
 */




function getPaginationParameters()
{
	if ( isset($_SESSION['numPerPage']) && !empty($_SESSION['numPerPage']))
	{
		$limit=$_SESSION["numPerPage"];
	}
	else
	{
		$limit = 100;
	}

	if (isset($_GET["page"])&& !empty($_GET["page"])) 
	{ 
		$page  = $_GET["page"]; 
	} 
	else 
	{ 
		$page=1; 
	}

	$startIndex = ($page-1) * $limit;

	return array($startIndex,$limit);
}

function generateBoardTable($dataset)
{
	$html="";

	$html.=<<<eof
    <div class="tableStyleB dropShadow center" id="table">
    <div class="threadList">
	<div class="threadHeader tableHeaderStyleB">
		<div class='deptCol'><div class='deptColText'><b>Department</b></div></div>
		<div class='unansQuestCol'><b>Un-Answered</b></div>
		<div class='totalQuestCol'><b>Total</b></div>
	</div><!--threadHeader-->
	<div class="threadBody tableRowStyleB">
eof;

	foreach($dataset as $row)
	{
		$html.="<div class='threadRow'>";
		$html.="    <div class='deptCol'>";
		$html.="        <form action='{$_SERVER['PHP_SELF']}' method='GET'>";
		$html.="            <div class='deptColText'>";
		$html.=               "<button type='submit' name='viewBoard' class='buttonToLink' value='{$row['deptid']}'>{$row['deptname']}</button>";
		$html.="            </div>";
		$html.="        </form>";
		$html.="    </div>";
		$html.="    <div class='unansQuestCol'>";
		$html.=           "{$row['unanswered_count']}";
		$html.="    </div>";
		$html.="    <div class='totalQuestCol'>";
		$html.=           "{$row['question_count']}";
		$html.="    </div>";
		$html.="    </div><!--threadRow-->";
	}
	$html.="</div><!--threadBody-->";
	$html.="</div><!--threadList-->";
	$html.="</div><!--tableContainer-->";


	return $html;
}


function generateThreadTable($dataset)
{
	$html="";

	$html.=<<<eof
    <div class="tableStyleB dropShadow center" id="table">
    <div class="threadList">
	<div class="threadHeader tableHeaderStyleB">
		<div class='statusCol'><b>Status</b></div>
		<div class='subjectCol'><b>Subject</b></div>
		<div class='classCol'><b>Class</b></div>
		<div class='infoCol'><div class='infoColText'><b>Info</b></div></div>
	</div><!--threadHeader-->
	<div class="threadBody tableRowStyleB">
eof;

	foreach($dataset as $row)
	{

		$time= date('F d, h:i a',strtotime($row['added']));

		$html.="<div class='threadRow'>";
		$html.="    <div class='statusCol'>";

		if($row['status']=='answered')
		{
			$html.="<img src='styles/img/icons/answered-question.svg' alt='Answered'>";
		}
		else
		{
			$html.="<img src='styles/img/icons/unanswered-question.svg' alt='Un-Answered'>";
		}

		$html.="    </div>";
		$html.="    <div class='subjectCol'>";
		$html.="        <form action='{$_SERVER['PHP_SELF']}' method='GET'>";
		$html.=             preserveOldGETParams();
		$html.=             "<button type='submit' name='viewThread' class='buttonToLink' value='{$row['quid']}'>{$row['title']}</button>";
		$html.="        </form>";
		$html.="    </div>";
		$html.="    <div class='classCol'>";
		$html.="{$row['classname']}";
		$html.="    </div>";
		$html.="    <div class='infoCol'>";
		$html.="        <div class='infoColText'>";
		$html.="{$row['author']}<br>";
		$html.="<div class='tableDateStyleB'>$time</div>";
		$html.="        </div>";
		$html.="    </div>";
		$html.="    </div><!--threadRow-->";

	}
	$html.="</div><!--threadBody-->";
	$html.="</div><!--threadList-->";
	$html.="</div><!--tableContainer-->";


	return $html;
}



/*
 *
 *   
 *  Generate the posts in the thread.
 *
 *
 */



function generateThread($dataset)
{
	$html="";
	$html.="\n<div class='postList'>\n";

	foreach($dataset as $row)
	{
		$html.="<div class='postBlock dropShadow'>\n";
		$html.="    <div class='postBlockUserInfoContainer'>\n";
		$html.="        <div class='postBlockUserImgContainer'>\n";
		$html.=             getPostUserImg($row['author']);
		$html.="        </div><!--postBlockUserImgContainer-->\n";
		$html.="        <div class='postBlockUserInfo'>\n";
		$html.=             getPostUserInfo($row['author']);
		$html.="        </div><!--postBlockUserInfo-->\n";
		$html.="    </div><!--postBlockUserInfo-->\n";
		$html.="    <div class='postBlockContainer'>\n";
		$html.="        <div class='postBlockHeader'>\n";
		$html.=             getPostHeader($row['author'],$row['added'],$row['is_question'],$row['status']);
		$html.="        </div><!--postBlockHeader-->\n";
		$html.="        <div class='postBlockDataContainer'>\n";
		$html.="            <div class='postBlockData'>\n";
		$html.=                 $row['body'];
		$html.="            </div><!--postBlockData-->\n";
		$html.="        </div><!--postBlockDataContainer-->\n";
		$html.="    </div><!--postBlockContainer-->\n";
		$html.="</div><!--postBlock-->\n";

	}
	$html.="</div><!--postList-->\n";

	return $html;
}


function getPostUserImg($idno)
{
	$html = "";

	$result = databaseQuery($sql="select encode(avatar,'base64')as avatar from user_avatars where user_avatars.idno=?",array($idno));

	if(is_array($result) && count($result)!=0)
	{
		$html = "           <div class='postBlockUserImg'>\n";
		$html.= "               <img src=\"data:image/png;base64," . $result[0]["avatar"] . "\"/>\n";
		$html.= "           </div>\n";
	}
	else
	{
		$html = "           <div class='postBlockUserImg'> \n";
		$html.= "               <img src='styles/img/icons/user.svg' class='defaultFill'/>\n";
		$html.= "           </div>\n";
	}

	return $html;
}

function getPostUserInfo($idno)
{
	$html = "";

	$result = databaseQuery($sql="select realname,email,role from users where idno=?",array($idno));

	if(is_array($result) && !empty($result))
	{
		$html = "           <div class=\"postBlockUserInfo\">\n";
		$html.= "               <p><em><b>{$result[0]['role']}</b></em></p>\n";
		$html.= "               <b>{$result[0]['realname']}</b>\n";
		$html.= "               <em>{$result[0]['email']}</em>\n";
		$html.= "           </div>\n";

	}

	return $html;
}

function getPostHeader($author,$date,$is_question,$status)
{
	$formURL=basename($_SERVER['REQUEST_URI']);


	$time = date('M d, h:i a',strtotime($date));
	$html = "       <div class='postBlockHeaderWrapper'>\n";
	$html.= "           <div class='postDate'>\n";
	$html.= "               <em>$time</em>\n";
	$html.= "           </div>\n";
	if($is_question==true)
	{
		if($_SESSION['useridno']==$author || isUserRoleGreaterThanOrEqualTo($_SESSION['useridno'],'tutor')==1)
		{
			$html.= "       <div class='isSolved'>\n";
			$html.= "           <form name='header' method='POST' action='{$formURL}'>\n";

			if($status=='answered')
			{
				$html.= "           <input id='solvedCheck' onChange='this.form.submit()' name='solvedCheck' type='checkbox' checked>\n";
				$html.= "           <input type='hidden' name='solvedCheck' value='-100' >\n";
				$html.= "           <label for='solvedCheck'><em><b>Solved ?</b></em></label>\n";
			}
			else
			{
				$html.= "           <input id='solvedCheck' onChange='this.form.submit()' name='solvedCheck' type='checkbox'>\n";
				$html.= "           <input type='hidden' name='solvedCheck' value='100' >\n";
				$html.= "           <label for='solvedCheck'><em><b>Solved ?</b></em></label>\n";
			}

			$html.= "           </form>\n";
			$html.= "       </div>\n";
		}
	}
	$html.= "       </div>\n";
	return $html;
}




/*
 *
 *   
 *  End Thread generation.
 *
 *
 */




function preserveOldGETParams()
{
	$html="";

	$keys = array('viewBoard', 'viewThread', 'viewPost', 'page');

	foreach($keys as $name) 
	{
		if(!isset($_GET[$name])) 
		{
			continue;
		}

		$value = htmlspecialchars($_GET[$name]);
		$name = htmlspecialchars($name);
		$html.= '<input type="hidden" name="'. $name .'" value="'. $value .'">';
	}

	return $html;
}

function printBottomPagination($paginationValues,$count)
{    
	$total_records = $count[0]['count'];

	if($paginationValues[1]==0)
	{
		$total_pages = 1;
	}
	else
	{
		$total_pages = ceil($total_records / $paginationValues[1]);
	}

	$baseurl=strtok($_SERVER["REQUEST_URI"],'?') . '?';

	foreach($_GET as $index =>$get)
	{
		if($index!='page')
			$baseurl.=$index.'='.$get.'&';
	}
	$pagLink = "<div class='pagination centerFlex'>";
	$pagLink .= "<ul><li>Page: </li>";  
	for ($i=1; $i<=$total_pages; $i++) 
	{  
		if(empty($_SERVER['QUERY_STRING']))
			$pagLink .= "<li><a href='$baseurl?page=".$i."'>".$i."</a></li>";
		else
			$pagLink .= "<li><a href='$baseurl"."page=".$i."'>".$i."</a></li>";
	}

	$pagLink.="</ul></div><br><br><br><br>";

	return $pagLink;
}

function genClass($dept)
{
	//Declare array
	$result=array();
	$class="";

	$html="";

	$sql= "select distinct name,sections.cid from terms,sections left join classes on sections.cid=classes.cid where terms.activeterm=true and dept=? and sections.term = terms.code";

	$result=databaseQuery($sql,array($dept));
	
	if(!is_array($result) || empty($result))
	{
		return "Could not find any classes. Please contact administrator.";
	}
	else
	{
		filterClasses($result);
	}

	if(isset($_POST['classSelect']) && !empty($_POST['classSelect']))
	{       
		$class=$_POST['classSelect'];
	}

	//Generate the html code for the class selection box
	$html.= "<select name=\"classSelect\" class=\"inputSelect\">";

	foreach($result as $row)
	{
		#$row = ;
		if ($class == $row["cid"])
		{
			$html.= "<option value=\"" . $row["cid"] . "\" selected>" . $row["name"]  . "</option>";
		}
		else
		{
			$html.= "<option value=\"" . $row["cid"]  . "\">" . $row["name"]  . "</option>";
		}
	}
	$html.= "</select>";


	//Send the "string" of html code back to the calling function
	return $html;
}

function filterClasses()
{
	//Filter out anything not accessable b y students
	$curRole=getUserLevelAccessIdno($_SESSION['useridno']);

	
	if($curRole=='student')
	{
		//Loof for anything that contains the word tutor, and mark the in a new array
		if (($key = preg_grep("/(tutor)/", array_map('strtolower',array_column($result,'name')))) !== false) 
		{

			//Remove anything matching the expression from the array 
			foreach($key as $row)
			{
				if(($newKey = array_search($row,array_map('strtolower',array_column($result,'name')))) !== false)
				{
					unset($result[$newKey]);
				}
			}
			//Re-index the array
			$result = array_values($result);
		}
	}
}

/*
 *
 *
 *
 *   Database Queries
 *
 *
 */

function updateQuestionStatus($array)
{
	$result=databaseQuery("update questions set status=? where quid=?",$array);

	if(!is_array($result) || empty($result))
	{
		return -1;
	}
	else
	{
		return 1;
	}

}

function getQuestionName($array)
{
	$result=databaseQuery("select title from questions where quid=?",$array);

	if(!is_array($result) || empty($result))
	{
		return "Question not found";
	}
	else
	{
		return $result[0]['title'];
	}
}


function createResponse($array)
{
	$result=databaseQuery("insert into posts (question,author,body,is_question,added) values (?,?,?,false,CURRENT_TIMESTAMP)",$array);

	if(is_array($result))
	{
		return 1;
	}
	else
	{
		return -1;
	}
}

function createThread($array, $array2)
{
	$query =<<<'SQL'
INSERT INTO questions(subject, term, title, asker, status, added)
	VALUES(?, (SELECT code FROM terms WHERE terms.activeterm = true), ?, ?, 'awaiting_response', CURRENT_TIMESTAMP) 
	RETURNING quid
SQL;

	$result = databaseQuery($query, $array);


	if(is_array($result))
	{

		$temp=$result[0]['quid'];
		array_unshift($array2,$temp);

		$result=databaseQuery("insert into posts (question,author,body,is_question,added) values (?,?,?,true,CURRENT_TIMESTAMP)",$array2);


		if(is_array($result))
		{
			return 1;
		}
		else
		{
			return -1;
		}

	}
	else
	{
		return -1;
	}
}

function getThreadList($array)
{
	$query = <<<'SQL'
WITH filt_classes AS (
	SELECT * FROM classes WHERE classes.dept = ?
)
SELECT questions.quid as quid, filt_classes.name AS classname, questions.title, users.realname AS author,
	questions.status, posts.added
	FROM questions
	JOIN filt_classes  ON questions.subject = filt_classes.cid
	JOIN users         ON questions.asker   = users.idno
	JOIN posts         ON questions.quid    = posts.question
	WHERE posts.added = (SELECT MAX(posts.added) FROM posts WHERE posts.question = questions.quid)
		AND questions.term = (SELECT code FROM terms WHERE terms.activeterm = true)
	ORDER BY posts.added DESC
	OFFSET ? LIMIT ?
SQL;

	return databaseQuery($query, $array);
}

function getBoardList($array)
{
	return databaseQuery("select * from forum_overview offset ? limit ?",$array);
}

function getQuestionList($array)
{
	return databaseQuery("select posts.postid,posts.question,posts.author,posts.body,posts.is_question,posts.added,questions.status from posts,questions where questions.quid=posts.question and posts.question=? order by added offset ? limit ?",$array);
}

function getPaginationCountThread($array)
{
	return databaseQuery("select count(questions.title) as count from questions,terms,sections,classes where questions.subject = sections.secid and sections.term=terms.code and sections.cid=classes.cid and classes.dept=? and activeterm=true ",$array);
}

function getPaginationCountBoard()
{
	return databaseExecute("select count(deptid) as count from departments");
}

function getPaginationCountQuestion($array)
{
	return databaseQuery("select count(body) as count from posts where question=?",$array);
}




// function createSearchBar()
// {
// 	//Search options
// 	$options=array(array('Title','title'),array('Class','cid'),array('User','username'));
// 	$prevVal= $options[0][1];
// 
// 	//Restore val if present, or use default
// 	if(isSet($_GET['searchSelect']) && !empty($_GET['searchSelect']))
// 	{
// 		$prevVal=$_GET['searchSelect'];
// 	}
// 
// 	$html = "<form action='manageusers.php' method='GET'><div class='flex rightAlignFlex padding20Bottom' >";
// 
// 	if(isSet($_GET['searchSubmit']))
// 	{
// 		$html.= "   <div class=' searchContainer flex' id='searchMaster'>";    
// 	}
// 	else
// 	{
// 		$html.= "   <div class=' searchContainer flex' id='searchMaster' style='display:none;'>";
// 	}
// 
// 
// 	//$html.= "       <div class='flexSearchColumn'>";
// 	$html.= "       <div></div>";
// 	$html.= "       <div class='flexSearchRow'>";
// 	$html.= "           <div >Category: </div> ";
// 	$html.= "            <div> <select name='searchSelect' class='inputSelectSmall'>";
// 
// 	foreach($options as $row)
// 	{
// 		if ($prevVal == $row[1])
// 		{
// 			$html.= "<option value=\"" . $row[1] . "\" selected>" . $row[0]  . "</option>";
// 		}
// 		else
// 		{
// 			$html.= "<option value=\"" . $row[1]  . "\">" . $row[0]  . "</option>";
// 		}
// 	}
// 
// 	$html.= "           </select>";
// 	$html.= "           </div>";
// 	$html.= "       </div>";
// 	$html.= "       <div class='flexSearchRow'>";
// 	$html.= "           <div >Search: </div>";
// 	$html.= "           <div><input class='inputprimary' placeholder='Use % for wildcard search' name='searchText' type='text'/></div>";
// 	$html.= "       </div>";
// 	$html.= "       <div class= 'flexSearchRow'>";
// 	$html.= "           <input class= 'btn btnleft' type='submit' name='searchSubmit' value='Search'>";
// 	$html.= "           <input class= 'btn btnright' type='submit' name='searchReset' value='Reset'>";
// 	$html.= "       </div>";
// 	$html.= "   </div>";
// 	$html.= "</div></form>";
// 
// 	return $html;
// }
// 
// function searchResults()
// {
// 	$html = "<script type='text/javascript' src='scripts/manageusers.js'></script>";
// 	$html.= "<div class='flex columnLayout2 alignCenterFlex'>";
// 	$html.= "<div><h3>Users:</h3></div>";
// 	$html.= "<div id='searchIcon'><a href='javascript:showHideSearch()'><img src='styles/img/icons/search.svg' alt='Show/Hide search'></a></div>";
// 	$html.= "</div><div class=\"\">";
// 	$html.= createSearchBar();
// 	$html.= generatePaginatedTableSearch();
// 
// 	$html .="</div>";
// 	return $html;
// }

// function paginatedThreadListingOLD()
// {
// 
// 	$html="";
// 
// 	$paginationParams=array_merge(array('CS-IS'),getPaginationParameters());
// 
// 	$dataset=getThreadList($paginationParams);
// 
// 	$html.=<<<eof
//     <div class="tableStyleB dropShadow center" id="table">
//     <form class="" action="manageusers.php" method="post">
//     <table>
// 	<thead>
// 	    <tr>
// 		<th class='statusCol'>Status</th>
// 		<th class='subjectCol'>Subject </th>
// 		<th class='classCol'>Class</th>
// 		<th class='infoCol'><div class='infoColText'>Info</div></th>
// 	    </tr>
// 	</thead>
// 	<tbody>
// eof;
// 
// 	foreach($dataset as $row)
// 	{
// 		$html.="<a href='{$_SERVER['PHP_SELF']}'>";
// 		$html.="<tr>";
// 
// 		if($row['status']=='answered')
// 		{
// 			$html.="    <td class='statusCol'>v</td>";
// 		}
// 		else
// 		{
// 			$html.="    <td class='statusCol'>X</td>";
// 		}
// 
// 		$html.="    <td class='subjectCol'>". $row['title'] ."</td>";
// 		$html.="    <td class='classCol'>". $row['classname'] ."</td>";
// 		$html.="    <td class='infoCol'><div class='infoColText'>". $row['author'] ."</div></td>";
// 		$html.="</tr>";
// 		$html.="</a>";
// 	}
// 	$html.="</tbody></table>";
// 
// 	return $html;
// }

// function generateSearchSql()
// {
// 	$options=array(array('ID#','idno'),array('Department','deptid'),array('Username','username'),array('Full Name','realname'),array('E-mail','email'),array('Role','role'));
// 
// 	if(isSet($_GET['searchSelect']) &&!empty($_GET['searchSelect']))
// 	{
// 		$search=$_GET['searchSelect'];
// 	}
// 	else
// 	{
// 		return "Search error";
// 	}
// 
// 	foreach($options as $row)
// 	{
// 		if ($search == $row[1])
// 		{
// 			if($search=='role')
// 				$sql="select * from users where ". $row[1] ."=? order by role desc, username OFFSET ? LIMIT ? ";
// 			else
// 				$sql="select * from users where ". $row[1] ." ilike ? order by role desc, username OFFSET ? LIMIT ? ";
// 
// 			return $sql;
// 		}
// 	}
// 
// 	return "Search error";
// 
// }

// function getSearchList($sql,$array)
// {
// 	return databaseQuery($sql,$array);
// }

?>
