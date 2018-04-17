<?php
require_once "logic/common/ldap.php";
require_once "logic/database/dbCon.php";

/*
 * Get a customized message for a 403 error.
 */
function getCustomErrorMessage()
{
	if(isSet($_SESSION['username']) && !empty($_SESSION['username']))
	{
		$message="I'm sorry ". getUserRealName(). ", but I can't let you do that.";
	}
	else if (isSet($_SESSION['sidno']) && !empty($_SESSION['sidno']))
	{
		$message="I'm sorry ". getUserRealNameId(). ", but I can't let you do that.";
	}
	else
	{
		$message="I'm sorry, but I can't let you do that.";
	}

	$message .= "<br> You appear to have insufficient privileges.";

	if(isSet($_GET['requestedPage']) && !empty($_GET['requestedPage']))
	{
		$message .= "<br><br><br><br> Requested page: {$_GET['requestedPage']}";
	}

	return $message;
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
