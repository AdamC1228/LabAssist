<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

/*
 * Display all of the classes.
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

	/*
	 * Search options
	 */
	$options=array(
		array('Department','dept'),
		array('Class name','name')
	);

	/*
	 * Set default previous value.
	 */
	$prevVal= $options[0][1];

	/*
	 * Restore previous value if we had one.
	 */
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


	$html.= "         <div></div>";
	$html.= "           <div class='flexSearchRow'>";
	$html.= "               <div >Category: </div> ";
	$html.= "               <div> <select name='searchSelect' class='inputSelectSmall'>";

	foreach($options as $row)
	{
		if ($prevVal == $row[1])
		{
			$html.= "<option value=\"{$row[1]}\" selected>{$row[0]}</option>";
		}
		else
		{
			$html.= "<option value=\"{$row[1]}\">{$row[0]}</option>";
		}
	}

	$html.= <<<HTML
			</select>
		</div>
	</div>
	<div class='flexSearchRow'>
		<div >Search: </div>
		<div>
			<input class='inputprimary' placeholder='Use % for wildcard search' name='searchText' type='text'/>
		</div>
		</div>
			<div class= 'flexSearchRow'>
				<input class= 'btn btnleft' type='submit' name='searchSubmit' value='Search'>
				<input class= 'btn btnright' type='submit' name='searchReset' value='Reset'>
			</div>
		</div>
	</div>
</form>
HTML;

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
 * Edit a class.
 */
function editEntry($uniqueID)
{
	return genEditForm($uniqueID);
}

/*
 * Create the 'edit-class' form.
 */
function genEditForm($uniqueID)
{
	$html=<<<HTML
<div class="flex ">
	<div>
		<h3> Edit Class</h3>
		<div class="group paddingRight20">
HTML;
	$html.=formattedInformation($uniqueID);

	$html.="		</div>";
	$html.="	</div>";

	$html.=printSectionsAssociated($uniqueID);

	$html.="</div>";

	return $html;
}

/*
 * Display info for a class.
 */
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

	$deptList = getDepartmentList();

	$html = "";

	$html.=<<<HTML
<form action='{$_SERVER['PHP_SELF']}' method='post'>
	<div class='flex paddingLeft20 '>
		<div class='flexRightAlign paddingRight20 '>
			<div style='padding-bottom:1px; padding-top:3px;'><p>Class Name: </p></div>
			<div style='margin:auto;'><p>Department: </p></div>
		</div>
		<div class='flexLeftAlign paddingTop10 flexOne'>
			<input class='inputprimary marginBottom20' placeholder='Catalog Name' value='{$dataArray[0]['name']}' name='className' type='text'/>
			<br/>
			{$deptList}
		</div>
	</div>
	<div class='flex centerFlex paddingBottom20 '>
		<button class="btn margin20top btnleft" name="cancelEdit" type="submit" value='cancelEdit'>Cancel</button>
		<button class="btn margin20top btnright" name="submitEdit" type="submit" value='$uniqueID'>Update</button>
	</div>
</form>
HTML;

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

	/*
	 * :DepartmentListing
	 */
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
				$html.= "<option value=\"{$row['deptid']}\" selected>{$row['deptname']}</option>";
			}
			else
			{
				$html.= "<option value=\"{$row['deptid']}\">{$row['deptname']}</option>";
			}
		}

		$html .= "</select>";
	}

	return $html;
}

/*
 * Generate the table.
 */
