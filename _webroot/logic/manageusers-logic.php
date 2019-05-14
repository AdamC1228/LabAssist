<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";



/*
 * Display all of the data.
 */
function displayAll()
{
	$html = "<script type='text/javascript' src='scripts/searchbar.js'></script>";
	$html.= "<div class='flex columnLayout2 alignCenterFlex'>";
	$html.= "<div><h3>Users:</h3></div>";
	$html.= "<div id='searchIcon'><a href='javascript:showHideSearch()'><img src='styles/img/icons/search.svg' alt='Show/Hide search'></a></div>";
	$html.= "</div><div class=\"\">";
	$html.= createSearchBar();
	$html.= generatePaginatedTable();

	$html .="</div>";
	return $html;
}

/*
 * Create the search bar.
 */
function createSearchBar()
{

	//Search options
	$options=array(array('ID#','idno'),array('Department','deptid'),array('Username','username'),array('Full Name','realname'),array('E-mail','email'),array('Role','role'));
	$prevVal= $options[0][1];

	//Restore val if present, or use default
	if(isSet($_GET['searchSelect']) && !empty($_GET['searchSelect']))
	{
		$prevVal=$_GET['searchSelect'];
	}


	$html = "<form action='manageusers.php' method='GET'><div class='flex rightAlignFlex padding20Bottom' >";

	if(isSet($_GET['searchSubmit']))
	{
		$html.= "   <div class=' searchContainer flex' id='searchMaster'>";    
	}
	else
	{
		$html.= "   <div class=' searchContainer flex' id='searchMaster' style='display:none;'>";
	}


	//$html.= "       <div class='flexSearchColumn'>";
	$html.= "         <div></div>";
	$html.= "           <div class='flexSearchRow'>";
	$html.= "               <div >Category: </div> ";
	$html.= "               <div> <select name='searchSelect' class='inputSelectSmall'>";

	foreach($options as $row)
	{
		if ($prevVal == $row[1])
		{
			$html.= "<option value=\"" . $row[1] . "\" selected>" . $row[0]  . "</option>";
		}
		else
		{
			$html.= "<option value=\"" . $row[1]  . "\">" . $row[0]  . "</option>";
		}
	}

	$html.= "                   </select>";
	$html.= "               </div>";
	$html.= "           </div>";
	$html.= "           <div class='flexSearchRow'>";
	$html.= "               <div >Search: </div>";
	$html.= "               <div><input class='inputprimary' placeholder='Use % for wildcard search' name='searchText' type='text'/></div>";
	$html.= "           </div>";
	$html.= "           <div class= 'flexSearchRow'>";
	$html.= "               <input class= 'btn btnleft' type='submit' name='searchSubmit' value='Search'>";
	$html.= "               <input class= 'btn btnright' type='submit' name='searchReset' value='Reset'>";
	//$html.= "           </div>";
	$html.= "       </div>";
	$html.= "   </div>";
	$html.= "</div></form>";

	return $html;
}

/*
 * Display search results.
 */
function searchResults()
{
	$html = "<script type='text/javascript' src='scripts/manageusers.js'></script>";
	$html.= "<div class='flex columnLayout2 alignCenterFlex'>";
	$html.= "<div><h3>Users:</h3></div>";
	$html.= "<div id='searchIcon'><a href='javascript:showHideSearch()'><img src='styles/img/icons/search.svg' alt='Show/Hide search'></a></div>";
	$html.= "</div><div class=\"\">";
	$html.= createSearchBar();
	$html.= generatePaginatedTableSearch();

	$html .="</div>";
	return $html;
}

/*
 * Edit a user.
 */
function editUser($userID)
{
	return genEditForm($userID);
}

/*
 * Generate 'edit-user' form.
 */
function genEditForm($userID)
{
	$html=<<<eof
    <h3> Edit User</h3>
    <div class="group ">
	<form action = "manageusers.php" method="post">
	    <div class= "flex3columns">
eof;

	$html.=formattedUserInformation($userID);
	$html.="</div></form></div>";



	return $html;
}


/*
 * Display formatted user info.
 */
