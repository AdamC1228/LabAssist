<?php

require_once "logic/database/dbCon.php";

function verifyUser()
{
	//Users not registered should be forced to register
	if(isSet($_SESSION["register"]) && !empty($_SESSION["register"]))
	{
        if(!(basename($_SERVER['PHP_SELF'])=='user_registration.php'))
		{
			header("Location: user_registration.php");
			exit();
		}
	}
	//Users not registered should be forced to register
	else if(isSet($_SESSION["registerSid"]) && !empty($_SESSION["registerSid"]))
	{
		if(!(basename($_SERVER['PHP_SELF'])=='user_registration.php'))
		{
			header("Location: user_registration.php");
            exit();
		}
	}
	//We have to handle the login page a bit differently or else we get an infinite loop of redirects.
	else if((basename($_SERVER['PHP_SELF'])=='login.php'))
	{
		//Logged in users are always sent to the portal
		if (isSet($_SESSION['username']) && !empty($_SESSION['username']))
		{
            header("Location: portal.php");
            exit();
		}
	}
	//If not on a registration page
	else if(!(basename($_SERVER['PHP_SELF'])=='user_registration.php'))
	{
		//Force user to login if not already
		if(!(isSet($_SESSION["username"]) || empty($_SESSION["username"])))
		{
			header("Location: login.php");
			exit();
		}
	}
	//If you slipped through the cracks then go the the login page you hacker.
	else
	{
		header("Location: login.php");
		exit();
	}

}

function verifyKiosk()
{
	// Usermode login does not grant access to this mode
	if((isSet($_SESSION["username"]) && !empty($_SESSION["username"])))
	{
		header("Location: login.php");
		exit();
	}

	if(isSet($_SESSION["register"]) && !empty($_SESSION["register"]))
	{
		if(!(basename($_SERVER['PHP_SELF'])=='user_registration.php'))
		{
			header("Location: user_registration.php");
		}
	}

	//Users not logged in should not be able to connect
	if(!(isSet($_SESSION["kiosk"]) && !empty($_SESSION["kiosk"])))
	{
		#print_r ($_SESSION);
		header("Location: login.php");
		exit();
	}

}

function getUserLevelAccess($user)
{

	$result= databaseQuery("select role from users where username=?",array($user));

	if(!empty($result))
	{
		return $result[0]['role'];
	}
	else 
	{
		return -1;
	}
}

function getUserLevelAccessIdno($idno)
{

	$result= databaseQuery("select role from users where username=?",array($idno));

	if(!empty($result))
	{
		return $result[0]['role'];
	}
	else 
	{
		return -1;
	}
}

function verifyUserLevelAccess($username,$requestPage)
{

	$result=databaseQuery("select count(username) from users,pageaccess where users.username=? and pageaccess.page=? and users.role>=pageaccess.role",array($username,$requestPage));

	if(empty($result) || $result[0]['count']==0)
	{
		header("Location: 403.php?requestedPage=$requestPage");
		exit();
	}
	else if( $result[0]['count'] != 1)
	{
		echo "Whoops Contact System Admin";
		exit();
	}
}

function verifyUserIdLevelAccess($sidno,$requestPage)
{
	$result=databaseQuery("select count(username) from users,pageaccess where users.idno=? and pageaccess.page=? and users.role>=pageaccess.role",array($sidno,$requestPage));


	if(empty($result) || $result[0]['count']==0)
	{
		header("Location: 403.php?requestedPage=$requestPage");
	}
	else if( $result[0]['count'] != 1)
	{
		echo "Whoops Contact System Admin";
		exit();
	}
}

function construction($username,$requestPage)
{
	$result=databaseQuery("select count(username) from users,pageaccess where users.username=? and pageaccess.page=? and users.role>=pageaccess.role",array($username,$requestPage));

	if(empty($result) || $result[0]['count']==0)
	{
		header("Location: construction.php?requestedPage=$requestPage");
		exit();
	}
	else if( $result[0]['count'] != 1)
	{
		echo "Whoops Contact System Admin";
		exit();
	}
}

function compareAccessLevelbyName($user1,$user2)
{
	$result=databaseQuery("select count (user1.role) as result from users as user1, users as user2 where (user1.username=? and user2.username=?) and user1.role > user2.role",array($user1,$user2));

	if(empty($result) || !is_array($result))
	{
		return -1;
	}
	else
	{
		return $result[0]['result'];
	}
}

function isUserRoleGreaterThanOrEqualTo($array)
{
	$result=databaseQuery("select count (role) as result from users where idno=? and role>=?::role ",$array);

	if(empty($result) || !is_array($result))
	{
		return -1;
	}
	else
	{
        if($result[0]['result']==1)
        {
            return 1;
        }
        else
        {
            return 0;
        }
	}
}


function doesUserBelongToDept($user,$dept)
{
	$result=databaseQuery("select deptid from users where username=?",array($user));

	if(empty($result) || !is_array($result))
	{
		return -1;
	}
	else
	{
		if($result[0]['deptid']==$dept)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
}


function getUsersDepartment($user)
{
	$result = databaseQuery("select deptid from users where username=?",$user);

	if(!empty($result) && is_array($result))
	{
		return $result;
	}
	else
	{
		return -1;
	}
}

function myDebug($array)
{
	echo "\n<pre>";
	var_dump($array);
	echo "</pre>\n";
}



function generateSelectBox($name, $array, $matchPrev, $key, $value, $nullVal) {
	//Create select box
	$html = "<select name='{$name}' class='scheduleSelect' onchange='this.form.submit()'>";

	if($matchPrev === "") {
		$html .= "<option value=\"{$nullVal}\" selected>None</option>";
	} else {
		$html .= "<option value=\"{$nullVal}\">None</option>";
	}

	foreach($array as $row) {
		if ($matchPrev == $row[$key]) {
			$html.= "<option value=\"{$row[$key]}\" selected>{$row[$value]}</option>";
		} else {
			$html.= "<option value=\"{$row[$key]}\">{$row[$value]}</option>";
		}
	}

	$html .= "</select>";

	return $html;
}

function timeArrToStamp($arr) {
	$year = $arr['tm_year'] + 1900;
	$yday = $arr['tm_yday'] + 1;

	$strang = "{$year}.{$yday} {$arr['tm_hour']}:{$arr['tm_min']}:{$arr['tm_sec']}";

	myDebug($arr);

	return strtotime($strang);
}

function wknumtoname($num) {
	switch($num) {
	case 1:
		return 'Monday';
	case 2:
		return 'Tuesday';
	case 3:
		return 'Wednesday';
	case 4:
		return 'Thursday';
	case 5:
		return 'Friday';
	case 6:
		return 'Saturday';
	case 7:
		return 'Sunday';
	default:
		return 'Unknown weekday';
	}
}
?>
