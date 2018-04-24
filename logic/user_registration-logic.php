<?php
require_once "logic/common/ldap.php";
require_once "logic/database/dbCon.php";



/*
 * Restore form values.
 */
function restoreForm()
{
	if(isSet($_POST["fname"]) && $_POST["fname"]!=$_SESSION["userAttr"]['fullName'])
	{
		$_SESSION["userAttr"]['fullName'] = $_POST["fname"];
	}
	if(isSet($_POST["email"]) && $_POST["email"]!=$_SESSION["userAttr"]['mail'])
	{
		$_SESSION["userAttr"]['mail'] = $_POST["email"];
	}
}



/*
 * Generate registration forms.
 */
function generateRegistration()
{
	#User came from userLogin
	if(isSet($_SESSION["username"])&&!empty($_SESSION["username"]))
	{
		$username=$_SESSION["username"];
	}
	else
	{
		$username=$_SESSION["userAttr"]['uid'];
	}
	$fullName=$_SESSION["userAttr"]['fullName'];
	$mail=$_SESSION["userAttr"]['mail'];
	$sidno=$_SESSION["userAttr"]['sidno'];  

	$code = <<<eof

    <input maxlength="255" name="username" type="text" class="inputprimary " value="$username"  placeholder="Username" id="login-name" disabled >
    <br /><br />
    <input maxlength="255" name="fname" type="text" class="inputprimary " value="$fullName" placeholder="Full Name" id="full-name" >
    <br /><br />
    <input maxlength="255" name="sidno" type="text" class="inputprimary " value="$sidno" placeholder="Student/Faculty ID Number" id="sidno" disabled >
    <br /><br />
    <input maxlength="255" name="email" type="text" class="inputprimary " value="$mail" placeholder="E-mail Address" id="email" >
eof;

	return $code;

}

/*
 * Attempt to perform registration.
 */
function attemptRegistration()
{
	if (empty($_POST['fname']) || empty($_POST['email']))
	{
		return -1;
	}

	if(isSet($_POST['sidno']))
	{
		if(!preg_match("/^\d{9}$/",$_POST['sidno']))
		{
			return -2;
		}
	}

	if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)===false)
	{
		return -3;
	}

	$error = insertRegistration();

	return $error;
}

/*
 * Insert registration info.
 */
function insertRegistration()
{
	$status = false;

	if(isSet($_SESSION["username"])&&!empty($_SESSION["username"]))
	{
		$username=$_SESSION["username"];
	}
	else
	{
		$username=$_SESSION["userAttr"]['uid'];
	}

	try
	{
		$dbCon = connectDB();

		$sql="insert into users (idno,realname, email, role, username) values (?,?,?,?,?)";

		$stmt= $dbCon->prepare($sql);

		$result = $stmt->execute(array($_SESSION["userAttr"]['sidno'],$_POST['fname'],$_POST['email'],"student",$username));

		$status = 1;
	}
	catch(PDOException $d)
	{
		$status = -999;
	}


	return $status;
}

?>
