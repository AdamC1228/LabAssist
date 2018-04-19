<?php

require_once "logic/database/dbCon.php";

/*
 * Check if a variable is set to a valid value.
 */
function isVarSet($var) {
	return isset($var) && !empty($var);
}

/*
 * Check if the current page is the specified one.
 */
function pageMatches($pag) {
	return basename($_SERVER['PHP_SELF']) === $pag;
}

/*
 * Verify that a user is registered.
 * 
 * Logged in users or users currently registering are handled differently.
 */
function verifyUser()
{
	/*
	 * Users not registered should be forced to register.
	 */
	if(isset($_SESSION["register"]) && !empty($_SESSION["register"]))
	{
		if(!(basename($_SERVER['PHP_SELF'])==='user_registration.php'))
		{
			header("Location: user_registration.php");
			exit();
		}
	}
	/*
	 * Users not registered should be forced to register.
	 */
	else if(isset($_SESSION["registerSid"]) && !empty($_SESSION["registerSid"]))
	{
		if(!(basename($_SERVER['PHP_SELF'])==='user_registration.php'))
		{
			header("Location: user_registration.php");
			exit();
		}
	}
	/*
	 * We have to handle the login page a bit differently or else we get an infinite loop of redirects.
	 */
	else if((basename($_SERVER['PHP_SELF'])==='login.php'))
	{
		/*
		 * Logged in users are always sent to the portal.
		 */
		if (isset($_SESSION['username']) && !empty($_SESSION['username']))
		{
			header("Location: portal.php");
			exit();
		}
	}
	/*
	 * If not on a registration page.
	 */
	else if(!(basename($_SERVER['PHP_SELF'])==='user_registration.php'))
	{
		/*
		 * Force user to login if not already.
		 */
		if(!(isset($_SESSION["username"]) || empty($_SESSION["username"])))
		{
			header("Location: login.php");
			exit();
		}
	}
	/*
	 * If you slipped through the cracks then go the the login page you hacker.
	 */
	else
	{
		header("Location: login.php");
		exit();
	}

}

/*
 * Verify that the user has logged into kiosk mode.
 */
function verifyKiosk()
{
	/*
	 * Usermode login does not grant access to this mode.
	 */
	if((isset($_SESSION["username"]) && !empty($_SESSION["username"])))
	{
		header("Location: login.php");
		exit();
	}

	/*
	 * Send the user to registration if they are registering.
	 */
	if(isset($_SESSION["register"]) && !empty($_SESSION["register"]))
	{
		if(!(basename($_SERVER['PHP_SELF'])==='user_registration.php'))
		{
			header("Location: user_registration.php");
		}
	}

	/*
	 * Users not logged in should not be able to connect.
	 */
	if(!(isset($_SESSION["kiosk"]) && !empty($_SESSION["kiosk"])))
	{
		header("Location: login.php");
		exit();
	}

}

/*
 * Get the access level that a user has, based off of their username.
 */
function getUserLevelAccess($username)
{
	$result = databaseQuery("SELECT role FROM users WHERE username=?", array($username));

	if(!empty($result))
	{
		return $result[0]['role'];
	}
	else 
	{
		return -1;
	}
}

/*
 * Get the access level that a user has, based off of their id number.
 */
function getUserLevelAccessIdno($idno)
{
	$result = databaseQuery("SELECT role FROM users WHERE idno=?", array($idno));

	if(!empty($result))
	{
		return $result[0]['role'];
	}
	else 
	{
		return -1;
	}
}

/*
 * Verify that a user has permission to access a particular page, using their 
 * username.
 *
 * If they don't, redirect them to the 403 page.
 */
function verifyUserLevelAccess($username, $requestPage)
{
	$query = <<<'SQL'
SELECT COUNT(users.username) FROM users JOIN pageaccess ON users.role >= pageaccess.role WHERE users.username=? AND pageaccess.page=?
SQL;

	$result = databaseQuery($query, array($username, $requestPage));

	if(empty($result) || $result[0]['count'] === 0)
	{
		header("Location: 403.php?requestedPage=$requestPage");
		exit();
	}
	else if( $result[0]['count'] !== 1)
	{
		echo "Whoops Contact System Admin";
		exit();
	}
}

