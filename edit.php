<?php // edit.php 
	  // author: Matt O'Neill
	  // March, 2020

	require_once("inc/config.php");	

	require_login();

	// User has clicked Cancel on form. Back out o the index page
	if ( isset($_POST["cancel"])) {
		header("Location: index.php");
		exit;
	}

	// Check that the profile_id passed in or specified in the GET portion of
	// the URL is valid. If not, throw an error and redirect to the index page
	// with an error.
	if (! exists_in_db($pdo, "profile_id", "Profile", $_REQUEST["profile_id"])) {
		$_SESSION["error"] = ERR_NO_PROFILE;
		header("Location: index.php");		
		exit;


	// OK! We are actually on an edit page with a valid, logged in user. This is 
	// the GET arrival method, meaning they have come from the index page or
	// manually entered a (valid) profile id in profile_id = ?		
	} else {

		// Grab the row and fields for the profile to pre-populate the form. Also
		// Check to make sure the user has edit rights on this record.
		
		$profile_id = $_REQUEST['profile_id'];

        $stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :pid");
        $stmt->execute(array(":pid" => $profile_id));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // No row found or returned from the query. This means there's no profile
        // for this user.
        // WE SHOULD BE ABLE TO REMOVE THIS CODEBLOCK AS WE DO THIS CHECK UP ABOVE
        // =======================================================================
        /*if ( $row === false ) {
            $_SESSION['error'] = ERR_NO_PROFILE;
            header( 'Location: index.php' ) ;
            exit;
        }*/

        $fn = htmlentities($row['first_name']);
        $ln = htmlentities($row['last_name']);
        $em = htmlentities($row['email']);
        $he = htmlentities($row['headline']);
        $su = htmlentities($row['summary']);
        $ui = $row['user_id'];

        // Users can only edit information associated with their user_id. If
        // a user tries to get in to edit an unauthoirised record, they will 
        // be redirected to the READ ONLY view.php of the data.
        if ( $ui !== $_SESSION["user_id"]) {
        	$_SESSION['error'] = ERR_NO_PROFILE;
	    	header( 'Location: view.php?profile_id=' .  $profile_id ) ;
	    	exit;
        }

        // Additional SQL statment pulls the postions associated with the current profile
		// orders them in descending order by the ranking (ordinal); this could be changed to the
		// year etc. field trivially.
		$sql = "SELECT year, description, ranking  
				FROM Position 
				WHERE profile_id=? 
				ORDER BY ranking ASC";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(1, $profile_id);
		$stmt->execute();
		$position = $stmt->fetchAll(PDO::FETCH_ASSOC); // get all rows

        // SQL for Education
		$sql = "SELECT Institution.name, Education.ranking, Education.year 
				FROM Institution 
				JOIN Education ON Institution.institution_id = Education.institution_id
				WHERE Education.profile_id=? 
				ORDER BY ranking ASC";

		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(1, $profile_id);
		$stmt->execute();
		$education = $stmt->fetchAll(PDO::FETCH_ASSOC); // get all rows

	}

	if ( isset($_POST["save"]) ) {  // Coming from form

		foreach ($_POST as $form_value) {  // Check all fields for empty strings
		
			if ($form_value == "") {
				$_SESSION["error"] = ERR_EMPTY_FIELDS;
				header("Location: edit.php");
				exit;
			}
		}

		if (! is_bool($err = validate_position()) ) {
			err_redir($err, "add.php");
		}

		if (! strrpos($_POST["email"], "@") ) { // Check for @ in email
			$_SESSION["error"] = ERR_EMAIL;
			header("Location: edit.php");
			exit;
		} 

		// Execute the update query on the base fields
		$stmt = $pdo->prepare('UPDATE Profile 
							   SET  first_name = :fn, 
							   		last_name = :ln, 
							   		email = :em, 
							   		headline = :he, 
							   		summary = :su
        					   WHERE profile_id = :pid');
    	$stmt->execute(array(
	        ':fn' => $_POST['first_name'],
	        ':ln' => $_POST['last_name'],
	        ':em' => $_POST['email'],
	        ':he' => $_POST['headline'],
	        ':su' => $_POST['summary'],
    	    ':pid' => $_POST['profile_id'])
    	);


	    // Clear out the old position entries and re-add the new set of positions
	    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
	    $stmt->execute(array( ':pid' => $profile_id));

	    // Insert the position entries
	    insert_positions($pdo, $profile_id);

	   	// Clear out the old education entries and re-add the new set of educations
	    $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
	    $stmt->execute(array( ':pid' => $profile_id));

	    // Insert the position entries
	    insert_educations($pdo, $profile_id);
    	
    	$_SESSION["success"] = "Record updated";
    	header("Location: index.php");
    	exit;
	}
	
	if (! isset($_REQUEST["profile_id"])) { //  No profile ID is set on the URL (GET) (may fire on form subit)
	    $_SESSION["error"] = ERR_NO_PROFILE_ID;
	    header("Location: index.php");
	    exit;
	}
?>

<!-- ================================ HTML ================================ -->

<!DOCTYPE html>
<html lang='en'>
<head>
	<script type="text/javascript" src="inc/jsfunc.js"></script>
	<?php include("inc/header.php");?>
</head>
<body>
<div class="container" id="main-content">
	<h1> Editing Profile for <?= $fn . " " . $ln ?></h1>
	<!-- flash error -->
	<?php flash_msg(); ?>
	<form name="add_user" method="post" action="">
		<div class="form-group">
			<label for="txt_fname">First Name</label>
			<input type="text" name="first_name" id="txt_fname" class="form-control" value=<?= $fn ?>>

			<label for="txt_lname">Last Name</label>
			<input type="text" name="last_name" id="lname" class="form-control" value=<?= $ln ?>><br>
			
			<label for="txt_email">Email</label>
			<input type="text" name="email" id="txt_email" class="form-control" value=<?= $em ?>><br>

			<label for="txt_headline">Headline</label>
			<input type="text" name="headline" id="txt_head" class="form-control" value=<?= $he ?>> <br>
			
			<input type="hidden" name="profile_id" value=<?= $profile_id ?>>

			<label for="txt_fname">Summary</label>
			<textarea name="summary" id="txta_summary" rows="10" class="form-control"><?= $su ?></textarea><br>

			<!-- Position Management -->
			<p>Position <input type="submit" id="add_position" name="add_pos" value="+"></p>
			<div id="position_fields">
				<?php
					$max_pos = 0;

					if ( $position )  {

						for ($i=0; $i < sizeof($position); $i++) {
							
							$year = htmlentities($position[$i]["year"]);
							$desc = htmlentities($position[$i]["description"]);
							$rank = $position[$i]["ranking"];
							$max_pos = ($rank > $max_pos) ? $rank : $max_pos; // always get the highest rank found

							print '<div id="position' . $rank . '">';
							print '<p>Year: <input type="text" name="year_pos[' . $rank . ']" value="' . $year . '">'; 
							print '<input type="button" name="rem_pos" value="-" onclick="$(\'#position' . $rank . '\').remove(); return false;"></p>';
							print '<textarea name="pos_desc[' . $rank . ']" rows="8" cols="80">' . $desc . '</textarea>';
							print '</div>';

						}

					}

					print "<script>var max_positions = " . $max_pos . ";</script>"; // this is so we can start at the new max number
				?>
			</div>
			<!-- End Position Management -->

			<!-- Education Management -->
			<p>Education <input type="submit" id="add_education" name="add_edu" value="+"></p>
			<div id="education_fields">
				<?php
					$max_edu = 0;

					if ( $education )  {

						for ($i=0; $i < sizeof($education); $i++) {
							$year = htmlentities($education[$i]["year"]);
							$desc = htmlentities($education[$i]["name"]);
							$rank = $education[$i]["ranking"];
							$max_edu = ($rank > $max_edu) ? $rank : $max_edu; // always get the highest rank found

							print '<div id="education' . $rank . '">';
							print '<p>Year: <input type="text" name="year_edu[' . $rank . ']" value="' . $year . '">'; 
							print '<input type="button" name="rem_edu" value="-" onclick="$(\'#education' . $rank . '\').remove(); return false;"></p>';
							print '<input type="text" class="school" name="edu_desc[' . $rank . ']" size="80" value="' . $desc . '">';
							print '</div>';

						} 

					}

					print "<script>var max_educations = " . $max_edu . "; console.log('Edu: ' + max_educations);</script>";
				?>
			</div>
			<!-- End Education Management -->

			<!-- Submit & Cancel Form -->
			<input type="submit" name="save" value="Save" 
				   onclick='return validateAdd(["input", "textarea"]);' 
				   class="btn btn-primary">
			<input type="submit" name="cancel" value="Cancel" class="btn">
		</div>
	</form>

</div>
	<script>
		<!-- /* Dynamically add Position & Education via jquery */ -->

		var num_positions = max_positions; 		// this is coming from the javascript above, pulling in the 
									  			// max rank number from the existing records.

		var num_educations = max_educations; 	// this is coming from the javascript above, pulling in the 
									     		// max rank number from the existing records.

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
					 <p>Year: <input type="text" \
					 				 name="year_pos[' + num_positions + ']" \
					 				 value="" /> \
					 <input type="button" name="rem_pos" value="-" \
					 	onclick="$(\'#position' + num_positions + '\').remove(); num_positions--; return false;"></p> \
					 <textarea name="pos_desc[' + num_positions + ']" rows="8" cols="80"></textarea> \
					 <input type="hidden" name="position[' + num_positions + ']" value="' + num_positions + '"> \
					 </div>');				
			});

			$('#add_education').click( function(event) {
				event.preventDefault();
				if ( num_educations >= 9 ) {
					alert("Maximum of nine educations entries exceeded.");
					return;
				}

				num_educations++;

				window.console && console.log("Adding education " + num_educations);

				$('#education_fields').append(
					'<div id="education' + num_educations + '"> \
					 <p>Year: <input type="text" \
					 				 name="year_edu[' + num_educations + ']" \
					 				 value="" /> \
					 <input type="button" name="rem_edu" value="-" \
					 	onclick="$(\'#education' + num_educations + '\').remove(); num_educations--; return false;"></p> \
					 <input type="text" class="school" name="edu_desc[' + num_educations + ']" size="80"> \
					 <input type="hidden" name="education[' + num_educations + ']" value="' + num_educations + '"> \
					 </div>');				
			});

			$('.school').autocomplete({ source: 'school.php'});

		});
	</script>

	<?php include("inc/footer.php");?>
</body>

</html>
