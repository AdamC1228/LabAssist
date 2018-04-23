<?php
require_once "logic/database/dbCon.php";


/*
 * Create the section-ading form and table.
 */
function createForm()
{
	$html=<<<HTML
	    <div class="flex ">
		<div>
		    <h3> Add Section:</h3>
		    <div class="group paddingRight20">
HTML;

	$html.=generateForm();
	$html.="		</div>";
	$html.="	</div>";
	$html.=generateTable(getCurrentClassSections());
	$html.="</div>";
	return $html;
}

/*
 * Create the section adding form.
 */
function generateForm()
{
	/*
	 * Set the current value.
	 */
	if(isset($_POST['sectionCode']) && !empty($_POST['sectionCode']))
	{
		$current=$_POST['sectionCode'];
	}
	else
	{
		$current="";
	}

	$html = "";

	$html.=<<<HTML
<form action='{$_SERVER['PHP_SELF']}' method='post'>
	<div class='flex paddingLeft20 '>
		<div class='flexRightAlign paddingRight20 '>
			<div style='padding-bottom:1px; padding-top:3px; text-align:right;'>
				<p>Term: </p>
			</div>
			<div style='margin:auto; padding-bottom:1px;text-align:right;'>
				<p>Class: </p>
			</div>
			<div style='margin:auto; padding-bottom:1px;text-align:right;'>
				<p>Professor: </p>
			</div>
			<div style='margin:auto; padding-bottom:1px;text-align:right;'>
				<p>Code: </p>
			</div>
		</div>
		<div class='flexLeftAlign paddingTop10 flexOne'>
HTML;
	$html.=             getTermList();
	$html.= "           <br/>";
	$html.=             getClassList();
	$html.= "           <br/>";
	$html.=             getProfList();
	$html.=<<<HTML
			<br/>
			<input class='inputprimary' maxlength=3 placeholder='Section Code' name='sectionCode' value='{$current}' type='text'/>
		</div>
	</div>
	<div class='flex centerFlex paddingBottom20 '>
		<button class="btn margin20top btnleft" name="cancelEdit" type="submit" value='cancelEdit'>Cancel</button>
		<button class="btn margin20top btnright" name="submitEdit" type="submit" value='submitEdit'>Add Class</button>
	</div>
</form>
HTML;

	return $html;
}

/*
 * Get the list of classes.
 */
function getClassList()
{
	$html = "";
	$currentDepartment= getUsersDepartment($_SESSION['username']);
	if($currentDepartment === -1)
	{
		return "<div style='margin:auto; padding-top:6px;padding-bottom:6px;'>Unable to find any departments</div>";
	}

	$sql = "SELECT classes.cid, classes.name FROM classes WHERE classes.dept=?";

	$result = databaseQuery($sql, array($currentDepartment));

	if(empty($result) || !is_array($result))
	{
		$html = "<div style='margin:auto; padding-top:6px;padding-bottom:6px;'>Unable to find any classes for this department.</div>";
	}
	else
	{    
		if(isset($_POST['classSelected']) && !empty($_POST['classSelected']))
		{
			$current=$_POST['classSelected'];
		}
		else
		{
			$current="";
			$_POST['classSelected']=$result[0]['cid'];
		}

		/*
		 * Create select box.
		 */
		$html="<select name='classSelected' onchange='this.form.submit()' class='inputSelectLarge marginBottom20'>";

		foreach($result as $row)
		{
			if ($current === $row["cid"])
			{
				$html.= "<option value=\"{$row["cid"]}\" selected>{$row["name"]}</option>";
			}
			else
			{
				$html.= "<option value=\"{$row["cid"]}\">{$row["name"]}</option>";
			}
		}

		$html .= "</select>";
	}
	return $html;
}

/*
 * Get the list of the professors.
 */
function getProfList()
{
	$html = "";

	/*
	 * Set the current value.
	 */
	if(isset($_POST['profSelected']) && !empty($_POST['profSelected']))
	{
		$current=$_POST['profSelected'];
	}
	else
	{
		$current="";
	}

	$currentDepartment= getUsersDepartment($_SESSION['username']);
	if($currentDepartment === -1)
	{
		return "<div style='margin:auto; padding-top:6px;padding-bottom:6px;'>Unable to find any departments</div>";
	}

	$sql = "SELECT users.idno, users.realname FROM users WHERE users.deptid=? AND users.role>='staff'::role AND users.role<'developer'::role";

	$result = databaseQuery($sql, array($currentDepartment));

	if(empty($result) || !is_array($result))
	{
		$html = "<div style='margin:auto; padding-top:6px;padding-bottom:6px;'>Unable to find any professors for this department.</div>";
	}
	else
	{            
		/*
		 * Create select box.
		 */
		$html="<select name='profSelected' class='inputSelectLarge marginBottom20'>";

		foreach($result as $row)
		{
			if ($current === $row["idno"])
			{
				$html.= "<option value=\"{$row['idno']}\" selected>{$row['realname']}</option>";
			}
			else
			{
				$html.= "<option value=\"{$row['idno']}\">{$row['realname']}</option>";
			}
		}

		$html .= "</select>";
	}
	return $html;
}

