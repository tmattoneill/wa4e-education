<?php

	require_once("inc/config.php");

	if (! isset($_SESSION["user_id"])) {  // Not logged in
		die(ERR_NO_ACCESS);
	}

	if ( isset($_POST["cancel"])) {
		header("Location: index.php");
		exit;
	}

	if ( isset($_POST["add"]) ) {          // Coming from form

		foreach ($_POST as $key => $value) {  // Check all fields for empty strings
			
			if ($value == "") {
				$_SESSION["error"] = ERR_EMPTY_FIELDS;
				header("Location: add.php");
				exit;
			}

			if ( strpos($key, "year") && (! is_numeric($_POST[$key])) ) {
				$_SESSION["error"] = "Year must contain only numbers";
				header("Location add.php");
				exit;
			}
		}

		if (! is_bool($err = validatePos()) ) {
			$_SESSION["error"] = $err;
			header("Location: add.php");
			exit;
		}

		if (! strrpos($_POST["email"], "@") ) { // Check for @ in email address
			$_SESSION["error"] = ERR_EMAIL;
			header("Location: add.php");
			exit;
		}

		$stmt = $pdo->prepare('INSERT INTO Profile (user_id, first_name, last_name, email, headline, summary)
        					   VALUES ( :uid, :fn, :ln, :em, :he, :su)');
    	$stmt->execute(array(
	        ':uid' => $_SESSION['user_id'],
	        ':fn' => $_POST['first_name'],
	        ':ln' => $_POST['last_name'],
	        ':em' => $_POST['email'],
	        ':he' => $_POST['headline'],
	        ':su' => $_POST['summary'])
    	);

    	$profile_id = $pdo->lastInsertId();

    	if (! empty($_POST['position']) && is_array($_POST['position'])) {

    		foreach ( $_POST['position'] as $pos => $rank) {

	    		$stmt = $pdo->prepare('INSERT INTO Position (profile_id, ranking, year, description) VALUES ( :pid, :rank, :year, :desc)');

				$stmt->execute(array(
				  ':pid' => $profile_id,
				  ':rank' => $rank,
				  ':year' => $_POST['year'][$pos],
				  ':desc' => $_POST['desc'][$pos])
				);
    		}
    	}

    	$_SESSION["success"] = "Record added. Profile ID: $profile_id";
    	header("Location: index.php");
    	exit;

	}

// PHP Helper Functions

	function validatePos() {
  		for($i=1; $i<=9; $i++) {
		    if ( ! isset($_POST['year'][$i]) ) continue;
		    if ( ! isset($_POST['desc'][$i]) ) continue;

		    $year = $_POST['year'][$i];
		    $desc = $_POST['desc'][$i];

		    if ( strlen($year) == 0 || strlen($desc) == 0 ) {
		      return "All fields are required";
	    }

	    if ( ! is_numeric($year) ) {
	      return "Position year must be numeric";
	    }
	  }
	  return true;
	}

	function alert_out($str) {
		print "<script>alert(\"" . var_dump($str). "\")</script>";
	}

?>

<!DOCTYPE html>
<html lang='en'>
<head>
	<script type="text/javascript" src="inc/jsfunc.js"></script>
	<?php include("inc/header.php"); ?>
</head>
<body>
<div class="container" id="main-content">
	<h1> Adding Profile for <?= $_SESSION["name"] ?></h1>
	<!-- flash error -->
	<?php include("inc/flash.php"); ?>
	<form name="add_user" method="post" action="add.php">
		<div class="form-group">
			<label for="txt_fname">First Name</label>
			<input type="text" name="first_name" id="txt_fname" class="form-control">

			<label for="txt_lname">Last Name</label>
			<input type="text" name="last_name" id="lname" class="form-control"><br>
			
			<label for="txt_email">Email</label>
			<input type="text" name="email" id="txt_email" class="form-control"><br>

			<label for="txt_headline">Headline</label>
			<input type="text" name="headline" id="txt_head" class="form-control"><br>

			<label for="txt_fname">Summary</label>
			<textarea name="summary" id="txta_summary" rows="10" class="form-control"></textarea><br>

			<!-- Position Management -->
			<p>Position <input type="submit" id="add_position" name="add_pos" value="+"></p>
			<div id="position_fields">
			</div>
			<!-- End Position Management -->

			<!-- Submit & Cancel Form -->
			<input type="submit" name="add" value="Add" 
				   onclick='return validateAdd(["input", "textarea"]);' 
				   class="btn btn-primary">
			<input type="submit" name="cancel" value="Cancel" class="btn">
		</div>
	</form>

</div>
	<script>
		<!-- Dynamically add Position year and description via jquery -->
		num_positions = 0;

		$(document).ready(function(){
			window.console && console.log("Document ready called");
			$('#add_position').click( function(event) {
				event.preventDefault();
				if ( num_positions >= 9 ) {
					alert("Maximum of nine position entries exceeded.");
					return;
				}

				num_positions++;

				window.console && console.log("Adding position " + num_positions);

				$('#position_fields').append(
					'<div id="position' + num_positions + '"> \
					<h3>Position: ' + num_positions + '</h3> \
					 <p>Year: <input type="text" \
					 				 name="year[' + num_positions + ']" \
					 				 value="" /> \
					 <input type="button" name="rem_pos" value="-" \
					 	onclick="$(\'#position' + num_positions + '\').remove(); num_positions--; return false;"></p> \
					 <textarea name="desc[' + num_positions + ']" rows="8" cols="80"></textarea> \
					 <input type="hidden" name="position[' + num_positions + ']" value="' + num_positions + '"> \
					 </div>');				
			});
		});
	</script>
	<?php include("inc/footer.php");?>
</body>

</html>
