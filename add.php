<?php

	require_once("inc/config.php");
	require_login();

	if ( isset($_POST["cancel"])) {
		header("Location: index.php");
		exit();
	}

	if ( isset($_POST["add"]) ) {          	  
		// FORM VALIDATION
		foreach ($_POST as $key => $value) {  
			
			if ( is_array($value)) {
				for ($i=0; $i < count($value); $i++) {

					if ($value[$i] == "") {
						err_redir( $key . ": " . ERR_EMPTY_FIELDS, "add.php");
					}

					if ( strpos($key, "year") && (! is_numeric($value[$i])) ) {
						err_redir( $key . ": " . ERR_NUMERIC_ONLY, "add.php");
					}
				}
			} else {
				
				if ($value == "") {
					err_redir( $key . ": " . ERR_EMPTY_FIELDS, "add.php");
				}
			}
		}
	
		if (! is_bool($err = validate_position()) ) {
			err_redir($err, "add.php");
		}

		if (! strrpos($_POST["email"], "@") ) { 
			err_redir(ERR_EMAIL, "add.php");
		}
		// END FORM VALIDATION

		// Add base informaition (required)
		$stmt = $pdo->prepare('INSERT INTO Profile (user_id, 
												    first_name, 
												    last_name, 
												    email, 
												    headline, 
												    summary)

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

    	// Add the positions if entered
    	if (! empty($_POST['position']) && is_array($_POST['position'])) {

    		foreach ( $_POST['position'] as $pos => $rank) {

	    		$stmt = $pdo->prepare('INSERT INTO Position (profile_id, 
	    													 ranking, 
	    													 year, 
	    													 description) 
	    							   VALUES ( :pid, :rank, :year, :desc)');

				$stmt->execute(array(
				  ':pid' => $profile_id,
				  ':rank' => $rank,
				  ':year' => $_POST['pos_year'][$pos],
				  ':desc' => $_POST['pos_desc'][$pos])
				);
    		}
    	}

    	// Add Education if entered

    	// check if there are school(s) entered in the Add form and confirm it
    	// is passed as an array of one or more schools.
    	if (! empty($_POST['school']) && is_array($_POST['school'])) {

    		// loop through each of the schools in the array 
    		foreach ( $_POST['school'] as $edu => $rank) {

    			// store this entry's year and school in simple variables
    			$year = $_POST['edu_year'][$edu];
    			$school = $_POST['edu_school'][$edu];
    			$institution_id = false;				// Why is this necessary?

    			// does the institution exist? If not, add it
				$stmt = $pdo->prepare('SELECT institution_id 
									   FROM Institution 
									   WHERE name =:name');

    			$stmt->execute(array(':name' => $school));
    			$row = $stmt->fetch(PDO::FETCH_ASSOC);
    			$institution_id = $row ? $row["institution_id"] : false;

    			if (! $institution_id ) {
    				$stmt = $pdo->prepare('INSERT INTO Institution (name) 
    									   VALUES ( :name) ');

    				$stmt->execute(array(':name' => $school));
    				$institution_id = $pdo->lastInsertId();
    			}

    			if (! $institution_id ) {
    				$_SESSION["error"] = "Error adding or finding that school.";
    				header("Location: add.php");
    				exit();
    			}

	    		$stmt = $pdo->prepare('INSERT INTO Education (profile_id, 
	    													  ranking, year, 
	    													  institution_id) 
	    							   VALUES ( :pid, 
	    							   			:rank, 
	    							   			:year, 
	    							   			:institution_id)');

				$stmt->execute(array(
				  ':pid' => $profile_id,
				  ':rank' => $rank,
				  ':year' => $year,
				  ':institution_id' => $institution_id)
				);
    		}
    	}

		$_SESSION["success"] = "Record added. Profile ID: $profile_id";
    	header("Location: index.php");
    	exit();

	}

?>

<!-- ================================ HTML ================================ -->

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
			<!-- Basic information -->
			<label for="txt_fname">First Name</label>
			<input type="text" name="first_name" 
				   id="txt_fname" class="form-control">

			<label for="txt_lname">Last Name</label>
			<input type="text" name="last_name" 
				   id="lname" class="form-control"><br>
			
			<label for="txt_email">Email</label>
			<input type="text" name="email" 
				   id="txt_email" class="form-control"><br>

			<label for="txt_headline">Headline</label>
			<input type="text" name="headline" 
				   id="txt_head" class="form-control"><br>

			<label for="txt_fname">Summary</label>
			<textarea name="summary" 
					  id="txta_summary" 
					  rows="10" class="form-control"></textarea>

			<hr>

			<!-- Education Management -->
			<p>Education / School <input type="submit" 
										 id="add_education" 
										 name="add_sch" 
										 value="+"></p>

			<div id="education_fields">
			</div>
			<!-- End Education Management -->

			<!-- Position Management -->
			<p>Position <input type="submit" 
							   id="add_position" 
							   name="add_pos" 
							   value="+"></p>

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

				

				if ( num_positions >= 9 ) {
					alert("Maximum of nine position entries exceeded.");
					return;
				}

				var source_pos = $('#pos-template').html();

				$('#position_fields').append(source_pos.replace(/%COUNT%/g, num_positions));
				num_positions++;

			});

			// Addd educaiton / school to the page
			$('#add_education').click( function(event) {

				event.preventDefault();

				
				
				if ( num_education >= 9 ) {
					alert("Maximum of nine schools exceeded.");
					return;
				}

				var source_edu = $('#edu-template').html();
				
				$('#education_fields').append(source_edu.replace(/%COUNT%/g, num_education));
				console.log(num_education);
				console.log(source_edu.replace(/%COUNT%/g, num_education));

				// Typeahed code for the School field
				$('.school').autocomplete({ source: 'school.php'});
				num_education++;

			});
		});
	</script>
	<?php include("inc/footer.php");?>

	<!-- TEMPLATE USES %COUNT% FOR VARIABLE-->
	<!-- School / Education -->
	<script id="edu-template" type="text/html">
		<div id="school_%COUNT%"> 
			 <p>Year: <input type="text" name="edu_year[%COUNT%]" value="">
			 	<input type="button" name="rem_edu" value="-" 
			 		   onclick="$('#school_%COUNT%').remove(); num_education--; return false;"><br />
			 	<label for ="txt_sch_%COUNT%">School:</label>   
			 	<input type="text" size=80 name="edu_school[%COUNT%]" class="school" value="" id="txt_sch_%COUNT%">
			 	<input type="hidden" name="school[%COUNT%]" value="%COUNT%">
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