function formattedUserInformation($userID)
{
	$dataArray=getDetailedUserInfo($userID);

	if(empty($dataArray))
	{
		return "<br><b>User does not exist</b><br>";
	}
	else if($dataArray==-1)
	{
		return "<br><b>Internal database error. Please contact system administrator or go back and try again.</b><br>";
	}




	//Formatted Image
	$html= "<div id=\"column1\">";
	$html.="<p><b>Logo: </b></p></div>";
	$html.= "<div id=\"column2\" class=\"widthper\"><div class=\"userIconLarge\"><div class=\"userLogoWrapper\">";

	if(is_null($dataArray[0]["avatar"]))
	{
		$html.= "<img style='background-color:gray;'src='styles/img/icons/user.svg'/> </div></div></div>";
	}
	else
	{
		$html.= "<img src=\"data:image/png;base64," . $dataArray[0]["avatar"] . "\"/> </div></div></div>";
	}

	//Formatted userData
	$html.= "<div id=\"column3\">";
	$html.= "<p><b>Name: </b></p>";
	$html.= "<p><b>Username: </b></p>";
	$html.= "<p><b>E-mail: </b></p>";
	$html.= "</div>";

	$html.= "<div id=\"column4\">";
	$html.= "<p>" . $dataArray[0]['realname'] . "</p>";
	$html.= "<p>" . $dataArray[0]['username'] . "</p>";
	$html.= "<p>" . $dataArray[0]['email'] . "</p>";
	$html.= "</div>";

	$html.= "<div id=\"column5\">";
	$html.= "<p><b>Role: </b></p>";
	$html.= "<p><b>Department: </b></p>";
	$html.= "</div>";

	$html.= "<div id=\"column6\">";

	if(is_null($dataArray[0]['deptid']) || empty($dataArray[0]['deptid']))
	{
		$dataArray[0]['deptid'] = "NULL";
	}

	$html.= "<p>" . $dataArray[0]['role'] . "</p>";
	$html.= "<p>" . $dataArray[0]['deptid'] . "</p>";
	$html.= "</div>";


	$html.= "<div id=\"column7\">";
	$html.= "<b><p>Set Role: </p>";
	$html.= "<p>Set Department: </p></b>";
	$html.= "</div>";

	$html.= "<div id=\"column8\">";
	$html.= getaccessLevelSelect($dataArray[0]['role']);
	$html.= "<br><br>";
	$html.= getDepartmentList($dataArray[0]['deptid']);
	$html.= "<div>";
	$html.= "<button class=\"btn margin20top btnleft\" name=\"cancelEdit\" type=\"submit\" value='cancelEdit'>Cancel</button>";
	$html.= "<button class=\"btn margin20top btnright\" name=\"submitEdit\" type=\"submit\" value='$userID'>Save</button>";
	$html.= "</div>";
	$html.= "</div>";

	return $html;
}


/*
 * Generate the table.
 */
function generateTable()
{
	$dataset=getUserList();

	if(empty($dataset))
	{
		return "<p>Database error<p>";
	}

	$html=<<<eof
	<div class="tableStyleA dropShadow center" id="table">
	<form class="" action="manageusers.php" method="post">
	<table>
	    <thead>
		<tr>
		    <th>ID#</th>
		    <th>User Name </th>
		    <th>Full name</th>
		    <th>E-mail</th>
		    <th>Department</th>
		    <th>Role</th>
		    <th>Action</th>
		</tr>
	    </thead>
eof;
	foreach ($dataset as $row)
	{
		$html.="<tr>";
		$html.="    <td>". $row['idno'] ."</td>";
		$html.="    <td>". $row['username'] ."</td>";
		$html.="    <td>". $row['realname'] ."</td>";
		$html.="    <td>". $row['email'] ."</td>";
		$html.="    <td>". $row['deptid'] ."</td>";
		$html.="    <td data-field=\"role\">". $row['role'] ."</td>";

		//Prevent modification of other users if they have equal or greater power
		if(compareAccessLevelbyName($_SESSION['username'],$row['username'])==1)
		{
			$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"".$row["idno"]."\">Edit</button></td>";
		}
		else
		{
			$html.="<td> </td";
		}
		$html.="</tr>";
	}

	$html.="</table></form></div>";

	return $html;
}

/*
 *
 *
 *
 *   Search Functionality
 *
 *
 */



/*
 *
 *
 *
 *   Pagination Functionality
 *
 *
 */

/*
 * Get pagination parameters.
 */
