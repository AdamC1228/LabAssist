<?php
require_once "logic/common/ldap.php";
require_once "logic/database/dbCon.php";



/*
 *
 *
 *  High Level 
 *
 *
*/


function generateClockinClockoutForm()
{
    $html ="";
    $isClockinResult=isClockin();

    
    if($isClockinResult==1)
    {
        //We are clocking in
        $html.=genClockinForm();
    }
    else if ($isClockinResult==2)
    {
        //We are clocking out
        $html.=genClockoutForm();
    }
    else
    {
        //Its an error
    }
    
    return $html;
}


function attemptClockinClockout()
{
	if(isClockin()==1)
	{
		return attemptClockin();
	}
	else
	{
		return attemptClockout();
	}
}


/*
 *
 *
 *  Forms
 *
 *
*/


function genClockinForm()
{
    $html ="";
    
    //Clockin Logic

    #User came from userLogin   
    $dept=NULL;
    $class=NULL;
    $section=NULL;

    if(isSet($_POST["department"]))
    {
        $dept=$_POST["department"];
    }

    if(isSet($_POST["class"]))
    {
        $class=$_POST["class"];
    }

    if(isSet($_POST["section"]))
    {
        $section=$_POST["section"];
    }

    $html = "<h1>Select Session</h1><br><br>";
    $html.= "<table class='center custtable'>";
    $html.= "       <tr><td>Department:</td><td>";
    $html.= genLab($dept);
    $html.= "</td>";
    $html.= "   <tr><td><br></td><td></td></tr>";
    $html.= "       <tr><td>Class:</td><td>";
    $html.= genClass($dept,$class);
    $html.= "       </td>";
    $html.= "   </tr>";
    
    //Only show section if sections if not a tutor clockin!
    if(checkSection($class))
    {
        $html.= "   <tr><td><br></td><td></td></tr>";
        $html.= "<tr>";
        $html.= "<td>Section:</td><td>";
        $html.= genSection($dept,$class,$section);
        $html.= "</td></tr>";
    }

    $html.= "</table>";
    
    $html.=preserveFormVariables();
    
    return $html;
}


//Clockout form generation
function genClockoutForm()
{
	$html="";
	$count = 1;


    $result=databaseQuery("select dept,name from classes,sections,usage where classes.cid = sections.cid and usage.secid = sections.secid and usage.student=? and usage.markout is null;",array($_SESSION['sidno']));

    if(!is_array($result))
    {
        return "<h1> An unknown error occurred during clockout. Please contact the system administrator</h1>";
    }

    $html .= "<h1> Clock out</h1> <br> <span id=\"warning\"> Clock out of the following tutoring session(s)?</span><br><div class=\"sessionsText\">";

    foreach($result as $row)
    {
        $html .= "$count. " . $row["dept"] . ", " . $row["name"] . "<br>";
        $count++;
    }
    $html .= "</div>";



	return $html;
}



/*
 *
 *
 *  Clock-in Form Helpers 
 *
 *
*/

