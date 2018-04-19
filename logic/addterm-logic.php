<?php
require_once "logic/database/dbCon.php";

/*
 * Generate the add term form and term list.
 */
function createForm()
{
	$html=<<<HTML
	    <div class="flex ">
		<div>
		    <h3> Add Term:</h3>
		    <div class="group paddingRight20">
HTML;

	$html.=generateForm();
	$html.="		</div>";
	$html.="	</div>";
	$html.=generateTable(getCurrentTermList());
	$html.="</div>";

	return $html;
}

/*
 * Generate the add term form.
 */
function generateForm()
{
	/*
	 * Set the current term.
	 */
	$current="";
	if(isset($_POST['termCode']) && !empty($_POST['termCode']))
	{
		$current=$_POST['termCode'];
	}


	$html = "";

	$html.=<<<HTML

<form action='{$_SERVER['PHP_SELF']}' method='post'>
	<div class='flex paddingLeft20 '>
		<div class='flexRightAlign paddingRight20 '>
			<div style='padding-bottom:1px; padding-top:3px; text-align:right;'>
				<p>New Term: </p>
			</div>
		</div>
		<div class='flexLeftAlign paddingTop10 flexOne'>
			<input class='inputprimary' maxlength=6 placeholder='Section Code' name='termCode' value='{$current}' type='text'/>
		</div>
	</div>
	<div class='flex centerFlex paddingBottom20 '>
		<button class="btn margin20top btnleft" name="cancelEdit" type="submit" value='cancelEdit'>Cancel</button>
		<button class="btn margin20top btnright" name="submitEdit" type="submit" value='submitEdit'>Add Term</button>
	</div>
</form>
HTML;

	return $html;
}

/*
 * Get the list of terms.
 */
function getCurrentTermList()
{
	$query = <<<SQL
SELECT terms.code, terms.activeterm FROM terms GROUP BY code ORDER BY code DESC LIMIT 10 OFFSET 0
SQL;
	return databaseExecute($query);
}

/*
 * Generate the term table.
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
		if($row['activeterm']===1)
		{
			$html.="<tr>";
			$html.="    <td><b>{$row['code']}</b></td>";
			$html.="    <td><b>TRUE</b></td>";
			$html.="</tr>";
		}
		else
		{
			$html.="<tr>";
			$html.="    <td>{$row['code']}</td>";
			$html.="    <td>FALSE</td>";
			$html.="</tr>";
		}
	}

	$html.="</table></form></div>";

	return $html;
}

/*
 * Attempt to add a term.
 */
function attemptAddTerm()
{
	if(!isset($_POST['termCode']) || empty($_POST['termCode']))
	{
		return -1;
	}

	if(isset($_POST['termCode']))
	{
		if(!preg_match("/^[0-9]{6}$/", $_POST['termCode']))
		{
			return -2;
		}
	}

	$query =<<<SQL
INSERT INTO terms(code, activeterm) VALUES(?, CAST(false as boolean))
SQL;

	return databaseQuery($query, array($_POST['termCode']));
}

?>