function getPaginated()
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


/*
 * Generate the paginated table.
 */
function generatePaginatedTable()
{

	$paginationValues=getPaginated();

	$dataset=getUserList($paginationValues);


	if(empty($dataset))
	{
		return "<p>Database error<p>";
	}

	$html="";
/*    
    $html="<div class='pagination rightAlignFlex zeroTopList'>";
    $html.=printTopPagination($paginationValues);
$html.="</div>";*/

	$html.=<<<eof
	<div class="tableStyleA dropShadow center" id="table">
	<form class="" action="manageusers.php" method="post">
	<table>
	    <thead>
		<tr>
		    <th>ID#</th>
		    <th>Username </th>
		    <th>Full Name</th>
		    <th>E-mail</th>
		    <th>Department</th>
		    <th>Role</th>
		    <th>Action</th>
		</tr>
	    </thead>
eof;
	foreach ($dataset as $row)
	{
		$html.="<tr>";
		$html.="    <td>". $row['idno'] ."</td>";
		$html.="    <td>". $row['username'] ."</td>";
		$html.="    <td>". $row['realname'] ."</td>";
		$html.="    <td>". $row['email'] ."</td>";
		$html.="    <td>". $row['deptid'] ."</td>";
		$html.="    <td data-field=\"role\">". $row['role'] ."</td>";

		//Prevent modification of other users if they have equal or greater power
		if(compareAccessLevelbyName($_SESSION['username'],$row['username'])==1)
		{
			$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"".$row["idno"]."\">Edit</button></td>";
		}
		else
		{
			$html.="<td> </td";
		}
		$html.="</tr>";
	}

	$html.="</table>";
	$html.="</form></div><div class='pagination centerFlex'>";
	$html.=printBottomPagination($paginationValues);
	$html.="</div>";

	return $html;
}

/*
 * Generate the search SQL.
 */
function generateSearchSql()
{
	$options=array(array('ID#','idno'),array('Department','deptid'),array('Username','username'),array('Full Name','realname'),array('E-mail','email'),array('Role','role'));

	if(isSet($_GET['searchSelect']) &&!empty($_GET['searchSelect']))
	{
		$search=$_GET['searchSelect'];
	}
	else
	{
		return "Search error";
	}

	foreach($options as $row)
	{
		if ($search == $row[1])
		{
			if($search=='role')
				$sql="select * from users where ". $row[1] ."=? order by role desc, username OFFSET ? LIMIT ? ";
			else
				$sql="select * from users where ". $row[1] ." ilike ? order by role desc, username OFFSET ? LIMIT ? ";

			return $sql;
		}
	}

	return "Search error";

}

/*
 * Generate a paginated search table.
 */
function generatePaginatedTableSearch()
{

	//Make sure that the search text is not null
	if(!isSet($_GET['searchText']) || empty($_GET['searchText']))
	{
		return "Must specify search parameter!";
	}



	$paginationValues=getPaginated();

	array_unshift($paginationValues,$_GET['searchText']);

	$dataset=getSearchList(generateSearchSql(),$paginationValues);


	if(empty($dataset) || !is_array($dataset))
	{
		return "<p>No results found!<p>";
	}



	$html="";
/*    
    $html="<div class='pagination rightAlignFlex zeroTopList'>";
    $html.=printTopPagination($paginationValues);
$html.="</div>";*/

	$html.=<<<eof
	<div class="tableStyleA dropShadow center" id="table">
	<form class="" action="manageusers.php" method="post">
	<table>
	    <thead>
		<tr>
		    <th>ID#</th>
		    <th>Username </th>
		    <th>Full Name</th>
		    <th>E-mail</th>
		    <th>Department</th>
		    <th>Role</th>
		    <th>Action</th>
		</tr>
	    </thead>
eof;
	foreach ($dataset as $row)
	{
		$html.="<tr>";
		$html.="    <td>". $row['idno'] ."</td>";
		$html.="    <td>". $row['username'] ."</td>";
		$html.="    <td>". $row['realname'] ."</td>";
		$html.="    <td>". $row['email'] ."</td>";
		$html.="    <td>". $row['deptid'] ."</td>";
		$html.="    <td data-field=\"role\">". $row['role'] ."</td>";

		//Prevent modification of other users if they have equal or greater power
		if(compareAccessLevelbyName($_SESSION['username'],$row['username'])==1)
		{
			$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"".$row["idno"]."\">Edit</button></td>";
		}
		else
		{
			$html.="<td> </td";
		}

		$html.="</tr>";
	}

	$html.="</table>";
	$html.="</form></div><div class='pagination centerFlex'>";
	$html.=printBottomPagination($paginationValues);
	$html.="</div>";

	return $html;
}

