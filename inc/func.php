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
	    $table = "<table border=1>\n<tbody>";
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
	
	function get_user () {
	    global $pdo;
	    
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
	
?>
