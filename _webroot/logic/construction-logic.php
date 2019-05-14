<?php
require_once "logic/common/ldap.php";
require_once "logic/database/dbCon.php";

/*
 * Get the 'under-construction error message.
 */
function getCustomErrorMessage()
{

	if(isSet($_SESSION['username']) && !empty($_SESSION['username']))
	{
		$username = getUserRealName();

		$message="Hey! You! Yes you {$username}. I can't let you come in here without a hard hat on.";
	}
	else if (isSet($_SESSION['sidno']) && !empty($_SESSION['sidno']))
	{
		$username = getUserRealNameId();

		$message="Hey! You! Yes you {$username}. I can't let you come in here without a hard hat on.";
	}
	else
	{
		$message="Hey! You! Yes you. I can't let you come in here without a hard hat on.";
	}

	$message .= "<br> Come back when this is safe or you find a hard hat.";

	$message .= getMeOutOfHere();

	if(isSet($_GET['requestedPage']) && !empty($_GET['requestedPage']))
	{
		$message .= "<br><br><br> Requested page: {$_GET['requestedPage']}<br><br>";
	}

	return $message;
}

/*
 * Create button to get out.
 */
function getMeOutOfHere()
{
	$code=<<<HTML
<br /><br /><br />
<form action = "logout.php" method = "post">
	Go back or click below to logout.
	<br />
	<input type="submit" class="btn" id="logout" name="logout" value="Logout"/>
</form>
HTML;

	return $code;
}

/*
 * Get the first name of the currently logged in user.
 *
 * The username must be present in the session.
 */
function getUserRealName()
{
	$sql = "SELECT users.realname FROM users WHERE users.username=?";

	$result = databaseQuery($sql, array($_SESSION['username']));

	$arr = array();

	if(is_array($result) && !empty($result)) {
		$arr = explode(' ', trim($result[0]['realname']));
	} else {
		return "";
	}

	return $arr[0];
}

/*
 * Get the first name of the currently logged in user.
 *
 * The id number must be present in the session.
 */
function getUserRealNameId()
{
	$sql = "SELECT realname FROM users WHERE idno=?";

	$result = databaseQuery($sql, array($_SESSION['sidno']));

	$arr = array();

	if(is_array($result) && !empty($result)) {
		$arr = explode(' ', trim($result[0]['realname']));
	} else {
		return "";
	}

	return $arr[0];
}
?>
