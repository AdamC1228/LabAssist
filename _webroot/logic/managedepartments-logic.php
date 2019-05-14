<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

/*
 * Display all the departments
 */
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

/*
 * Create the search bar.
 */
function createSearchBar()
{

	//Search options
	$options=array(array('Department ID','deptid'),array('Department Name','deptname'));
	$prevVal= $options[0][1];

	//Restore val if present, or use default
	if(isSet($_GET['searchSelect']) && !empty($_GET['searchSelect']))
	{
		$prevVal=$_GET['searchSelect'];
	}


	$html = "<form action='managedepartments.php' method='GET'><div class='flex rightAlignFlex padding20Bottom' >";

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

/*
 * Edit a department.
 */
function editEntry($uniqueID)
{
	return genEditForm($uniqueID);
}

/*
 * Generate the department editing form.
 */
function genEditForm($uniqueID)
{
	$html=<<<eof
	    <div class="flex ">
		<div>
		    <h3> Edit Department</h3>
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


/*
 * Display formatted information for a department.
 */
function formattedInformation($uniqueID)
{
	$dataArray=getDetailedInfo($uniqueID);

	if(empty($dataArray))
	{
		return "<br><b>Department does not exist</b><br>";
	}
	else if($dataArray==-1)
	{
		return "<br><b>Internal database error. Please contact system administrator or go back and try again.</b><br>";
	}

	$html = "";

	$html.= "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
	$html.= "   <div class='flex paddingLeft20 '>";
	$html.= "       <div class='flexRightAlign paddingRight20 '>";
	$html.= "           <div style='padding-bottom:1px; padding-top:3px;'><p>Department: </p></div>";
	$html.= "           <div style='margin:auto;'><p>Full Name: </p></div>";
	$html.= "       </div>";
	$html.= "       <div class='flexLeftAlign paddingTop10 flexOne'>";
	$html.= "           <input class='inputprimary marginBottom20' placeholder='Catalog Name' maxlength='6' value='{$dataArray[0]['deptid']}' name='deptid' type='text'/><br>";
	$html.= "           <input class='inputprimary marginBottom20' placeholder='Catalog Name' value='{$dataArray[0]['deptname']}' name='deptname' type='text'/><br>";
	$html.= "       </div>";
	$html.= "   </div>";
	$html.= "   <div class='flex centerFlex paddingBottom20 '>";
	$html.= "<button class=\"btn margin20top btnleft\" name=\"cancelEdit\" type=\"submit\" value='cancelEdit'>Cancel</button>";
	$html.= "<button class=\"btn margin20top btnright\" name=\"submitEdit\" type=\"submit\" value='$uniqueID'>Update</button>";
	$html.= "   </div>";
	$html.= "</form>";

	return $html;
}

/*
 * Get the list of departments.
 */
function getDepartmentList()
{
	$html = "";
	$currentDepartment= getUsersDepartment($_SESSION['username']);
	if($currentDepartment === -1)
	{
		return "An error has occured getting the available departments";
	}

	$sql="select * from departments where deptid=?";
	$result = databaseQuery($sql,array($currentDepartment));

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
 * Generate the table of departments.
 */
function generateTable($dataset)
{
	if(empty($dataset)|| !is_array($dataset))
	{
		return "<p>Database error<p>";
	}

	$html=<<<eof
	<div class="tableStyleA center flexGrow flexAlignSelf marginLeft20 marginBottom20" id="table">
	<h3>Current Department:</h3>
	<form class="" action="managedepartments.php" method="post">
	<table class= "dropShadow ">
	    <thead>
		<tr>
		    <th>Department</th>
		    <th>Full Name</th>
		</tr>
	    </thead>
eof;
	foreach ($dataset as $row)
	{
		$html.="<tr>";
		$html.="    <td>". $row['deptid'] ."</td>";
		$html.="    <td>". $row['deptname'] ."</td>";
		$html.="</tr>";
	}

	$html.="</table></form></div>";

	return $html;
}

/*
 * Get the sections associated with a department.
 */
function printSectionsAssociated($uniqueID)
{
	return generateTable(databaseQuery("select * from dept_stats where deptid=?",array($uniqueID)));
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
 * Generate a paginated table.
 */
function generatePaginatedTable()
{
	$paginationValues=getPaginated();

	$dataset=getDataSet($paginationValues);


	if(empty($dataset) || !is_array($dataset))
	{
		return "<p>Database error<p>";
	}


	$html="";

	$html.=<<<eof
	<div class="tableStyleA  center" id="table">
	<form class="dropShadow" action="managedepartments.php" method="post">
	<table>
	    <thead>
		<tr>
		    <th>Dept</th>
		    <th>Full Name </th>
		    <th>Professors</th>
		    <th>Classes</th>
		    <th>Action</th>
		</tr>
	    </thead>
eof;

	foreach ($dataset as $row)
	{
		$html.="<tr>";
		$html.="    <td>". $row['deptid'] ."</td>";
		$html.="    <td>". $row['deptname'] ."</td>";
		$html.="    <td>". $row['profcount'] ."</td>";
		$html.="    <td>". $row['classcount'] ."</td>";
		$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"".$row["deptid"]."\">Edit</button></td>";

		$html.="</tr>";
	}

	$html.="</table>";
	$html.="</form></div><div class='pagination centerFlex'>";
	$html.=printBottomPagination($paginationValues);
	$html.="</div>";

	return $html;
}

/*
 * Generate the SQL statement for a search.
 */
function generateSearchSql()
{
	$options=array(array('Department ID','deptid'),array('Department Name','deptname'));

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
				$sql="select * from dept_stats where ". $row[1] ." ilike ? order by deptid OFFSET ? LIMIT ? ";
			else
				$sql="select * from dept_stats where ". $row[1] ." ilike ? order by deptid OFFSET ? LIMIT ? ";

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

	var_dump($dataset);

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
	<div class="tableStyleA  center" id="table">
	<form class="dropShadow" action="managedepartments.php" method="post">
	<table>
	    <thead>
		<tr>
		    <th>Dept</th>
		    <th>Full Name </th>
		    <th>Professors</th>
		    <th>Classes</th>
		    <th>Action</th>
		</tr>
	    </thead>
eof;

	foreach ($dataset as $row)
	{
		$html.="<tr>";
		$html.="    <td>". $row['deptid'] ."</td>";
		$html.="    <td>". $row['deptname'] ."</td>";
		$html.="    <td>". $row['profcount'] ."</td>";
		$html.="    <td>". $row['classcount'] ."</td>";
		$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"".$row["deptid"]."\">Edit</button></td>";

		$html.="</tr>";
	}

	$html.="</table>";
	$html.="</form></div><div class='pagination centerFlex'>";
	$html.=printBottomPagination($paginationValues);
	$html.="</div>";

	return $html;
}

/*
 * Print the bottom things for pagination.
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
 * Get detailed info on a department.
 */
function getDetailedInfo($uniqueID)
{
	$sql="select * from dept_stats where deptid=?";


	return databaseQuery($sql,array($uniqueID));
}

/*
 * Get paginated info on all departments.
 */
function getDataSet($array)
{
	return databaseQuery("SELECT * from dept_stats OFFSET ? LIMIT ?",$array);
}

/*
 * Execute the SQL statement.
 */
function getSearchList($sql,$array)
{
	return databaseQuery($sql,$array);
}

/*
 * Get the number of departments.
 */
function getNumUsers()
{
	return databaseExecute("select count(deptid) as count from dept_stats");
}

/*
 * Update department info.
 */
function databaseSubmitEdits($array)
{
	$sql="update departments set deptid=?,deptname=? where deptid=?";

	$result=databaseQuery($sql,$array);

	return $result;
}

?>
