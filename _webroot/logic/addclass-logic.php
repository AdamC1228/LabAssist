<?php
require_once "logic/database/dbCon.php";

/*
 * Create the add class page.
 */
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

/*
 * Generate the add class form.
 */
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

/*
 * Get the list of departments.
 */
function getDepartmentList()
{
	$html = "";
	$currentDepartment = getUsersDepartment($_SESSION['username']);
	if($currentDepartment === -1)
	{
		return "An error has occured getting the available departments";
	}

	/*
	 * FUTURE: :DepartmentListing
	 *
	 * The reason the for-each loop is used with this query that seems to 
	 * only return one row, is in the case where someone can be attached to 
	 * multiple departments.
	 */
	$sql="SELECT * FROM departments WHERE departments.deptid=?";
	$result = databaseQuery($sql, array($currentDepartment));

	if(empty($result))
	{
		$html = "Unable to fetch departments. Contact system administrator.";
	}
	else
	{            
		/*
		 * Create select statement
		 */
		$html="<select name='department' class='inputSelectLarge'>";

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

/*
 * Generate a table containing all of the class for the current department.
 */
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

/*
 * Attempt to add a class from POST data.
 */
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

	return databaseSubmitAdd(array($_POST['className'], $_POST['department']));
}

/*
 * Execute adding a class.
 *
 * The array should be the class name, followed by the department
 */
function databaseSubmitAdd($array)
{
	return databaseQuery("INSERT INTO classes (name, dept) VALUES(?, ?)", $array);
}

/*
 * Get all of the classes for the current department.
 */
function getCurrentDepartmentClasses()
{
	$result = getUsersDepartment($_SESSION['username']);

	return databaseQuery("SELECT classes.name, classes.dept FROM classes WHERE classes.dept=?", array($result));
}
?>
