function doValidate() {

	// validate password field
	console.log('Validating usename and password...');
	try {
		pw = document.getElementById('pwd_pass').value;
		em = document.getElementById('txt_email').value;
		console.log("Validating em: " + em);

		// Field validation
		if (pw == null || pw == "" || 
			em == null || em == "") {
			alert("Both fields must be filled out");
			return false; // validation failed
		}

		// email formation check
		if ( em.search("@") < 0 ) {
			alert("Username must contain '@' in email");
			return false; // validation failed
		}

		return true; // validation passed		


	} catch(e) {
		return false; // try
	}
	return false; // function
}

// TODO: Education

function validateEmail(em) {

		// email formation check
		if ( em.search("@") < 0 ) {
			alert("Email address must contain '@'");
			return false; // validation failed
		}

		return true; // validation passed	

}

function validateYear(year) {

	if ( ! isNaN(year)) {
		return true;
	} else {
		alert("Year must be a number.");
		return false;
	}
}

function validateAdd(arrTagNames) {
	// arrTagName is an array of one or more form tag names to validate against
	console.log("Validating form...");
	for (const tag of arrTagNames) {

		var fields = document.getElementsByTagName(tag);

		for (const field of fields) {
			
			// Check that all fields are completed
			if ( field.value == "" ) {
				console.log("Error: ".field.value);
				alert ("All fields are required");
				return false;
			}
			// Check that the Year fields are numberic (e.g. 1999)
			// There may be up to 9 of these so need to check them all.
			if ( field.getAttribute("name").search("year") >= 0 ) {
				//alert("Year must be numeric");
				return (validateYear( field.value ));
			}
		}
	}

	if (! validateEmail(document.getElementById("txt_email").value)) {

		return false;
	}

	return true;

}

