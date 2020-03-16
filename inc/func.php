<?php
    /*  Helper functions for the WA4E set of projects. Contents:
     *  exists_in_db: checks to see if a value exists in the database
     *  profile_table: generates a user table
     *  get_all_profiles: returns a join table of user IDs and Profile IDs
     */

	function exists_in_db($pdo, $field, $table, $val) {
		// returns true if a given value exists in a database.
		// takes a PDO connection, a string of a field name and a string of a table
		// and the value to check.
		// returns true if found else false.

		$sql = "SELECT $field from $table where $field = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(1, $val);
		$stmt->execute();

		if ($stmt->rowCount() ) {
			return true;
		}

		return false;
	}
	
	function profile_table($profiles) {
	    // take a PDO object and return a string that generates a table in html
	    // gets all rows and fields
	    $table = "<table class='table-striped'>\n<tbody>";
	    $table .= "<thead><tr><th>Name</th><th>Headline</th><th>Action</th></thead>";
	    
	    while ( $row = $profiles->fetch() ) {
	        $table .= "\n<tr>";
	        
	        $user_id = $row["user_id"];
	        $profile_id = $row["profile_id"];
	        $full_name = $row["full_name"];
	        $headline = $row["headline"];
	        
	        $table .= "\n\t<td><a href='view.php?profile_id=$profile_id'>$full_name</a></td>";
	        $table .= "\n\t<td>$headline</td>";
	        $table .= "\n\t<td><a href='edit.php?profile_id=$profile_id'>Edit</a>&nbsp;" .
	        "<a href='delete.php?profile_id=$profile_id'>Delete</a></td>";
	        
	        $table .= "\n</tr>";
	    }
	    
	    $table .= "\n</tbody></table>";
	    
	    return $table;
	}
	
	function get_all_profiles () {
	    global $pdo;
	    
	    $sql = "SELECT Profile.user_id, profile_id, CONCAT(first_name, ' ', last_name) as full_name, headline
			FROM Profile JOIN users ON Profile.user_id = users.user_id
			WHERE 1";
	    
	    return $pdo->query($sql);
	    
	}
	
	function get_user ($pdo) {

	    $stmt = $pdo->prepare("SELECT user_id, name, email
							   FROM users
						       WHERE email = :em");
	    
	    $stmt->execute(array(':em' => $_POST['email']));
	    
	    $row = $stmt->fetch(PDO::FETCH_ASSOC);
	    
	    return $row;
	    
	}
	
	function check_password() {
	    
	    // Note that the salt here is fixed as a variable (set in config.php).
	    // This function assumes that isset has been checked for password and
	    // a valid password structure has been entered.
	    global $salt, $pdo;
	    
	    $check = hash('md5', $salt.$_POST['pass']);
	    
	    $stmt = $pdo->prepare("SELECT COUNT(email) as found
						       FROM users
						       WHERE email = :em AND password = :pw");
	    
	    $stmt->execute(array(
	        ':em' => $_POST['email'],
	        ':pw' => $check));
	    
	    $row = $stmt->fetch(PDO::FETCH_ASSOC);
	    $password_ok = $row["found"];
	    
	    return $password_ok;
	    
	}

	function flash_msg() {

		if ( isset($_SESSION["error"]) ) {
			echo "<p class='alert alert-warning'>";
			echo $_SESSION["error"] . "</p>";
			unset($_SESSION["error"]);
		
		} else if ( isset($_SESSION["success"]) ) {
			echo "<p class='alert alert-success'>";
			echo $_SESSION["success"] . "</p>";
			unset($_SESSION["success"]);

		} 
	}

	function require_login($msg=null, $dest=null) {

		if (! isset($_SESSION["user_id"])) {  // Not logged in
			if ( isset($dest)) {
				err_redir($msg, $dest);
			} else
				die(ERR_NO_ACCESS);
		}
	}

	function err_redir($msg, $dest) {
		$_SESSION["error"] = $msg;
		header("Location: $dest");
		exit();
	}

	function validate_position() {
  		for($i=1; $i<=9; $i++) {
		    if ( ! isset($_POST['pos_year'][$i]) ) continue;
		    if ( ! isset($_POST['pos_desc'][$i]) ) continue;

		    $year = $_POST['pos_year'][$i];
		    $desc = $_POST['pos_desc'][$i];

		    if ( strlen($year) == 0 || strlen($desc) == 0 ) {
		      return "All fields are required";
	    }

	    if ( ! is_numeric($year) ) {
	      return "Year must be numeric(e.g. 1998)";
	    }
	  }
	  return true;
	}

	function alert_out($str) {
		print "<script>alert(\"" . var_dump($str). "\")</script>";
  }

	function field_name_to_text($title) {
		// takes a mysql field name (column) and returns a friendly
		// string for use as a label or column head
		// Heuristics:
		//   - replace "_" with " "
		//   - use Title Case
		// $title: string value
		$title = str_replace("_", " ", $title);
		$title = ucwords($title);

	}

	function insert_positions($pdo, $profile_id) {
		$rank = 1;
	    for($i=1; $i<=9; $i++) {
	        if ( ! isset($_POST['year_pos'][$i]) ) continue;
	        if ( ! isset($_POST['pos_desc'][$i]) ) continue;

	        $year = $_POST['year_pos'][$i];
	        $desc = $_POST['pos_desc'][$i];

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
	}

	function insert_educations($pdo, $profile_id) {
		$rank = 1;											// <-- THIS COULD BE FUCKING THINGS UP (startng at 1 not 0;)

	    for($i=1; $i<=9; $i++) {
	        if ( ! isset($_POST['year_edu'][$i]) ) continue;
	        if ( ! isset($_POST['edu_desc'][$i]) ) continue;

	        $year = $_POST['year_edu'][$i];
	        $name = $_POST['edu_desc'][$i];


			$stmt = $pdo->prepare('SELECT institution_id 
								   FROM Institution 
								   WHERE name =:name');

			$stmt->execute(array(':name' => $name));
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$institution_id = $row ? $row["institution_id"] : false;

			if (! $institution_id ) {
				$stmt = $pdo->prepare('INSERT INTO Institution (name) 
									   VALUES ( :name) ');

				$stmt->execute(array(':name' => $name));
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

	        $rank++;
	    }
	}
	
?>
