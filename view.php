<?php
	require_once("inc/config.php");

	if (! isset($_GET["profile_id"])) {
		err_redir(ERR_NO_PROFILE_ID, "index.php");
	} else if ( exists_in_db($pdo, "profile_id", "Profile", $_GET["profile_id"])) {
		$profile_id = $_GET["profile_id"];

		// Output the basic user info
		$sql = "SELECT first_name, last_name, email, headline, summary
				FROM profile
				WHERE profile_id=?"; 

		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(1, $profile_id);
		$stmt->execute();
		$profile = $stmt->fetch(PDO::FETCH_ASSOC);



	} else {
		err_redir(ERR_NO_PROFILE, "index.php");
	}


// Helper functions

function get_position($pdo, $profile_id) {

	$sql = "SELECT * FROM position WHERE profile_id=? ORDER BY year DESC";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(1, $profile_id);
	$stmt->execute();

	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_education($pdo, $profile_id) {
	$sql = "SELECT Institution.name, Education.year 
			from Profile 
				join Education on Profile.profile_id = Education.profile_id
				join Institution on Education.institution_id = Institution.institution_id 
			where Profile.profile_id=?";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(1, $profile_id);
	$stmt->execute();

	return $stmt->fetchAll(PDO::FETCH_ASSOC);	
}

?>

<!DOCTYPE html>
<html lang='en'>
<head>
	<?php include("inc/header.php");?>
</head>
<body>
<div class="container" id="main-content">

	<h1>Profile for: <?= $profile["first_name"] ?></h1>
	<?php
		foreach ($profile as $key => $value) {
			$title = str_replace("_", " ", $key);
			$title = ucwords($title);
			$value = htmlentities($value);

			print "<p>$title: $value</<p>";
		}

		print "<hr>";
		// These two sections can be cleaned up and and modelled into a general
		// case / function.
		if ( $position = get_position($pdo, $profile_id) ) {
			print "<h3>Positions</h3><ul>";
		}

		for ($i=0; $i < sizeof($position); $i++) {
			echo '<li>' . $position[$i]['year'] . ': ';
			echo $position[$i]['description'] . '</li>';
		}

		if ( $education = get_education($pdo, $profile_id) ) {
			print "</ul><h3>Education / Schools</h3><ul>";
		}

		for ($i=0; $i < sizeof($education); $i++) {
			echo '<li>' . $education[$i]['year'] . ': ';
			echo $education[$i]['name'] . '</li>';
		}
		print "</ul>";

		
	?>
	<p><a href="index.php">Done</a></p>

</div>

	<?php include("inc/footer.php");?>
</body>

</html>
