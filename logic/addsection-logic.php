<?php
require_once "logic/database/dbCon.php";

function createForm()
{
	$html=<<<eof
	    <div class="flex ">
		<div>
		    <h3> Add Section:</h3>
		    <div class="group paddingRight20">
eof;
	$html.=generateForm();
	$html.="          </div>
		</div>";
	$html.=generateTable(getCurrentClassSections());
	$html.="</div>";
	return $html;
}



function generateForm()
{
	if(isset($_POST['sectionCode']) && !empty($_POST['sectionCode']))
	{
		$current=$_POST['sectionCode'];
	}
	else
	{
		$current="";
	}

	$html = "";

	$html.= "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
	$html.= "   <div class='flex paddingLeft20 '>";
	$html.= "       <div class='flexRightAlign paddingRight20 '>";
	$html.= "           <div style='padding-bottom:1px; padding-top:3px; text-align:right;'><p>Term: </p></div>";
	$html.= "           <div style='margin:auto; padding-bottom:1px;text-align:right;'><p>Class: </p></div>";
	$html.= "           <div style='margin:auto; padding-bottom:1px;text-align:right;'><p>Professor: </p></div>";
	$html.= "           <div style='margin:auto; padding-bottom:1px;text-align:right;'><p>Code: </p></div>";
	$html.= "       </div>";
	$html.= "       <div class='flexLeftAlign paddingTop10 flexOne'>";
	$html.=             getTermList();
	$html.= "           <br>";
	$html.=             getClassList();
	$html.= "           <br>";
	$html.=             getProfList();
	$html.= "           <br><input class='inputprimary' maxlength=3 placeholder='Section Code' name='sectionCode' value='{$current}' type='text'/>";
	$html.= "       </div>";
	$html.= "   </div>";
	$html.= "   <div class='flex centerFlex paddingBottom20 '>";
	$html.= "       <button class=\"btn margin20top btnleft\" name=\"cancelEdit\" type=\"submit\" value='cancelEdit'>Cancel</button>";
	$html.= "       <button class=\"btn margin20top btnright\" name=\"submitEdit\" type=\"submit\" value='submitEdit'>Add Class</button>";
	$html.= "   </div>";
	$html.= "</form>";

	return $html;
}


function getClassList()
{



	$html = "";
	$currentDepartment= getUsersDepartment(array($_SESSION['username']));
	if(!is_array($currentDepartment))
	{
		return "<div style='margin:auto; padding-top:6px;padding-bottom:6px;'>Unable to find any departments</div>";
	}

	$sql="select cid,name from classes where dept=?";
	$result = databaseQuery($sql,array($currentDepartment[0]['deptid']));

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

		//Create select statement
		$html="<select name='classSelected' onchange='this.form.submit()' class='inputSelectLarge marginBottom20'>";

		foreach($result as $row)
		{
			if ($current == $row["cid"])
			{
				$html.= "<option value=\"" . $row["cid"] . "\" selected>" . $row["name"]  . "</option>";
			}
			else
			{
				$html.= "<option value=\"" . $row["cid"]  . "\">" . $row["name"]  . "</option>";
			}
		}

		$html .= "</select>";
	}
	return $html;
}

function getProfList()
{
	$html = "";

	if(isset($_POST['profSelected']) && !empty($_POST['profSelected']))
	{
		$current=$_POST['profSelected'];
	}
	else
	{
		$current="";
	}


	$currentDepartment= getUsersDepartment(array($_SESSION['username']));
	if(!is_array($currentDepartment))
	{
		return "<div style='margin:auto; padding-top:6px;padding-bottom:6px;'>Unable to find any departments</div>";
	}

	$sql="select idno,realname from users where deptid=? and users.role>='staff'::role and role<'developer'::role";

	$result = databaseQuery($sql,array($currentDepartment[0]['deptid']));

	if(empty($result) || !is_array($result))
	{
		$html = "<div style='margin:auto; padding-top:6px;padding-bottom:6px;'>Unable to find any professors for this department.</div>";
	}
	else
	{            
		//Create select statement
		$html="<select name='profSelected' class='inputSelectLarge marginBottom20'>";

		foreach($result as $row)
		{
			if ($current == $row["idno"])
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

function getTermList()
{

	if(isset($_POST['termSelected']) && !empty($_POST['termSelected']))
	{
		$current=$_POST['termSelected'];
	}
	else
	{
		$current="";
	}


	$sql="select code from terms";
	$result = databaseExecute($sql);

	if(empty($result) || !is_array($result))
	{
		$html = "Unable to fetch terms. Contact system administrator.";
	}
	else
	{            
		//Create select statement
		$html="<select name='termSelected' class='inputSelectLarge marginBottom20'>";

		foreach($result as $row)
		{
			if ($current == $row["code"])
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

function generateTable($dataset)
{

	$data= databaseExecute("select code from terms where activeterm=true");

	if(empty($dataset))
	{
		return "<h3>All sections for current selected class:</h3>";
	}

	$html=<<<eof
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
			$html.="</tr>";
		}
		else
		{
			$html.="<tr>";
			$html.="    <td>". $row['term'] ."</td>";
			$html.="    <td>". $row['code'] ."</td>";
			$html.="    <td>". $row['realname'] ."</td>";
			$html.="    <td>". $row['email'] ."</td>";
			$html.="</tr>";
		}
	}

	$html.="</table></form></div>";

	return $html;
}



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

	return databaseSubmitAdd(array($_POST['termSelected'],$_POST['classSelected'],$_POST['profSelected'],$_POST['sectionCode']));
}



function databaseSubmitAdd($array)
{

	return databaseQuery("insert into sections (term,cid,teacher,code) values(?,?,?,?)",$array);

}

function getCurrentClassSections()
{
	if(isset($_POST['classSelected']) && !empty($_POST['classSelected']))
	{
		return databaseQuery("select secid,sections.code,term,realname,email from sections,users,terms where sections.term=terms.code and (sections.cid=? and users.idno=sections.teacher) order by term DESC,code",array($_POST['classSelected']));
	}
	else
	{
		return null;
	}
}

?>
