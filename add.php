<?php

	require_once("inc/config.php");

	require_login();

	if ( isset($_POST["cancel"])) {
		header("Location: index.php");
		exit;
	}

	if ( isset($_POST["add"]) ) {          // Coming from form
	// FORM VALIDATION
		foreach ($_POST as $key => $value) {  // Check all fields for empty strings
			
			if ($value == "") {
				err_redir(ERR_EMPTY_FIELDS, "add.php");
			}

			if ( strpos($key, "year") && (! is_numeric($_POST[$key])) ) {
				err_redir(ERR_NUMERIC_ONLY, "add.php");
			}
		}

		if (! is_bool($err = validate_position()) ) {
			err_redir($err, "add.php");
		}

		if (! strrpos($_POST["email"], "@") ) { // Check for @ in email address
			err_redir(ERR_EMAIL, "add.php");
		}
	// FORM VALIDATED CONTINUE
		
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
	<?php flash_msg(); ?>
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
				/* Replace with the script/template model */
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
