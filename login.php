<?php
	require_once("inc/config.php");

	if ( isset($_POST['login'])) { // login value from form

		// Check to make sure the fields are filled in
		if ( $_POST['email'] == "" or $_POST['pass'] == "" ) {

			$_SESSION["error"] = ERR_EMPTY_FIELDS;
			header("Location: login.php");
			exit;
		
		} else if (! strrpos($_POST["email"], "@") ){
			$_SESSION["error"] = ERR_EMAIL;
			header("Location: login.php");
			exit;

		} 

		if ( check_password() ) {
			$row = get_user($pdo);
			$_SESSION['user_id'] = $row['user_id'];
			$_SESSION['name'] = htmlentities($row['name']);
			$_SESSION['success'] = "Welcome, ".$_SESSION["name"].".";
			header("Location: index.php");
			exit;
			
		} else {
			$_SESSION["error"] = "Invalid password.";
			header("Location: login.php");
			exit;
		}

	}

?>

<!DOCTYPE html>
<html lang='en'>
<head>
	<script type="text/javascript" src="inc/jsfunc.js"></script>
	<?php include("inc/header.php");?>
</head>
<body>

	<div class="container" id="main-content">
		<h1>Please Login</h1>
		<!-- flash error -->
		<?php flash_msg(); ?>
		
		<form method="POST">
			<label for="txt_email">User Name</label>
			<input type="text" name="email" id="txt_email"><br/>
			
			<label for="pwd_pass">Password</label>
			<input type="password" name="pass" id="pwd_pass"><br/>
			
			<input type="submit" value="Log In" name="login" 
				   onclick="return doValidate();">
			<a href="index.php">Cancel</a>
		</form>

	</div>

	<?php include("inc/footer.php");?>
</body>

</html>