function generateTable($dataset)
{
    if(empty($dataset))
    {
	return "<p>Database error<p>";
    }

    $html=<<<HTML
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
HTML;

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

/*
 * Get associated sections.
 */
function printSectionsAssociated($uniqueID)
{
	$query =<<<SQL
SELECT secid, term_sections.code, term, realname, email
	FROM term_sections
	JOIN terms ON term_sections.term = terms.code
	JOIN users ON users.idno = term_sections.teacher
	WHERE term_sections.cid = ?
SQL;

	return generateTable(databaseQuery($query,array($uniqueID)));
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
 * Get the paginated stuff.
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


	if(empty($dataset))
	{
		return "<p>Database error<p>";
	}


	$html="";

	$html.=<<<HTML
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
HTML;

	foreach ($dataset as $row)
	{
		$html.="<tr>";
		$html.="    <td>". $row['cid'] ."</td>";
		$html.="    <td>". $row['dept'] ."</td>";
		$html.="    <td>". $row['name'] ."</td>";
		$html.="    <td>". $row['sectioncount'] ."</td>";

		/*
		 * Prevent modification of other users if they have equal or greater power
		 */
		if(doesUserBelongToDept($_SESSION['username'],$row['dept'])==1)
		{
			$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"{$row["cid"]}\">Edit</button></td>";
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
 * Generate the search SQL statement.
 */
function generateSearchSql()
{
	$options=array(
		array('Department','dept'),
		array('Class name','name')
	);

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
		if ($search === $row[1])
		{
			if($search === 'role') {
				$query = <<<SQL
SELECT classes.cid, classes.dept, classes.name, COUNT(sections.secid) AS sectioncount
	FROM classes
	LEFT JOIN sections ON classes.cid = sections.cid
	WHERE {$row[1]} = ?
	GROUP BY classes.cid
	ORDER BY classes.dept
	OFFSET ? LIMIT ?
SQL;

				return $query;
			} else {
				$query = <<<SQL
SELECT classes.cid, classes.dept, classes.name, COUNT(sections.secid) AS sectioncount
	FROM classes
	LEFT JOIN sections ON classes.cid = sections.cid
	WHERE {$row[1]} ILIKE ?
	GROUP BY classes.cid
	ORDER BY classes.dept
	OFFSET ? LIMIT ?
SQL;

				return $query;
			}

			return $sql;
		}
	}

	return "Search error";

}

/*
 * Generate the search of the paginated table.
 */
function generatePaginatedTableSearch()
{

	/*
	 * Make sure that the search text is not null
	 */
	if(!isSet($_GET['searchText']) || empty($_GET['searchText']))
	{
		return "Must specify search parameter!";
	}

	$paginationValues=getPaginated();

	array_unshift($paginationValues,$_GET['searchText']);

	$dataset=databaseQuery(generateSearchSql(),$paginationValues);

	if(empty($dataset) || !is_array($dataset))
	{
		return "<p>No results found!<p>";
	}



	$html="";

	$html.=<<<HTML
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
HTML;

	foreach ($dataset as $row)
	{
		$html.="<tr>";
		$html.="<tr>";
		$html.="    <td>". $row['cid'] ."</td>";
		$html.="    <td>". $row['dept'] ."</td>";
		$html.="    <td>". $row['name'] ."</td>";
		$html.="    <td>". $row['sectioncount'] ."</td>";

		/*
		 * Prevent modification of other users if they have equal or greater power
		 */
		if(doesUserBelongToDept($_SESSION['username'],$row['dept'])==1)
		{
			$html.="    <td><button class=\"btnSmall\" name=\"edit\" type=\"submit\" value=\"{$row["cid"]}\">Edit</button></td>";
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
 * Print the bottom pagination values.
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
		if(empty($_SERVER['QUERY_STRING'])) {
			$pagLink .= "<li><a href='$baseurl?page={$i}'>{$i}</a></li>";
		} else {
			$pagLink .= "<li><a href='$baseurl"."page={$i}'>{$i}</a></li>";
		}
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
 * Get access levels.
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
		/*
		 * Get array remove preceding and postceeding {}
		 */
		$temp = substr($result[0]['enum_range'],1,-1);
		/*
		 * Convert to array of items
		 */
		$array = explode( ',', $temp );

		$array=filterRoleList($array);

		/*
		 * Create select statement
		 */
		$html="<select name='role' class='inputSelect'>";

		foreach($array as $row)
		{
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
 * Filter the role list.
 */
function filterRoleList($dataSet)
{
	$currentRole = getUserLevelAccess($_SESSION['username']);
	$newSet = array();

	foreach($dataSet as $value)
	{
		if($currentRole===$value)
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
 * Get detailed info for a class.
 */
function getDetailedInfo($uniqueID)
{
	$sql="select * from classes where cid=?";


	return databaseQuery($sql,array($uniqueID));
}

/*
 * Get the dataset.
 */
function getDataSet($array)
{
	return databaseQuery("SELECT classes.cid, classes.dept, classes.name, count(sections.secid) as sectioncount FROM classes LEFT JOIN sections ON classes.cid = sections.cid group BY classes.cid order by classes.dept OFFSET ? LIMIT ?",$array);
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
