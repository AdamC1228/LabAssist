<?php
require_once "logic/database/dbCon.php";

function createForm()
{
	$html=<<<eof
	    <div class="flex ">
		<div>
		    <h3> Add Department:</h3>
		    <div class="group paddingRight20">
eof;
	$html.=generateForm();
	$html.="          </div>
		</div>";
	//$html.=generateTable(getCurrentTermList());
	$html.="</div>";
	return $html;
}



function generateForm()
{
	$deptID="";
	$deptName="";
	if(isset($_POST['deptID']) && !empty($_POST['deptID']))
	{
		$deptID=$_POST['deptID'];
	}
	if(isset($_POST['deptName']) && !empty($_POST['deptName']))
	{
		$deptName=$_POST['deptName'];
	}


	$html = "";

	$html.= "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
	$html.= "   <div class='flex paddingLeft20 '>";
	$html.= "       <div class='flexRightAlign paddingRight20 '>";
	$html.= "           <div style='padding-bottom:1px; padding-top:3px; text-align:right;'><p>Abreviation: </p></div>";
	$html.= "           <div style='padding-bottom:1px; padding-top:3px; text-align:right;'><p>Full Name: </p></div>";
	$html.= "       </div>";
	$html.= "       <div class='flexLeftAlign paddingTop10 flexOne'>";
	$html.= "            <input class='inputprimary' maxlength=6 placeholder='Abreviation' name='deptID' value='$deptID' type='text'/><br><br>";
	$html.= "            <input class='inputprimary' placeholder='Full Name' name='deptName' value='$deptName' type='text'/>";
	$html.= "       </div>";
	$html.= "   </div>";
	$html.= "   <div class='flex centerFlex paddingBottom20 '>";
	$html.= "       <button class=\"btn margin20top btnleft\" name=\"cancelEdit\" type=\"submit\" value='cancelEdit'>Cancel</button>";
	$html.= "       <button class=\"btn margin20top btnright\" name=\"submitEdit\" type=\"submit\" value='submitEdit'>Add Department</button>";
	$html.= "   </div>";
	$html.= "</form>";

	return $html;
}




function getCurrentTermList()
{
	return (databaseExecute("select code,activeterm from terms group by code order by code desc limit 10 offset 0"));
}


function generateTable($dataset)
{

	if(empty($dataset)|| !is_array($dataset))
	{
		return "<h3>No terms have been added yet.</h3>";
	}

	$html=<<<eof
	<div class="tableStyleA center flexGrow flexAlignSelf marginLeft20 marginBottom20" id="table">
	<h3>Ten most recent terms:</h3>
	<form class="" action="managesections.php" method="post">
	<table class= "dropShadow ">
	    <thead>
		<tr>
		    <th>Term</th>
		    <th>Active</th>
		</tr>
	    </thead>
eof;
	foreach ($dataset as $row)
	{
		if($row['activeterm']==1)
		{
			$html.="<tr>";
			$html.="    <td><b>". $row['code'] ."</b></td>";
			$html.="    <td><b>TRUE</b></td>";
			$html.="</tr>";
		}
		else
		{
			$html.="<tr>";
			$html.="    <td>". $row['code'] ."</td>";
			$html.="    <td>FALSE</td>";
			$html.="</tr>";
		}
	}

	$html.="</table></form></div>";

	return $html;
}



function attemptAddDepartment()
{
	if(!isset($_POST['deptID']) || empty($_POST['deptID']))
	{
		return -1;
	}
	if(!isset($_POST['deptName']) || empty($_POST['deptName']))
	{
		return -1;
	}


	return databaseQuery("insert into departments (deptid,deptname) values(?,?)",array($_POST['deptID'],$_POST['deptName']));
}

?>
