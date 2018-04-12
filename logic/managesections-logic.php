<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";



function displayAll()
{
	$html = "<script type='text/javascript' src='scripts/searchbar.js'></script>";
	$html.= "<div class='flex columnLayout2 alignCenterFlex'>";
	$html.= "<div><h3>Manage Sections:</h3> </div>";

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


	$html = "<form action='{$_SERVER['PHP_SELF']}' method='GET'><div class='flex rightAlignFlex padding20Bottom' >";

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
	$html="<div class='flex '><div>";

	if(isset($_SESSION['selectedSection']) && !empty($_SESSION['selectedSection']))
	{
		$html.=<<<eof
		    <h3> Edit Class</h3>
		    <div class="group paddingRight20">
eof;

		$html.=formattedInformation($_SESSION['selectedSection']);
	}
	else
	{
		//Dont show edit if no section is selected.
	}


	$html.="        </div>
		</div>        
		";
	$html.=printSectionsAssociated($uniqueID);


	return $html;
}


function formattedInformation($uniqueID)
{
	$dataArray=getDetailedInfo($uniqueID);

	if(empty($dataArray))
	{
		return "<br><b>Section does not exist</b><br>";
	}
	else if($dataArray==-1)
	{
		return "<br><b>Internal database error. Please contact system administrator or go back and try again.</b><br>";
	}

	$html = "";

	$html.= "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
	$html.= "   <div class='flex paddingLeft20 '>";
	$html.= "       <div class='flexRightAlign paddingRight20 '>";
	$html.= "           <div style='padding-bottom:1px; padding-top:3px; '><p style='text-align:right;'>Term: </p></div>";
	$html.= "           <div style='padding-bottom:1px; padding-top:1px; '><p style='text-align:right;'>Professor: </p></div>";
	$html.= "           <div style='padding-bottom:1px; padding-top:1px; '><p style='text-align:right;'>Section Code: </p></div> ";
	$html.= "       </div>";
	$html.= "       <div class='flexLeftAlign paddingTop10 flexOne'>";
	$html.=             "<div class='marginBottom20'>". getTermList($dataArray[0]['term']) . '</div>';
	$html.=             getProfessorList($dataArray[0]['teacher']);
	$html.= "           <br><input class='inputprimary' placeholder='Catalog Code' value='{$dataArray[0]['code']}' name='sectionCode' maxlength='3' type='text'/>";
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

function getTermList($prev)
{
	$html = "";
	$data= databaseExecute("select * from terms");

	if(!is_array($data))
	{
		return "An error has occured getting the available terms.";
	}

	if(empty($data))
	{
		$html = "Unable to fetch terms. Contact system administrator.";
	}
	else
	{            
		//Create select statement
		$html="<select name='terms' class='inputSelectLarge'>";

		foreach($data as $row)
		{
			if ($row["code"]==$prev)
			{
				$html.= "<option value=\"" . $row["code"] . "\" selected>" . $row["code"]  . "</option>";
			}
			else
			{
				$html.= "<option value=\"" . $row["code"]  . "\">" . $row["code"]  . "</option>";
			}
		}

		$html .= "</select>";
	}
	return $html;
}

function getProfessorList($prev)
{
	$html = "";

	$temp= getUsersDepartment(array($_SESSION['username']));


	if(!is_array($temp))
	{
		return "An error has occured getting the available professors.";
	}


	$data = databaseQuery("select idno,realname from users where users.role>=cast('staff' as role) and deptid=?",array($temp[0]['deptid']));

	if(empty($data)|| !is_array($data))
	{
		$html = "Unable to fetch professors. Contact system administrator.";
	}
	else
	{            
		//Create select statement
		$html="<select name='professor' class='inputSelectLarge marginBottom20'>";

		foreach($data as $row)
		{   
			if($prev == $row['idno'])
			{
				$html.= "<option value=\"" . $row["idno"] . "\" selected>" . $row["realname"]  . "</option>";
			}
			else
			{
				$html.= "<option value=\"" . $row["idno"]  . "\">" . $row["realname"]  . "</option>";
			}
		}

		$html .= "</select>";
	}
	return $html;
}

function generateTable($dataset)
{

	$data= databaseExecute("select code from terms where activeterm=true");


	if(empty($dataset))
	{
		return "<h3>There are no sections to manage. Please add a section or edit another class.</h3>";
	}

	$html=<<<eof
	<div class="tableStyleA center flexGrow flexAlignSelf marginLeft20 marginBottom20" id="table">
	<h3>Sections available:</h3>
	<form class="" action="{$_SERVER['PHP_SELF']}" method="post">
	<table class= "dropShadow ">
	    <thead>
		<tr>
		    <th>Term</th>
		    <th>Section Code</th>
		    <th>Professor</th>
		    <th>Email</th>
		    <th>Edit</th>
		</tr>
	    </thead>
eof;
	foreach ($dataset as $row)
	{
		if($row['term']==$data[0]['code'])
		{
			$html.="<tr>";
			$html.="    <td><b>". $row['term'] ."</b></td>";
			$html.="    <td><b>". $row['code'] ."</b></td>";
			$html.="    <td><b>". $row['realname'] ."</b></td>";
			$html.="    <td><b>". $row['email'] ."</b></td>";
			$html.="    <td><button class=\"btnSmall\" name=\"selectedSection\" type=\"submit\" value=\"".$row["secid"]."\">Edit</button></td>";
			$html.="</tr>";
		}
		else
		{
			$html.="<tr>";
			$html.="    <td>". $row['term'] ."</td>";
			$html.="    <td>". $row['code'] ."</td>";
			$html.="    <td>". $row['realname'] ."</td>";
			$html.="    <td>". $row['email'] ."</td>";
			$html.="    <td><button class=\"btnSmall\" name=\"selectedSection\" type=\"submit\" value=\"".$row["secid"]."\">Edit</button></td>";
			$html.="</tr>";
		}
	}

	$html.="</table></form></div>";

	return $html;
}

function printSectionsAssociated($uniqueID)
{
	return generateTable(databaseQuery("select secid,sections.code,term,realname,email from sections,users where (sections.cid=? and users.idno=sections.teacher) order by term DESC,code",array($uniqueID)));
}


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

	$paginationValues=array_merge(array(getUsersDepartment(array($_SESSION['username']))[0]['deptid']),$paginationValues);
	
	$dataset=getDataSet($paginationValues);





	if(!is_array($dataset))
	{
		$dataset=array();
	}




	$html="";

	$html.=<<<eof
	<div class="tableStyleA  center" id="table">
	<form class="dropShadow" action="{$_SERVER['PHP_SELF']}" method="post">
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
			$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"".$row["cid"]."\">Edit Sections</button></td>";
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
				$sql="select classes.cid, classes.dept, classes.name, count(sections.secid) as sectioncount  FROM (classes LEFT JOIN sections ON classes.cid = sections.cid) where ". $row[1] ."=? and activeterm=true group BY classes.cid order by classes.dept OFFSET ? LIMIT ? ";
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
	<form class="" action="{$_SERVER['PHP_SELF']}" method="post">
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
			$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"".$row["cid"]."\">Edit Sections</button></td>";
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
	$sql="select sections.secid,sections.code,sections.cid,sections.term,sections.teacher from sections where secid=?";


	return databaseQuery($sql,array($uniqueID));
}

function getQuery($sql, $array)
{
	return databaseQuery($sql,$array);
}

function getDataSet($array)
{
	return databaseQuery("SELECT classes.cid, classes.dept, classes.name, count(sections.secid) as sectioncount FROM classes LEFT JOIN sections ON classes.cid = sections.cid where classes.dept=? group BY classes.cid order by classes.dept OFFSET ? LIMIT ?",$array);
}

function getSearchList($sql,$array)
{
	return databaseQuery($sql,$array);
}

function getNumUsers()
{
	return databaseExecute("select count(cid) as count from classes");
}

function databaseSubmitEdits($array)
{
	$sql="update sections set term=?, code=?, teacher=? where secid=?";
	$result=databaseQuery($sql,$array);

	return $result;
}

?>
