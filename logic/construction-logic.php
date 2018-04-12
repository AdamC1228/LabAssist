<?php
require_once "logic/common/ldap.php";
require_once "logic/database/dbCon.php";


function getCustomErrorMessage()
{

	if(isSet($_SESSION['username']) && !empty($_SESSION['username']))
	{
		$message="Hey! You! Yes you ". getUserRealName(). ". I can't let you come in here without a hard hat on.";
	}
	else if (isSet($_SESSION['sidno']) && !empty($_SESSION['sidno']))
	{
		$message="Hey! You! Yes you ". getUserRealNameId(). ". I can't let you come in here without a hard hat on.";
	}
	else
	{
		$message="Hey! You! Yes you. I can't let you come in here without a hard hat on.";
	}

	$message .= "<br> Come back when this is safe or you find a hard hat.";

	$message .= getMeOutOfHere();

	if(isSet($_GET['requestedPage']) && !empty($_GET['requestedPage']))
	{
		$message .= "<br><br><br> Requested page: " . $_GET['requestedPage'] . "<br><br>";
	}

	return $message;
}

function getMeOutOfHere()
{
	$code=<<<eof

	    <br><br><br>
	    <form action = "logout.php" method = "post">
		Go back or click below to logout.
	    <br>
		<input type="submit" class="btn" id="logout" name="logout" value="Logout"/>
	    </form>

eof;

	return $code;
}

function getUserRealName()
{
	$status = false;

	$dbCon = connectDB();

	$sql="select realname from users where username=?";

	$stmt= $dbCon->prepare($sql);

	$stmt->execute(array($_SESSION['username']));

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$arr = explode(' ',trim($result[0]['realname']));


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
