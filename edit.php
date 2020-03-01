<?php // edit.php 
	  // author: Matt O'Neill
	  // February, 2020
	require_once("inc/config.php");
	

	// Not logged in. Throw fatl NO ACCESS error and terminate. No real
	// reason not to redirect to the index page here or prompt for a
	// login.
	if (! isset($_SESSION["user_id"])) {
		die(ERR_NO_ACCESS);
	}

	// User has clicked Cancel on form. Back out o the index page
	if ( isset($_POST["cancel"])) {
		header("Location: index.php");
		exit;
	}

	// Check that the profile_id passed in or specified in the GET portion of
	// the URL is valid. If not, throw an error and redirect to the index page
	// with an error.
	if (! exists_in_db($pdo, "profile_id", "Profile", $_GET["profile_id"])) {
		$_SESSION["error"] = ERR_NO_PROFILE;
		header("Location: index.php");		
		exit;


	// OK! We are actually on an edit page with a valid, logged in user. This is 
	// the GET arrival method, meaning they have come from the index page or
	// manually entered a (valid) profile id in profile_id = ?		
	} else {

		// Grab the row and fields for the profile to pre-populate the form. Also
		// Check to make sure the user has edit rights on this record.
		$profile_id = $_GET['profile_id'];
        $stmt = $pdo->prepare("SELECT * FROM Profile where profile_id = :pid");
        $stmt->execute(array(":pid" => $_GET['profile_id']));
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
        	$_SESSION['error'] = 'You do no have permission to edit this record.';
	    	header( 'Location: view.php?profile_id=' .  $_GET['profile_id']) ;
	    	exit;
        }

        // Additional SQL statment pulls the postions associated with the current profile
		// orders them in descending order by the ranking (ordinal); this could be changed to the
		// year etc. field trivially.
		$sql = "SELECT * FROM position WHERE profile_id=? ORDER BY ranking ASC";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(1, $profile_id);
		$stmt->execute();
		$position = $stmt->fetch(PDO::FETCH_ASSOC); // <-- this is the first row
													//     of data returned from
													//     the query. Or it is 
													//     false if no positions
													//	   were found.
	}

	if ( isset($_POST["save"]) ) {  // Coming from form

		foreach ($_POST as $form_value) {  // Check all fields for empty strings
		
			if ($form_value == "") {
				$_SESSION["error"] = ERR_EMPTY_FIELDS;
				header("Location: edit.php");
				exit;
			}
		}

		if (! strrpos($_POST["email"], "@") ) { // Check for @ in email
			$_SESSION["error"] = ERR_EMAIL;
			header("Location: edit.php");
			exit;
		} 

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


	    // Clear out the old position entries
	    // and re-add the new set of positions
	    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
	    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

	    // Insert the position entries
	    $rank = 1;
	    for($i=1; $i<=9; $i++) {
	        if ( ! isset($_POST['year'][$i]) ) continue;
	        if ( ! isset($_POST['desc'][$i]) ) continue;

	        $year = $_POST['year'][$i];
	        $desc = $_POST['desc'][$i];

	        $stmt = $pdo->prepare('INSERT INTO Position (profile_id, ranking, year, description)
	        					   VALUES ( :pid, :rank, :year, :desc)');
	        $stmt->execute(array(
	            ':pid' => $_REQUEST['profile_id'],
	            ':rank' => $rank,
	            ':year' => $year,
	            ':desc' => $desc)
	        );
	        $rank++;
	    }
    	
    	$_SESSION["success"] = "Record updated";
    	header("Location: index.php");
    	exit;
	}
	
	if (! isset($_GET["profile_id"])) { //  No profile ID is set on the URL (GET) (may fire on form subit)
	    $_SESSION["error"] = ERR_NO_PROFILE_ID;
	    header("Location: index.php");
	    exit;
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
	<h1> Adding Profile for <?= $_SESSION["name"] ?></h1>
	<!-- flash error -->
	<?php include("inc/flash.php"); ?>
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
			
			<input type="hidden" name="profile_id" value=<?= $_GET["profile_id"] ?>>

			<label for="txt_fname">Summary</label>
			<textarea name="summary" id="txta_summary" rows="10" class="form-control"><?= $su ?></textarea><br>

			<!-- Position Management -->
			<p>Position <input type="submit" id="add_position" name="add_pos" value="+"></p>
			<div id="position_fields">
				<?php
					$max_pos = 0;

					if ( $position )  {

						do {
							$year = htmlentities($position["year"]);
							$desc = htmlentities($position["description"]);
							$rank = $position["ranking"];

							print '<div id="position' . $rank . '">';
							print "<h3>Position: $rank</h3>";
							print '<p>Year: <input type="text" name="year[' . $rank . ']" value="' . $year . '">'; 
							print '<input type="button" name="rem_pos" value="-" onclick="$(\'#position' . $rank . '\').remove(); return false;"></p>';
							print '<textarea name="desc[' . $rank . ']" rows="8" cols="80">' . $desc . '</textarea>';
							print '</div>';

						} while ( $position = $stmt->fetch(PDO::FETCH_ASSOC) );

						$max_pos = $rank; // this only works here because we've sorted in ascending order
										  // an improvement would be to read in each position rank and then
										  // grab the max value from that set.
					}

					print "<script>var max_postions = " . $max_pos . ";</script>";
				?>
			</div>
			<!-- End Position Management -->

			<!-- Submit & Cancel Form -->
			<input type="submit" name="save" value="Save" 
				   onclick='return validateAdd(["input", "textarea"]);' 
				   class="btn btn-primary">
			<input type="submit" name="cancel" value="Cancel" class="btn">
		</div>
	</form>

</div>
	<script>
		<!-- /* Dynamically add Position year and description via jquery */ -->
		num_positions = max_postions; // this is coming from the javascript above, pulling in the 
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
