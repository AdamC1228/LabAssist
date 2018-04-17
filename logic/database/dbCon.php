<?php

/*
 * Per-page DB connection
 */
$DBCON = null;

function connectDB() 
{
	global $DBCON;

	/*
	 * Don't reconnect to the database.
	 */
	 
	if($DBCON !== null) 
	{
		return $DBCON;
	}

	/*
	 * Connection string information for the database.
	 */
	 
	$host     = "localhost";
	$user     = "labassist";
	$password = "labassist";

	$dBase = "labassist";

	try 
	{
		/*
		 * This is how to connect to the database. 
		 *
		 * I ran into a character set issue, so I'm specifically telling 
		 * PDO that I want the character set to be utf8.
		 */

		// MySQL DB conn
		#$dbase_connection = new PDO("pgsql:dbname=$dBase" $user, $password);

		/*
		 * PostgreSQL DB conn
		 */
		$DBCON = new PDO("pgsql:dbname=$dBase", $user);

		/*
		 * PDO error mode is set to throw exceptions
		 */
		$DBCON->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		/*
		 * Let PDO try to use native prepared statements
		 */
		$DBCON->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		/*
		 * Set the default fetch mode to be associative
		 */
		$DBCON->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) 
	{
		echo $e->__toString();
	}

	return $DBCON;            
}

function databaseExecute($sql) 
{
	$result = "";

	try 
	{
		$dbCon = connectDB();

		$stmt   = $dbCon->query($sql);
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $d) 
	{
		$result = -1;
		var_dump($d);
	}

	return $result;
}

function databaseQuery($sql,$array) 
{
	$result = "";
	try 
	{
		$dbCon = connectDB();

		$stmt = $dbCon->prepare($sql);

		$stmt->execute($array);

		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $d) 
	{
		$result = -1;
		var_dump($d);
	}

	return $result;
}

function safeDBQuery($sql, $vars) 
{
	$result = databaseQuery($sql, $vars);

	if(!empty($result) && is_array($result)) 
	{
		return $result;
	} 
	else 
	{
		return -1;
	}
}
?>
