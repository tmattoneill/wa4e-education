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

	<!-- User Form -->
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

			<!-- Education Management -->
			<p>Education / School <input type="submit" id="add_education" name="add_sch" value="+"></p>
			<div id="education_fields">
			</div>
			<!-- End Education Management -->

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
	<!-- End Form -->

</div>
	<!-- Dynamically add Position year and description via jquery -->
	<script>
		
		num_positions = 0;
		num_education = 0;

		$(document).ready(function(){

			// Add in a position to the page
			$('#add_position').click( function(event) {

				event.preventDefault();

				num_positions++;

				if ( num_positions >= 9 ) {
					alert("Maximum of nine position entries exceeded.");
					return;
				}

				var source_pos = $('#pos-template').html();

				$('#position_fields').append(source_pos.replace(/%COUNT%/g, num_positions));
		
			});

			// Addd educaiton / school to the page
			$('#add_education').click( function(event) {

				event.preventDefault();

				num_education++;				
				
				if ( num_education >= 9 ) {
					alert("Maximum of nine schools exceeded.");
					return;
				}

				var source_edu = $('#edu-template').html();
				
				$('#education_fields').append(source_edu.replace(/%COUNT%/g, num_education));

				// Typeahed code for the School field
				$('.school').autocomplete({ source: 'school.php'});

			});
		});
	</script>
	<?php include("inc/footer.php");?>

	<!-- TEMPLATE USES %COUNT% FOR VARIABLE-->

	<!-- School / Education -->
	<script id="edu-template" type="text/html">
		<div id="school_%COUNT%"> 
			 <p>Year: <input type="text" name="edu_year_%COUNT%" value="">
			 	<input type="button" name="rem_edu" value="-" 
			 		   onclick="$('#school_%COUNT%').remove(); num_education--; return false;"><br />
			 	<label for ="txt_sch_%COUNT%">School:</label>   
			 	<input type="text" size=80 name="school_%COUNT%" class="school" value="" id="txt_sch_%COUNT%">
			 </p>
		 </div>
	</script>

	<!-- Position -->
	<script id="pos-template" type="text/html">
			<div id="position_%COUNT%"> 
				<p>Year: <input type="text" name="pos_year[%COUNT%]" value="">
			 		<input type="button" name="rem_pos" value="-" 
			 		   onclick="$('#position_%COUNT%').remove(); num_positions--; return false;"><br />
			 	<label for="pos_desc_%COUNT%">Description:</label>   
			 	<textarea name="pos_desc[%COUNT%]" rows="8" cols="80" id="pos_desc_%COUNT%"></textarea>
			 	<input type="hidden" name="position[%COUNT%]" value="%COUNT%">
			 </p>
		 </div>
	</script>
</body>

</html>
