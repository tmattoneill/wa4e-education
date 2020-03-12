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


		// Output the positions / experience
		// Currently returns a single row; improvement could be to return the 
		// Full array.
		/* $sql = "SELECT * FROM position WHERE profile_id=? ORDER BY year DESC";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(1, $profile_id);
		$stmt->execute();
		$position = $stmt->fetch(PDO::FETCH_ASSOC); */

	} else {
		err_redir(ERR_NO_PROFILE, "index.php");
	}

function get_position($pdo, $profile_id) {

		$sql = "SELECT * FROM position WHERE profile_id=? ORDER BY year DESC";
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
		
		if ( $position = get_position($pdo, $profile_id) ) {
			print "<h3>Positions</h3>";
		}

		for ($i=0; $i < sizeof($position); $i++) {
			echo '<li>' . $position[$i]['year'] . ': ';
			echo $position[$i]['description'] . '</li>';
		}
		
		/*foreach ($position as $key => $value ) {
			print "<h3>Position</h3>\n<ul>";
			do {
				$year = htmlentities($position["year"]);
				$desc = htmlentities($position["description"]);
				print "<li>" . $year . ": " . $desc . "</li>";				
			} while ( $position );
		}

			print "</ul>"; */
	?>
	<p><a href="index.php">Done</a></p>

</div>

	<?php include("inc/footer.php");?>
</body>

</html>
