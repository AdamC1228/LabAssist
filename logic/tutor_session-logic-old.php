<?php
require_once "logic/common/ldap.php";
require_once "logic/database/dbCon.php";

//Master function to generate the form for the clockin clockout functionality
function generateClockinClockoutForm()
{
	$code = "";
	$result=isClockin();
	if($result == 1)
	{
		//Clockin Logic

		#User came from userLogin   
		$prevDept=NULL;
		$prevClass=NULL;
		$prevSection=NULL;

		if(isSet($_POST["subject"]))
		{
			$prevDept=$_POST["subject"];
		}

		if(isSet($_POST["class"]))
		{
			$prevClass=$_POST["class"];
		}

		if(isSet($_POST["section"]))
		{
			$prevSection=$_POST["section"];
		}

		$code = "<h1>Select Session</h1><br><br>";
		$code.= "<table class='center custtable'>";
		$code.= "       <tr><td>Department:</td><td>";
		$code.= genLab($prevDept);
		$code.= "</td>";
		$code.= "   <tr><td><br></td><td></td></tr>";
		$code.= "       <tr><td>Class:</td><td>";
		$code.= genClass($prevDept,$prevClass);
		$code.= "       </td>";
		$code.= "   </tr>";



		//Only show section if sections if not a tutor clockin!
		if(isset($_POST['showSection']) && $_POST['showSection']===true)
		{
			$code.= "   <tr><td><br></td><td></td></tr>";
			$code.= "<tr>";
			$code.= "<td>Section:</td><td>";
			$code.= genSection($prevDept,$prevClass,$prevSection);
			$code.= "</td></tr>";
		}

		$code.= "</table>";
	}
	else if ($result==0)
	{
		//If its not a clock in then its a clockout
		//Clockout Logic
		$code = genClockoutForm();
	}
	else
	{
		$error = "Internal database error. Contact System Administrator.asdf";
	}


	return $code;
}


