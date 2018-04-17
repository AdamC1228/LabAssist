<?php
require_once "logic/database/dbCon.php";

function createForm()
{
	$html=<<<HTML
<div class="flex ">
	<div>
		<h3> Add Class</h3>
			<div class="group paddingRight20">
HTML;

	$html.=generateForm();

	$html.=<<<HTML
			</div>
		</div>
HTML;

	$html.=generateTable(getCurrentDepartmentClasses());

	$html.="</div>";

	return $html;
}



function generateForm()
{
	$html = "";

	$html.= <<<HTML
<form action='addclass.php' method='post'>
	<div class='flex paddingLeft20 '>
		<div class='flexRightAlign paddingRight20 '>
			<div style='padding-bottom:1px; padding-top:3px;'>
				<p>Class Name: </p>
			</div>
			<div style='margin:auto;'>
				<p>Department: </p>
			</div>
		</div>
		<div class='flexLeftAlign paddingTop10 flexOne'>
			<input class='inputprimary marginBottom20' placeholder='Catalog Name' name='className' type='text'/>
			<br/>
HTML;

	$html.=getDepartmentList();

	$html.= <<<HTML
		</div>
	</div>
	<div class='flex centerFlex paddingBottom20'>
		<button class='btn margin20top btnleft' name='cancelEdit' type='submit' value='cancelEdit'>Cancel</button>
		<button class='btn margin20top btnright' name='submitEdit' type='submit' value='submitEdit'>Add Class</button>
	</div>
</form>
HTML;

	return $html;
}

function getDepartmentList()
{
	$html = "";
	$currentDepartment = getUsersDepartment($_SESSION['username']);
	if($currentDepartment === -1)
	{
		return "An error has occured getting the available departments";
	}

	$sql="select * from departments where deptid=?";
	$result = databaseQuery($sql, array($currentDepartment));

	if(empty($result))
	{
		$html = "Unable to fetch roles. Contact system administrator.";
	}
	else
	{            
		//Create select statement
		$html="<select name='department' class='inputSelectLarge'>";

		/*
		 * @CLEANUP
		 *
		 * Is this for-loop necessary?
		 */
		foreach($result as $row)
		{
			if ($currentDepartment === $row["deptid"])
			{
				$html.= "<option value='{$row["deptid"]}' selected> {$row["deptname"]} </option>";
			}
			else
			{
				$html.= "<option value='{$row["deptid"]}'> {$row["deptname"]} </option>";
			}
		}

		$html .= "</select>";
	}
	return $html;
}

function generateTable($dataset)
{
	if(empty($dataset))
	{
		return "<p>No data<p>";
	}

	$html=<<<HTML
<div class="tableStyleA center flexGrow flexAlignSelf marginLeft20 marginBottom20" id="table">
	<h3>All classes for current department</h3>
	<form class="" action="manageclasses.php" method="post">
		<table class= "dropShadow ">
			<thead>
				<tr>
					<th>Class</th>
					<th>Department</th>
				</tr>
			</thead>
HTML;

	foreach ($dataset as $row)
	{
		$html.=<<<HTML
			<tr>
			    <td>{$row['name']}</td>
			    <td>{$row['dept']}</td>
			</tr>
HTML;
	}

	$html.=<<<HTML
		</table>
	</form>
</div>
HTML;

	return $html;
}



function attemptAddClass()
{
	if(!isset($_POST['className']) || empty($_POST['className']))
	{
		return -1;
	}
	if(!isset($_POST['department']) || empty($_POST['department']))
	{
		return -1;
	}

	return databaseSubmitAdd(array($_POST['className'],$_POST['department']));
}



function databaseSubmitAdd($array)
{
	return databaseQuery("insert into classes (name,dept) values(?,?)",$array);

}

function getCurrentDepartmentClasses()
{
	$result=getUsersDepartment($_SESSION['username']);

	return databaseQuery("select name,dept from classes where dept=?",array($result));
}

// function createForm()
// {
//     $html = "";
//     $html.= "<h3>Add Class:</h3>";
//     $html.= "<div class='flex columnLayout2'>";
//     $html.= "   <div class='group'>";
//     $html.= generateForm();
//     $html.= "   </div>";
//     $html.= "   <div></div>";
//     $html.= "   <div></div>";
//     $html.= "</div>";
// 
// 
//     return $html;
// }
// function generateForm()
// {
//     $html = "";
//     
//     $html.= "<form action='adduser.php' method='post'>";
//     $html.= "   <div class='flex flexRow'>";
//     $html.= "       <div id='column1' class='flex flexRow marginLeft20'>";
//     $html.= "           <div class='flexRightAlign flexGrow paddingTop10'>";
//     $html.= "               <p>Class Name:</p>";
//     $html.= "           </div>";
//     $html.= "           <div class='flexLeftAlign flexGrow marginLeft20 paddingTop20'>";
//     $html.= "               <input class='inputprimary ' placeholder='Catalog Name' name='className' type='text'/>";
//     $html.= "           </div>";
//     $html.= "       </div>";
//     $html.= "       <div id='column2' class='flex flexRow marginLeft20'>";
//     $html.= "           <div class='flexRightAlign flexGrow paddingTop10'>";
//     $html.= "               <p>Department:</p>";
//     $html.= "           </div>";
//     $html.= "           <div class='flexLeftAlign flexGrow marginLeft20 paddingTop20'>";
//     $html.= getDepartmentList();
//     $html.= "           </div>";
//     $html.= "       </div>";
// //     $html.= "       <div id='column3' class='flex marginLeft20'>";
// //     $html.= "       </div>";
//     $html.= "   </div>";
//     $html.= "</form>";
//     
//     return $html;
// }
?>
