<?php
require_once "logic/database/dbCon.php";





function getUserInfo()
{
	$output = "";

	try
	{
		$dbCon = connectDB();

		$sql="select realname,email,encode(avatar,'base64')as avatar from users,user_avatars where username=? and users.idno=user_avatars.idno";

		$stmt= $dbCon->prepare($sql);

		$stmt->execute(array($_SESSION['username']));

		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);


		if(!empty($result))
		{
			$output = "<div class=\"userImgWrapper\">";
			$output.= "<img src=\"data:image/png;base64," . $result[0]["avatar"] . "\"/>";
			$output.= "</div>";
			$output.= "<div class=\"userDataWrapper\"><div class=\"userdata\"> <b> ";
			$output.= $result[0]["realname"] . "</b><br><b><span id=\"email\">" . $result[0]["email"] . "</span></b>";
			$output.= "</div></div>";
		}
		else
		{
			$dbCon = connectDB();

			$sql="select realname,email from users where username=?";

			$stmt= $dbCon->prepare($sql);

			$stmt->execute(array($_SESSION['username']));

			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$output = "<div class=\"userImgWrapper \">";
			$output.= "<img src=styles/img/icons/user.svg class= \"defaultFill\"/>";
			$output.= "</div>";
			$output.= "<div class=\"userDataWrapper\"><div class=\"userdata\"> <b> ";
			$output.= $result[0]["realname"] . "</b><br><b><span id=\"email\">" . $result[0]["email"];
			$output.= "</span></b></div></div>";

		}
	}
	catch(PDOException $d)
	{
		$output = "Error getting Info";
	}

	return $output;
}

?>
