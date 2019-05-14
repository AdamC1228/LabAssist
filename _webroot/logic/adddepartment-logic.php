<?php
require_once "logic/database/dbCon.php";

/*
 * Create the add department form.
 */
function createForm()
{
	$html=<<<HTML
	    <div class="flex ">
		<div>
		    <h3> Add Department:</h3>
		    <div class="group paddingRight20">
HTML;
	$html.=generateForm();
	$html.="          </div>
		</div>";

	//$html.=generateTable(getCurrentTermList());

	$html.="</div>";
	return $html;
}

/*
 * Generate the actual add department form.
 */
function generateForm()
{
	$deptID="";
	$deptName="";

	/*
	 * Set variables to previous values.
	 */
	if(isset($_POST['deptID']) && !empty($_POST['deptID']))
	{
		$deptID=$_POST['deptID'];
	}

	if(isset($_POST['deptName']) && !empty($_POST['deptName']))
	{
		$deptName=$_POST['deptName'];
	}


	$html = <<<HTML
<form action='{$_SERVER['PHP_SELF']}' method='post'>
	<div class='flex paddingLeft20 '>
		<div class='flexRightAlign paddingRight20 '>
		<div style='padding-bottom:1px; padding-top:3px; text-align:right;'>
			<p>Abreviation: </p>
		</div>
		<div style='padding-bottom:1px; padding-top:3px; text-align:right;'>
			<p>Full Name: </p>
		</div>
	</div>
	<div class='flexLeftAlign paddingTop10 flexOne'>
		<input class='inputprimary' maxlength=6 placeholder='Abreviation' name='deptID' value='{$deptID}' type='text'/>
		<br /><br />
		<input class='inputprimary' placeholder='Full Name' name='deptName' value='{$deptName}' type='text'/>
	</div>
	</div>
	<div class='flex centerFlex paddingBottom20 '>
		<button class="btn margin20top btnleft" name="cancelEdit" type="submit" value='cancelEdit'>Cancel</button>
		<button class="btn margin20top btnright" name="submitEdit" type="submit" value='submitEdit'>Add Department</button>
	</div>
</form>
HTML;

return $html;
}

/*
 * Get the list of current terms.
 */
function getCurrentTermList()
{
	$query = <<<SQL
SELECT terms.code, terms.activeterm FROM terms GROUP BY terms.code ORDER BY terms.code DESC LIMIT 10 OFFSET 0
SQL;
	return databaseExecute($query);
}

/*
 * Generate the table.
 */
function generateTable($dataset)
{

	if(empty($dataset)|| !is_array($dataset))
	{
		return "<h3>No terms have been added yet.</h3>";
	}

	$html=<<<HTML
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
HTML;

	foreach ($dataset as $row)
	{
		if($row['activeterm']==1)
		{
			$html.=<<<HTML
			<tr>
				<td>
					<b>{$row['code']}</b>
				</td>
				<td>
					<b>TRUE</b>
				</td>
			</tr>
HTML;
		}
		else
		{
			$html.=<<<HTML
			<tr>
				<td>
					<b>{$row['code']}</b>
				</td>
				<td>
					<b>FALSE</b>
				</td>
			</tr>
HTML;
		}
	}

	$html.="</table></form></div>";

	return $html;
}

/*
 * Attempt to add a department from POST values.
 */
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

	return databaseQuery("INSERT INTO departments(deptid, deptname) VALUES(?, ?)", array($_POST['deptID'], $_POST['deptName']));
}

?>