/*
 * Get the list of terms.
 */
function getTermList()
{
	/*
	 * Set the current value.
	 */
	if(isset($_POST['termSelected']) && !empty($_POST['termSelected']))
	{
		$current=$_POST['termSelected'];
	}
	else
	{
		$current="";
	}


	$sql="SELECT code FROM terms";
	$result = databaseExecute($sql);

	if(empty($result) || !is_array($result))
	{
		$html = "Unable to fetch terms. Contact system administrator.";
	}
	else
	{            
		/*
		 * Create select box.
		 */
		$html="<select name='termSelected' class='inputSelectLarge marginBottom20'>";

		foreach($result as $row)
		{
			if ($current === $row["code"])
			{
				$html.= "<option value=\"{$row['code']}\" selected>{$row['code']}</option>";
			}
			else
			{
				$html.= "<option value=\"{$row['code']}\">{$row['code']}</option>";
			}
		}

		$html .= "</select>";
	}

	return $html;
}

/*
 * Generate the class table.
 */
function generateTable($dataset)
{

	$data = databaseExecute("SELECT code FROM terms WHERE activeterm=true");

	if(empty($dataset))
	{
		return "<h3>All sections for current selected class:</h3>";
	}

	$html=<<<HTML
<div class="tableStyleA center flexGrow flexAlignSelf marginLeft20 marginBottom20" id="table">
	<h3>All sections for current selected class</h3>
	<form class="" action="managesections.php" method="post">
		<table class= "dropShadow ">
			<thead>
				<tr>
					<th>Term</th>
					<th>Section Code</th>
					<th>Professor</th>
					<th>Email</th>
				</tr>
			</thead>
HTML;

	foreach ($dataset as $row)
	{
		if($row['term'] === $data[0]['code'])
		{
			$html.="<tr>";
			$html.="    <td><b>{$row['term']}</b></td>";
			$html.="    <td><b>{$row['code']}</b></td>";
			$html.="    <td><b>{$row['realname']}</b></td>";
			$html.="    <td><b>{$row['email']}</b></td>";
			$html.="</tr>";
		}
		else
		{
			$html.="<tr>";
			$html.="    <td>{$row['term']}</td>";
			$html.="    <td>{$row['code']}</td>";
			$html.="    <td>{$row['realname']}</td>";
			$html.="    <td>{$row['email']}</td>";
			$html.="</tr>";
		}
	}

	$html.="</table></form></div>";

	return $html;
}

/*
 * Attempt to add a section.
 */
function attemptAddSection()
{
	if(!isset($_POST['termSelected']) || empty($_POST['termSelected']))
	{
		return -1;
	}

	if(!isset($_POST['classSelected']) || empty($_POST['classSelected']))
	{
		return -1;
	}

	if(!isset($_POST['profSelected']) || empty($_POST['profSelected']))
	{
		return -1;
	}

	if(!isset($_POST['sectionCode']) || empty($_POST['sectionCode']))
	{
		return -1;
	}

	return databaseSubmitAdd(array($_POST['termSelected'], $_POST['classSelected'], $_POST['profSelected'], $_POST['sectionCode']));
}

/*
 * Add a section to the database.
 */
function databaseSubmitAdd($array)
{
	return databaseQuery("INSERT INTO sections (term, cid, teacher, code) VALUES(?, ?, ?, ?)" ,$array);
}

/*
 * Get the current sections of a class.
 */
function getCurrentClassSections()
{
	if(isset($_POST['classSelected']) && !empty($_POST['classSelected']))
	{
		$query = <<<SQL
SELECT sections.secid, sections.code, sections.term, users.realname, users.email 
	FROM sections
	JOIN terms ON sections.term = terms.code
	JOIN users ON sections.teacher = users.idno
	WHERE sections.cid = ?
	ORDER BY sections.term, sections.code
SQL;
		return databaseQuery($query, array($_POST['classSelected']));
	}
	else
	{
		return null;
	}
}

?>
