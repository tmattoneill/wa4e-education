<?php // delete.php
	  // author: Matt O'Neill
	  // March 2020

	require_once("inc/config.php");

	require_login();

    if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
	    $sql = "DELETE FROM Profile WHERE profile_id = :pid";
	    $stmt = $pdo->prepare($sql);
	    $stmt->execute(array(':pid' => $_POST['profile_id']));
	    $_SESSION['success'] = 'Record deleted';
	    header( 'Location: index.php' ) ;
	    exit;
	}

	// Guardian: Make sure that user_id is present
	if ( ! isset($_GET['profile_id']) ) {
	  $_SESSION['error'] = "Missing an Profile ID";
	  header('Location: index.php');
	  exit;
	}

	$stmt = $pdo->prepare("SELECT headline, profile_id FROM Profile where profile_id = :pid");
	$stmt->execute(array(":pid" => $_GET['profile_id']));

	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ( $row === false ) {
	    $_SESSION['error'] = 'Bad value for Profile ID: ' . $_GET['profile_id'];
	    header( 'Location: index.php' ) ;
	    exit;
	}

?>

<!DOCTYPE html>
<html lang='en'>
<head>
	<?php include("inc/header.php");?>
</head>
<body>
<div class="container" id="main-content">

	    <h1>Delete Record</h1>
        <p>Are you sure you wish to delete <?= htmlentities($row["headline"]); ?>?
        <form method="post">
            <input type="hidden" name="profile_id" value=<?= $_GET['profile_id']; ?> >
            <input type="submit" value="Delete" name="delete">
            <a href="index.php">Cancel</a>
        </form>

</div>

	<?php include("inc/footer.php");?>
</body>

</html>