//Check to see if the user is performing a clockin or a clock out action
function isClockin()
{    
	//Make database connection and attempt log the users clock in into the usage table.
	try
	{
		$dbCon = connectDB();

		$sql="select count(student) from usage where student = ? and markout is null";

		$stmt= $dbCon->prepare($sql);

		$stmt->execute(array($_SESSION['sidno']));

		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if($result[0]["count"]==0)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	catch (PDOException $d)
	{
		//Unknown error occured in the database. Return code.
		return -1;
	}
}


//Clockout form generation
function genClockoutForm()
{
	$html="";
	$count = 1;

	try
	{
		$dbCon = connectDB();

		$sql="select dept,name from classes,sections,usage where classes.cid = sections.cid and usage.secid = sections.secid and usage.student=? and usage.markout is null;";

		$stmt= $dbCon->prepare($sql);

		$stmt->execute(array($_SESSION['sidno']));

		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$html .= "<h1> Clock out</h1> <br> <span id=\"warning\"> Clock out of the following tutoring session(s)?</span><br><div class=\"sessionsText\">";

		foreach($result as $row)
		{
			$html .= "$count. " . $row["dept"] . ", " . $row["name"] . "<br>";
			$count++;
		}
		$html .= "</div>";

	}
	catch (PDOException $d)
	{
		$html = "";
	}


	return $html;
}




//We are passing $prev as a reference. If its the first time we are loading the page it will need
//to be set to the value of the first entry from the database. Since we query the database in the
//function to generate the list, we just dynamically set it within the function once the query has
//returned the list of department-id's.
function genLab(&$prev)
{
	//create array
	$result = array();

	$sql= "select * from departments order by deptname";
	$result=databaseExecute($sql);


	if(!is_array($result))
	{
		return "An error occured generating Departments. Please contact administrator.";
	}

	//Generate the html code for the department selection box

	$html = "<select name=\"subject\" onchange=\"this.form.submit()\" class=\"inputSelect\">";

	//If first time loading set the value of prev to be the first item in the drop down
	if(!isSet($_POST["subject"]))
	{
		$prev=$result[0]['deptid'];
	}

	foreach($result as $row)
	{
		#$row = ;
		if ($prev == $row["deptid"])
		{
			$html.= "<option value=\"" . $row["deptid"] . "\" selected>" . $row["deptname"]  . "</option>";
		}
		else
		{
			$html.= "<option value=\"" . $row["deptid"]  . "\">" . $row["deptname"]  . "</option>";
		}
	}
	$html.= "</select>";

	return$html;
}


function genClass($prevDept,&$prevClass)
{
	//Declare array
	$result=array();

	$html="";

	$sql= "select * from classes,sections,terms where terms.activeterm=true and dept=? and classes.cid=sections.cid and sections.term = terms.code;";

	$result=databaseQuery($sql,array($prevDept));


	if(!is_array($result))
	{
		return "An error occured generating classes. Please contact administrator.";
	}
	else
	{
		//Filter out anything not accessable b y students
		$curRole=getCurrentUserRole();

		if($curRole[0]["role"]=='student')
		{
			//Loof for anything that contains the word tutor, and mark the in a new array
			if (($key = preg_grep("/(tutor)/", array_map('strtolower',array_column($result,'name')))) !== false) 
			{

				//Remove anything matching the expression from the array 
				foreach($key as $row)
				{
					if(($newKey = array_search($row,array_map('strtolower',array_column($result,'name')))) !== false)
					{
						unset($result[$newKey]);
					}
				}
				//Re-index the array
				$result = array_values($result);
			}
		}
	}

	if(is_null($prevClass) || empty($prevClass))
	{
		if(isset($result[0]['cid']))
		{
			$prevClass=$result[0]['cid'];
		}
		else
		{
			return "<p>No available class</p>";
		}	
	}

	$sectionCode=databaseQuery("select code from sections where cid=?",array($prevClass));


	if($sectionCode[0]['code'] == "TUT")
	{
		$_POST['showSection']=false;
		$_POST['section']='TUT';
	}
	else
	{
		$_POST['showSection']=true;
	}


	//Generate the html code for the class selection box

	$html.= "<select name=\"class\" onchange=\"this.form.submit()\" class=\"inputSelect\">";

	foreach($result as $row)
	{
		#$row = ;
		if ($prevClass == $row["cid"])
		{
			$html.= "<option value=\"" . $row["cid"] . "\" selected>" . $row["name"]  . "</option>";
		}
		else
		{
			$html.= "<option value=\"" . $row["cid"]  . "\">" . $row["name"]  . "</option>";
		}
	}
	$html.= "</select>";


	//Send the "string" of html code back to the calling function
	return $html;
}


function genSection($prevDept,$prevClass,$prevSection)
{
	//create array
	$result = array();

	$sql= "select sections.secid,sections.code,users.realname from sections,classes,terms,users
                where classes.dept=?
                and classes.cid=?
                and sections.teacher=users.idno
                and sections.cid=classes.cid
                and sections.term=terms.code
                and terms.activeterm=true
                order by sections.code";
    $bindParams=array($prevDept,$prevClass);
	$result=databaseQuery($sql,$bindParams);
	
	myDebug($sql);
	myDebug($bindParams);


	if(!is_array($result))
	{
		return "An error occured generating Departments. Please contact administrator.";
	}

	//Generate the html code for the department selection box
	$html = "<select name=\"section\" onchange=\"this.form.submit()\" class=\"inputSelect\">";

	foreach($result as $row)
	{
		#$row = ;
		if ($prevSection == $row["secid"])
		{
			$html.= "<option value=\"" . $row["secid"] . "\" selected>" . $row["code"]  . " - " . $row["realname"] . "</option>";
		}
		else
		{
			$html.= "<option value=\"" . $row["secid"]  . "\">" . $row["code"]  . " - " . $row["realname"]  ."</option>";
		}
	}
	$html.= "</select>";

	return$html;
}



function attemptClockinClockout()
{
	if(isClockin())
	{
		return attemptClockin();
	}
	else
	{
		return attemptClockout();
	}
}

function generateButtons()
{
	if(isClockin())
	{
		$buttons=<<<eof
	<div class="floatCenter">
	    <input type="submit" class="btn marginright10" id="log-cancel" name="log-cancel" value="Cancel"/> 
	    <input type="submit" class="btn marginleft10" id="log-submit" name="log-submit" value="Clock-in"/>
	</div>    
eof;
	}
	else
	{
		$buttons=<<<eof
	<div class="floatCenter">
	    <input type="submit" class="btn marginright10" id="log-cancel" name="log-cancel" value="Cancel"/> 
	    <input type="submit" class="btn marginleft10" id="log-submit" name="log-submit" value="Clock-out"/>
	</div>    
eof;
	}

	return $buttons;
}


//Attempt to clock the uer in
function attemptClockin()
{
	//Default return code
	$status = -999;

	//Make sure that we have provided all the information. Otherwise return proper error code
	if(!isSet($_POST["subject"]))
	{
		return -1;
	}
	if(!isSet($_POST["class"]))
	{
		return -2;
	}

	if(!isSet($_POST["section"]))
	{
		return -3;
	}
	else
	{
		if($_POST['section']=='TUT')
		{
			$temp=(databaseQuery("select secid from sections where cid=?",array($_POST['class'])));
			$_POST['section']=$temp[0]['secid'];
		}
	}



	//Make database connection and attempt log the users clock in into the usage table.
	try
	{
		$dbCon = connectDB();

		$sql="insert into usage (student,secid,markin,markout) values (?,?,CURRENT_TIMESTAMP,null)";

		$stmt= $dbCon->prepare($sql);

		$result = $stmt->execute(array($_SESSION['sidno'],$_POST['section']));

		//If everything worked successfully return successful
		if($result = true)
		{
			$status=1;
			$_POST["status"]="clocked in";
		}
	}
	catch (PDOException $d)
	{
		//Unknown error occured in the database. Return code.
		$status=-999;
	}
	return $status;
}

//Attempt to clock the uer in
function attemptClockout()
{
	//Default return code
	$status = -999;

	//Make database connection and attempt log the users clock in into the usage table.
	try
	{
		$dbCon = connectDB();

		$sql="update usage set  markout= CURRENT_TIMESTAMP where student = ? and markout is null ";

		$stmt= $dbCon->prepare($sql);

		$result = $stmt->execute(array($_SESSION['sidno']));

		//If everything worked successfully return successful
		if($result = true)
		{
			$status=1;
			$_POST["status"]="clocked out";
		}
		else
		{
			$status=-10;
		}
	}
	catch (PDOException $d)
	{
		//Unknown error occured in the database. Return code.
		$status=-999;
	}
	return $status;
}


function getCurrentUserRole()
{
	return databaseQuery("select role from users where idno=?",array($_SESSION['sidno']));
}

?>
