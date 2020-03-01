<?php // Connection settings for the DB (mysql) connection
	  // Uses PDO for DB connection

	$user = "webapp";
	$dbpass = "Reggie12";
	$db = "misc";
	$host = "localhost";
	$port = "3306";

	$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db",$user, $dbpass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, 
					   PDO::ERRMODE_EXCEPTION);

?>