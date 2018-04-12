<?php
require_once "logic/common/ldap.php";
require_once "logic/database/dbCon.php";


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
		$message .= "<br><br><br><br> Requested page: " . $_GET['requestedPage'];
	}

	return $message;
}

function getUserRealName()
{
	$status = false;

	$dbCon = connectDB();

	$sql="select realname from users where username=?";

	$stmt= $dbCon->prepare($sql);

	$stmt->execute(array($_SESSION['username']));

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if(is_array($result) && !empty($result))
        $arr = explode(' ',trim($result[0]['realname']));
    else
        return "";
        

	return $arr[0];
}

function getUserRealNameId()
{
	$status = false;

	$dbCon = connectDB();

	$sql="select realname from users where idno=?";

	$stmt= $dbCon->prepare($sql);

	$stmt->execute(array($_SESSION['sidno']));

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$arr = explode(' ',trim($result[0]['realname']));


	return $arr[0];
}

?>
