<?php
	if ( isset($_SESSION["error"]) ) {
		echo "<p class='alert alert-warning'>";
		echo $_SESSION["error"] . "</p>";
		unset($_SESSION["error"]);
	
	} else if ( isset($_SESSION["success"]) ) {
		echo "<p class='alert alert-success'>";
		echo $_SESSION["success"] . "</p>";
		unset($_SESSION["success"]);

	} 
?>	