/*
 * PRint bottom pagination values.
 */
function printBottomPagination($paginationValues)
{    
	$count = getNumUsers();


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

	$pagLink = "<ul><li>Page: </li>";  
	for ($i=1; $i<=$total_pages; $i++) 
	{  
		if(empty($_SERVER['QUERY_STRING']))
			$pagLink .= "<li><a href='$baseurl?page=".$i."'>".$i."</a></li>";
		else
			$pagLink .= "<li><a href='$baseurl"."page=".$i."'>".$i."</a></li>";
	}

	$pagLink.="</ul>";

	return $pagLink;
}


/*
 *
 *
 *
 *   Database Queries
 *
 *
 */


/*
 * Get user access level.
 */
function getaccessLevelSelect($currentRole)
{
	$html = "";

	$result=databaseExecute("select enum_range(null::role)");

	if(empty($result))
	{
		$html = "Unable to fetch roles. Contact System administrator.";
	}
	else
	{
		//Get array remove preceding and postceeding {}
		$temp = substr($result[0]['enum_range'],1,-1);
		//Convert to array of items
		$array = explode( ',', $temp );

		$array=filterRoleList($array);

		//Create select statement
		$html="<select name='role' class='inputSelectLarge'>";

		foreach($array as $row)
		{
			#$row = ;
			if ($currentRole == $row)
			{
				$html.= "<option value=\"" . $row . "\" selected>" . $row  . "</option>";
			}
			else
			{
				$html.= "<option value=\"" . $row  . "\">" . $row  . "</option>";
			}
		}

		$html .= "</select>";

	}

	return $html;
}


/*
 * Get user department list.
 */
function getDepartmentList($currentDepartment)
{
	$html = "";
	$sql="select * from departments";
	$result = databaseExecute($sql);

	if(empty($result))
	{
		$html = "Unable to fetch roles. Contact system administrator.";
	}
	else
	{            
		//Create select statement
		$html="<select name='department' class='inputSelectLarge'>";

		foreach($result as $row)
		{
			if ($currentDepartment == $row["deptid"])
			{
				$html.= "<option value=\"" . $row["deptid"] . "\" selected>" . $row["deptname"]  . "</option>";
			}
			else
			{
				$html.= "<option value=\"" . $row["deptid"]  . "\">" . $row["deptname"]  . "</option>";
			}
		}

		$html .= "</select>";
	}
	return $html;
}


/*
 * Filter the role list.
 */
function filterRoleList($dataSet)
{
	$currentRole = getUserLevelAccess($_SESSION['username']);
	$newSet = array();

	foreach($dataSet as $value)
	{
		if($currentRole==$value)
		{
			break;
		}
		else
		{
			array_push($newSet,$value);
		}
	}

	unset($newSet[0]);
	return array_values($newSet);
}


/*
 * Get detailed user information.
 */
function getDetailedUserInfo($userID)
{
	$sql="select users.idno,deptid,username,realname,email,role,encode(avatar,'base64') as avatar from users left join user_avatars on user_avatars.idno=users.idno where users.idno=?";

	$array=array($userID);

	return databaseQuery($sql,$array);
}


/*
 * Get the list of users.
 */
function getUserList($array)
{
	return databaseQuery("select * from users order by role desc, username OFFSET ? LIMIT ?",$array);
}

/*
 * Get the search list.
 */
function getSearchList($sql,$array)
{
	return databaseQuery($sql,$array);
}

/*
 * Get the number of users.
 */
function getNumUsers()
{
	return databaseExecute("select count(idno) as count from users");
}

/*
 * Submit edits to the database.
 */
function databaseSubmitEdits($idno,$role,$department)
{
	$sql="update users set role=?, deptid=? where idno=?";

	$result=databaseQuery($sql,array($role,$department,$idno));

	return $result;
}

?>
