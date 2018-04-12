<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";



function displayAll()
{
	$html = "<script type='text/javascript' src='scripts/manageterms.js'></script>";
	$html.= "<div><h3>Manage Terms:</h3> </div>";
	$html.=     generatePaginatedTable();
	$html .="</div>";

	return $html;
}


/*
 *
 *
 *
 *   Table pagination Functionality
 *
 *
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

	$html.=<<<eof
	<div class="tableStyleA  center" id="table">
	<form class="dropShadow" action="{$_SERVER['PHP_SELF']}" method="post">
	<table>
	    <thead>
		<tr>
		    <th>Term</th>
		    <th># Classes in Term</th>
		    <th># Sections in Term</th>
		    <th>Active Term</th>
		    <th>Action</th>
		</tr>
	    </thead>
eof;

	foreach ($dataset as $row)
	{
		$html.="<tr>";
		if($row['activeterm']==1)
		{
			$html.="    <td><b>". $row['code'] ."</b></td>";
			$html.="    <td><b>". $row['classcount'] ."</b></td>";
			$html.="    <td><b>". $row['seccount'] ."</b></td>";
			$html.="    <td><b>TRUE</b></td>";
		}
		else
		{
			$html.="    <td>". $row['code'] ."</td>";
			$html.="    <td>". $row['classcount'] ."</td>";
			$html.="    <td>". $row['seccount'] ."</td>";
			$html.="    <td>FALSE</td>";
		}

		//Prevent modification of other users if they have equal or greater power
		if(!($row['activeterm']==1))
		{
			$html.="    <td><button class='btnSmall' name='setTerm' id='".$row["code"]."' onclick='return confirmTermEdit()' value='".$row["code"]."'>Make Active</button></td>";
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


function printBottomPagination($paginationValues)
{    
	$count = getPageCount();


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



function getDataSet($array)
{
	//return databaseQuery("select terms.code,count(sections.secid) as secCount, count(classes.cid) as classCount,terms.activeterm from terms left join sections on sections.term=terms.code left join classes on sections.cid=classes.cid group by terms.code order by CASE WHEN terms.activeterm = true THEN 1 ELSE 2 end asc OFFSET ? LIMIT ?",$array);

	return databaseQuery("select terms.code,count(sections.secid) as secCount, count(classes.cid) as classCount,terms.activeterm from terms left join sections on sections.term=terms.code left join classes on sections.cid=classes.cid group by terms.code order by terms.code desc OFFSET ? LIMIT ?",$array);
}

function getPageCount()
{
	return databaseExecute("select count(code) as count from terms");
}

function attemptTermChange($array)
{
	$result=databaseQuery("update terms set activeterm= case code when ? then cast(true as boolean) else cast(false as boolean) end;",$array);

	if(!(is_array($result)) || empty($result))
	{
		return -1;
	}
	else
	{
		myDebug($result);
		return 0;
	}
}

?>
