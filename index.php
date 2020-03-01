<?php
	require_once("inc/config.php");
	
	$msg = $logged_in ? "<p><a href='logout.php'>Log Out</a></p>" : 
	                    "<p><a href='login.php'>Please log in</a></p>";
?>

<!DOCTYPE html>
<html lang='en'>
<head>
	<?php include("inc/header.php");?>
</head>
<body>
<div class="container" id="main-content">

	<h1>Welcome to the Profiles Database</h1>

	<?php 
		include("inc/flash.php");
		
		echo $msg;

		echo profile_table(get_all_profiles());

		if ($logged_in) echo "<p>Please <a href='add.php'>Add New Entry</a></p>";
	?>

</div>
	<?php include("inc/footer.php");?>
</body>

</html>
