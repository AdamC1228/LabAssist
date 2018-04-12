<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";



function displayAll()
{
	$html = "<script type='text/javascript' src='scripts/searchbar.js'></script>";
	$html.= "<div class='flex columnLayout2 alignCenterFlex'>";
	$html.= "<div><h3>Classes:</h3></div>";

	$html.= "<div id='searchIcon'><a href='javascript:showHideSearch()'><img src='styles/img/icons/search.svg' alt='Show/Hide search'></a></div>";
	$html.= "</div><div class=\"\">";
	$html.= createSearchBar();
	$html.= generatePaginatedTable();

	$html .="</div>";
	return $html;
}

function createSearchBar()
{

	//Search options
	$options=array(array('Department','dept'),array('Class name','name'));
	$prevVal= $options[0][1];

	//Restore val if present, or use default
	if(isSet($_GET['searchSelect']) && !empty($_GET['searchSelect']))
	{
		$prevVal=$_GET['searchSelect'];
	}


	$html = "<form action='manageclasses.php' method='GET'><div class='flex rightAlignFlex padding20Bottom' >";

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

function searchResults()
{
	$html = "<script type='text/javascript' src='scripts/searchbar.js'></script>";
	$html.= "<div class='flex columnLayout2 alignCenterFlex'>";
	$html.= "<div><h3>Users:</h3></div>";
	$html.= "<div id='searchIcon'><img src='styles/img/icons/search.svg' alt='Show/Hide search'></a></div>";
	$html.= "</div><div class=\"\">";
	$html.= createSearchBar();
	$html.=generatePaginatedTableSearch();

	$html .="</div>";
	return $html;
}

function editEntry($uniqueID)
{
	return genEditForm($uniqueID);
}

function genEditForm($uniqueID)
{
	$html=<<<eof
	    <div class="flex ">
		<div>
		    <h3> Edit Class</h3>
		    <div class="group paddingRight20">
eof;
	$html.=formattedInformation($uniqueID);
	$html.="        </div>
		</div>        
		";
	$html.=printSectionsAssociated($uniqueID);
	$html.="</div>";

	return $html;
}


function formattedInformation($uniqueID)
{
	$dataArray=getDetailedInfo($uniqueID);

	if(empty($dataArray))
	{
		return "<br><b>Class does not exist</b><br>";
	}
	else if($dataArray==-1)
	{
		return "<br><b>Internal database error. Please contact system administrator or go back and try again.</b><br>";
	}

	$html = "";

	$html.= "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
	$html.= "   <div class='flex paddingLeft20 '>";
	$html.= "       <div class='flexRightAlign paddingRight20 '>";
	$html.= "           <div style='padding-bottom:1px; padding-top:3px;'><p>Class Name: </p></div>";
	$html.= "           <div style='margin:auto;'><p>Department: </p></div>";
	$html.= "       </div>";
	$html.= "       <div class='flexLeftAlign paddingTop10 flexOne'>";
	$html.= "           <input class='inputprimary marginBottom20' placeholder='Catalog Name' value='{$dataArray[0]['name']}' name='className' type='text'/><br>";
	$html.=             getDepartmentList();
	$html.= "       </div>";
	$html.= "   </div>";
	$html.= "   <div class='flex centerFlex paddingBottom20 '>";
	$html.= "<button class=\"btn margin20top btnleft\" name=\"cancelEdit\" type=\"submit\" value='cancelEdit'>Cancel</button>";
	$html.= "<button class=\"btn margin20top btnright\" name=\"submitEdit\" type=\"submit\" value='$uniqueID'>Update</button>";
	$html.= "   </div>";
	$html.= "</form>";

	return $html;
}

function getDepartmentList()
{
	$html = "";
	$currentDepartment= getUsersDepartment(array($_SESSION['username']));
	if(!is_array($currentDepartment))
	{
		return "An error has occured getting the available departments";
	}

	$sql="select * from departments where deptid=?";
	$result = databaseQuery($sql,array($currentDepartment[0]['deptid']));

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

// function genEditForm($uniqueID)
// {
//     $html=<<<eof
//             <div class="flex ">
//                 <div>
//                     <h3> Edit Class</h3>
//                     <div class="group paddingRight20">
//                         <form action = "manageclasses.php" method="post">
//                             <div class= "flex3columns marginRight20">
// eof;
//     
//     $html.=formattedInformation($uniqueID);
// 
//     $html.="                </div>
//                         </form>
//                     </div>
//                 </div>
// 
//                 
// ";
// 
//     $html.=printSectionsAssociated($uniqueID);
// 
//     $html.="</div>";
//     return $html;
// }

// function formattedInformation($uniqueID)
// {
//     $dataArray=getDetailedInfo($uniqueID);
// 
//     if(empty($dataArray))
//     {
//         return "<br><b>Class does not exist</b><br>";
//     }
//     else if($dataArray==-1)
//     {
//         return "<br><b>Internal database error. Please contact system administrator or go back and try again.</b><br>";
//     }
// 
//     //Formatted userData
//     $html = "<div class='paddingLeft20' id=\"column3\">";
//     $html.= "<p><b>Department: </b></p>";
//     $html.= "<p><b>Class ID #: </b></p>";
//     $html.= "</div>";
//     
//     $html.= "<div id=\"column4\">";
//     $html.= "<p>" . $dataArray[0]['dept'] . "</p>";
//     $html.= "<p>" . $dataArray[0]['cid'] . "</p>";
//     $html.= "</div>";
//     
//     $html.= "<div class=\"paddingLeft100\">";
// 
//     $html.= "<p><b>Class name:</b></p>";
//     $html.= "</div>";
//     
//     $html.= "<div id=\"column6\" class='paddingTop10'>";
//     $html.= "<input maxlength='254' name='className' type='text' class='inputprimary ' placeholder='ex. Applied Mathematics' value='" . $dataArray[0]['name'] . "'>";
//     $html.= "<div>";
//     $html.= "<button class=\"btn margin20top btnleft\" name=\"cancelEdit\" type=\"submit\" value='cancelEdit'>Cancel</button>";
//     $html.= "<button class=\"btn margin20top btnright\" name=\"submitEdit\" type=\"submit\" value='$uniqueID'>Update</button>";
//     $html.= "</div>";
//     $html.= "</div>";
//     
//     return $html;
// }

function generateTable($dataset)
{
    if(empty($dataset))
    {
	return "<p>Database error<p>";
    }

    $html=<<<eof
	<div class="tableStyleA center flexGrow flexAlignSelf marginLeft20" id="table">
	<h3>Sections taught this term:</h3>
	<form class="" action="manageclasses.php" method="post">
	<table class= "dropShadow ">
	    <thead>
		<tr>
		    <th>Section ID</th>
		    <th>Section Code</th>
		    <th>Professor</th>
		    <th>Email</th>
		</tr>
	    </thead>
eof;
    foreach ($dataset as $row)
    {
	    $html.="<tr>";
	    $html.="    <td>". $row['secid'] ."</td>";
	    $html.="    <td>". $row['code'] ."</td>";
	    $html.="    <td>". $row['realname'] ."</td>";
	    $html.="    <td>". $row['email'] ."</td>";
	    $html.="</tr>";
    }

    $html.="</table></form></div>";

    return $html;
}

function printSectionsAssociated($uniqueID)
{
	return generateTable(databaseQuery("select secid,sections.code,term,realname,email from sections,users,terms where sections.term=terms.code and sections.term=(select terms.code from terms where activeterm='true') and (sections.cid=? and users.idno=sections.teacher)",array($uniqueID)));
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


function generatePaginatedTable()
{
	$paginationValues=getPaginated();

	$dataset=getDataSet($paginationValues);


	if(empty($dataset))
	{
		return "<p>Database error<p>";
	}


	$html="";

	$html.=<<<eof
	<div class="tableStyleA  center" id="table">
	<form class="dropShadow" action="manageclasses.php" method="post">
	<table>
	    <thead>
		<tr>
		    <th>Class ID</th>
		    <th>Department </th>
		    <th>Class name </th>
		    <th>No. Sections</th>
		    <th>Action</th>
		</tr>
	    </thead>
eof;

	foreach ($dataset as $row)
	{
		$html.="<tr>";
		$html.="    <td>". $row['cid'] ."</td>";
		$html.="    <td>". $row['dept'] ."</td>";
		$html.="    <td>". $row['name'] ."</td>";
		$html.="    <td>". $row['sectioncount'] ."</td>";

		//Prevent modification of other users if they have equal or greater power
		if(doesUserBelongToDept($_SESSION['username'],$row['dept'])==1)
		{
			$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"".$row["cid"]."\">Edit</button></td>";
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

function generateSearchSql()
{
	$options=array(array('Department','dept'),array('Class name','name'));

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
				$sql="select classes.cid, classes.dept, classes.name, count(sections.secid) as sectioncount  FROM (classes LEFT JOIN sections ON classes.cid = sections.cid) where ". $row[1] ."=? group BY classes.cid order by classes.dept OFFSET ? LIMIT ? ";
			else
				$sql="select classes.cid, classes.dept, classes.name, count(sections.secid) as sectioncount  FROM (classes LEFT JOIN sections ON classes.cid = sections.cid) where ". $row[1] ." ilike ? group BY classes.cid order by classes.dept OFFSET ? LIMIT ? ";

			return $sql;
		}
	}

	return "Search error";

}

function generatePaginatedTableSearch()
{

	//Make sure that the search text is not null
	if(!isSet($_GET['searchText']) || empty($_GET['searchText']))
	{
		return "Must specify search parameter!";
	}



	$paginationValues=getPaginated();

	array_unshift($paginationValues,$_GET['searchText']);

	var_dump(generateSearchSql());
	var_dump($paginationValues);
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
	<form class="" action="manageclasses.php" method="post">
	<table>
	    <thead>
		<tr>
		    <th>Class ID</th>
		    <th>Department </th>
		    <th>Class name </th>
		    <th>No. Sections</th>
		    <th>Action</th>
		</tr>
	    </thead>
eof;
	foreach ($dataset as $row)
	{
		$html.="<tr>";
		$html.="<tr>";
		$html.="    <td>". $row['cid'] ."</td>";
		$html.="    <td>". $row['dept'] ."</td>";
		$html.="    <td>". $row['name'] ."</td>";
		$html.="    <td>". $row['sectioncount'] ."</td>";

		//Prevent modification of other users if they have equal or greater power
		if(doesUserBelongToDept($_SESSION['username'],$row['dept'])==1)
		{
			$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"".$row["cid"]."\">Edit</button></td>";
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
		$html="<select name='role' class='inputSelect'>";

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


function getDetailedInfo($uniqueID)
{
	$sql="select * from classes where cid=?";


	return databaseQuery($sql,array($uniqueID));
}

function getDataSet($array)
{
	return databaseQuery("SELECT classes.cid, classes.dept, classes.name, count(sections.secid) as sectioncount FROM classes LEFT JOIN sections ON classes.cid = sections.cid group BY classes.cid order by classes.dept OFFSET ? LIMIT ?",$array);
}

function getSearchList($sql,$array)
{
	return databaseQuery($sql,$array);
}

function getNumUsers()
{
	return databaseExecute("select count(cid) as count from classes");
}

function databaseSubmitEdits($idno,$name)
{
	$sql="update classes set name=? where cid=?";

	$result=databaseQuery($sql,array($name,$idno));

	return $result;
}

?>