/*
 * Verify that a user has permission to access a particular page, using their 
 * id number.
 *
 * If they don't, redirect them to the 403 page.
 */
function verifyUserIdLevelAccess($sidno,$requestPage)
{
	$query = <<<'SQL'
SELECT COUNT(users.username) FROM users JOIN pageaccess ON users.role >= pageaccess.role WHERE users.idno=? AND pageaccess.page=?
SQL;
	$result = databaseQuery($query, array($sidno, $requestPage));


	if(empty($result) || $result[0]['count']===0)
	{
		header("Location: 403.php?requestedPage=$requestPage");
	}
	else if( $result[0]['count'] !== 1)
	{
		echo "Whoops Contact System Admin";
		exit();
	}
}

/*
 * Verify that a user has permission to access a particular page, using their 
 * username.
 *
 * If they don't, redirect them to the 'under construction' page.
 */
function construction($username, $requestPage)
{
	$query = <<<'SQL'
SELECT COUNT(users.username) FROM users JOIN pageaccess ON users.role >= pageaccess.role WHERE users.idno=? AND pageaccess.page=?
SQL;

	$result = databaseQuery($query, array($username, $requestPage));

	if(empty($result) || $result[0]['count']===0)
	{
		header("Location: construction.php?requestedPage=$requestPage");
		exit();
	}
	else if( $result[0]['count'] !== 1)
	{
		echo "Whoops Contact System Admin";
		exit();
	}
}

/*
 * Check if user1 has a greater access level than user2.
 */
function compareAccessLevelbyName($user1, $user2)
{
	$query = <<<'SQL'
SELCT COUNT(user1.role) AS result FROM users AS user1 JOIN users AS user2 ON user1.role > user2.role WHERE user1.username=? AND user2.username=?
SQL;
	$result=databaseQuery($query, array($user1, $user2));

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

/*
 * Print out a debug representation of a value.
 */
function myDebug($val)
{
	echo "\n<pre>";
	var_dump($val);
	echo "</pre>\n";
}

function arrayPrint($array)
{
    ob_start();
    var_dump($array);
    $result = ob_get_clean();
    
    $html = "<pre>";
    $html.=     $result;
    $html.= "</pre>";
    
    return $html;
}

/*
 * Generate a select box.
 *
 * - name is the name of the select box
 * - array provides the data for the select box
 * - matchPrev is the previous value for the box, pass "" for no previous value
 * - key is the key into array to use for the select box values
 * - value is the key into array to use for the select box labels
 * - nullVal is the value for the 'None' option of the select box
 */
function generateSelectBox($name, $array, $matchPrev, $key, $value, $nullVal) {
	/*
	 * Create select box
	 */
	$html = "<select name='{$name}' class='scheduleSelect' onchange='this.form.submit()'>";

	if($matchPrev === "") {
		$html .= "<option value=\"{$nullVal}\" selected>None</option>";
	} else {
		$html .= "<option value=\"{$nullVal}\">None</option>";
	}

	foreach($array as $row) {
		if ($matchPrev === $row[$key]) {
			$html.= "<option value=\"{$row[$key]}\" selected>{$row[$value]}</option>";
		} else {
			$html.= "<option value=\"{$row[$key]}\">{$row[$value]}</option>";
		}
	}

	$html .= "</select>";

	return $html;
}

/*
 * Convert a strptime array into a timestamp value.
 */
function timeArrToStamp($arr) {
	$year = $arr['tm_year'] + 1900;
	$yday = $arr['tm_yday'] + 1;

	$strang = "{$year}.{$yday} {$arr['tm_hour']}:{$arr['tm_min']}:{$arr['tm_sec']}";

	return strtotime($strang);
}

/*
 * Convert a weekday number into a name.
 */
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
