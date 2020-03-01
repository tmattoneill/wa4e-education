<?php

	session_start();
	unset($_SESSION['email']);
	unset($_SESSION['user_id']);
	unset($_SESSION['name']);
	header('Location: index.php');

?>