<?php
	session_start();
	
	require_once("conn.php");
	require_once("func.php");

	// App Constants
	define ("ERR_EMAIL", "Email address must contain @");
	define ("ERR_BAD_PASS", "Invalid Password.");
	define ("ERR_EMPTY_FIELDS", "All values are required");
	define ("ERR_NO_ACCESS", "ACCESS DENIED");
	define ("ERR_DUPE_EMAIL", "A profile for that email address already exists.");
	define ("ERR_NO_PROFILE", "Could not load profile");
	define ("ERR_NO_PROFILE_ID", "Missing profile_id");
	define ("ERR_LOGIN_REQD", "You must be logged in to view that page.");
	define ("ERR_NUMERIC_ONLY", "This field must contain only numbers.");

	// vars needed throughout the app
	$salt = 'XyZzy12*_';
	$logged_in = isset($_SESSION["user_id"]);
	
	$summary_str = "At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, At accusam aliquyam diam diam dolore dolores duo eirmod eos erat, et nonumy sed tempor et et invidunt justo labore Stet clita ea et gubergren, kasd magna no rebum. sanctus sea sed takimata ut vero voluptua. est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat."
	
?>