function genLab(&$dept)
{
	//create array
	$result = array();

	$sql= "select * from departments order by deptname";
	$result=databaseExecute($sql);


	if(!is_array($result))
	{
		return "Could not find any departments. Please contact administrator.";
	}
	
    //If first time loading set the value of prev to be the first item in the drop down
	if(!isSet($_POST["department"]))
	{
		$dept=$result[0]['deptid'];
	}

	//Generate the html code for the department selection box
	$html = "<select name=\"department\" onchange=\"this.form.submit()\" class=\"inputSelect\">";
	
	foreach($result as $row)
	{
		#$row = ;
		if ($dept == $row["deptid"])
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


function genClass($dept,&$class)
{
	//Declare array
	$result=array();

	$html="";

	$sql= "select distinct name,sections.cid from terms,sections left join classes on sections.cid=classes.cid where terms.activeterm=true and dept=? and sections.term = terms.code";

	$result=databaseQuery($sql,array($dept));


	if(!is_array($result))
	{
		return "Could not find any classes. Please contact administrator.";
	}
	else
	{
        filterClasses($result);
	}
    
    if(isset($_POST['prev-department']))
    {
        if($_POST['prev-department']!=$_POST['department'])
        {
            $class=NULL;
        }
	}
	
	if(is_null($class) || empty($class))
	{       
		if(isset($result[0]['cid']))
		{
			$class=$result[0]['cid'];
		}
		else
		{
			return "<p>No classes available</p>";
		}	
	}
	
	//Generate the html code for the class selection box
	$html.= "<select name=\"class\" onchange=\"this.form.submit()\" class=\"inputSelect\">";

	foreach($result as $row)
	{
		#$row = ;
		if ($class == $row["cid"])
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

function genSection($dept,$class,$section)
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
    $bindParams=array($dept,$class);
	$result=databaseQuery($sql,$bindParams);
	

	if(!is_array($result))
	{
		return "An error occured generating Departments. Please contact administrator.";
	}

	//Generate the html code for the department selection box
	$html = "<select name=\"section\" onchange=\"this.form.submit()\" class=\"inputSelect\">";

	foreach($result as $row)
	{
		#$row = ;
		if ($section == $row["secid"])
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

//Hidden fields contain previous post values to give us a pseudo state
function preserveFormVariables()
{
    $html="";
    $keys = array('department', 'class', 'section');
    foreach($keys as $name) 
    {
        if(isset($_POST[$name]))
        {
            $value = htmlspecialchars($_POST[$name]);
            $name = htmlspecialchars($name);
            $html.= '<input type="hidden" name="prev-'. $name .'" value="'. $value .'">';
        }
    }
    
    return $html;
}




/*
 *
 *
 *  Logic Functions 
 *
 *
*/

function checkSection($class)
{
	$sectionCode=databaseQuery("select code from sections where cid=?",array($class));
	if(is_array($sectionCode) && !empty($sectionCode))
	{

        if($sectionCode[0]['code'] == "TUT")
        {
            $_POST['section']='TUT';
            return false;
        }
        else
        {
            return true;
        }
	}
	else
	{
		return false;
	}
}

function filterClasses()
{
    //Filter out anything not accessable b y students
    $curRole=getUserLevelAccessIdno($_SESSION['sidno']);

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

function isClockin()
{
    $result = databaseQuery("select count(student) from usage where student = ? and markout is null",array($_SESSION['sidno']));
    
    if(is_array($result) && !empty($result))
    {
        //Is a clockout
		if($result[0]["count"]==0)
		{
			return 1;
		}
		//Not a clockout
		else
		{
			return 2;
		}
    }
    //Whoops
    else
    {
        return -1;
    }
}

//Attempt to clock the uer in
function attemptClockin()
{
	//Default return code
	$status = -999;

	//Make sure that we have provided all the information. Otherwise return proper error code
	if(!isSet($_POST["department"]))
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
        //If this is a tutor clock in then override the value of the section post.
		if($_POST['section']=='TUT')
		{
			$temp=(databaseQuery("select secid from sections where cid=?",array($_POST['class'])));
			$_POST['section']=$temp[0]['secid'];
		}
	}

    $result = databaseQuery("insert into usage (student,secid,markin,markout) values (?,?,CURRENT_TIMESTAMP,null)",array($_SESSION['sidno'],$_POST['section']));

    
    if(is_array($result))
    {
        $_POST["status"]="clocked in";
        return 1;
    }
    else if($result=-1)
    {
        return -1;
    }
    else
    {
        return -999;
    }

}

function attemptClockout()
{
    $result=databaseQuery("update usage set  markout= CURRENT_TIMESTAMP where student = ? and markout is null ",array($_SESSION['sidno']));

    if(is_array($result))
    {
        $_POST["status"]="clocked out";
        return 1;
    }
    else if($result=-1)
    {
        return -10;
    }
    else
    {
        return -999;
    }
}


/*
 *
 *
 *  MISC Functions 
 *
 *
*/


function generateButtons()
{
	if(isClockin()==1)
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


?>
