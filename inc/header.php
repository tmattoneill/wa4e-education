	<title>Matt O'Neill Education Manager</title>

    <meta charset="utf-8">
    <meta name="viewport" 
    	  content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Try to connect to Botostrap CDN -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <script>
    bootstrap_files = {
        "bootstrap-css" : [
            "ver": "4.4.1",
            "type": "text/css",
            "rel": "stylesheet",
            "path": "/bootstrap/",
            "file": "bootstrap.min.css",
            "cdn_path": "https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/"
            ],

        "bootstrap-min-js" : [
            "ver": "4.4.1",
            "type": "script/javascript",
            "path": "/js/",
            "file": "bootstrap.min.js",
            "cdn_path" : "https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/"
        ],

        "bootstrap-bundle-js" : [
            "ver": "4.4.1",
            "type": "script/javascript",
            "path": "",
            "file": "",
            "cdn": "https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"
        ],

        "bootstrap-jquery-js" : [
            "ver": "3.3.1",
            "type": "script/javascript",
            "path": "/jquery/",
            "file": "jquery-ui.min.js",
            "cdn": "https://code.jquery.com/jquery-3.3.1.slim.min.js"
        ],
        
        "popper-js" : [
            "ver": "1.14.7",
            "type": "script/javascript",
            "path": "/node_modules/popper.js/dist/",
            "file": "popper.min.js",
            "cdn": "https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        ]
    }
    </script>
  <!-- Test if can connect to CDN -->

<script>
    var test = document.createElement("div")
    var local_prefix = "/"

    test.className = "hidden d-none"

    document.head.appendChild(test)
    var cssLoaded = window.getComputedStyle(test).display === "none"
    document.head.removeChild(test)

    // If not CDN Access use local versions
    if (!cssLoaded) {
        // bootstrap css
        var bs_link = document.createElement("link");

        bs_link.type = "text/css";
        bs_link.rel = "stylesheet";
        bs_link.href = "/bootstrap/bootstrap.min.css";

        document.head.appendChild(link);
    } // Else use local versions
  </script>

    <!-- Local CSS -->
    <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="/jquery/jquery-ui.css">

    <!-- CDN jQuery-->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" 
    	    integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" 
    	    crossorigin="anonymous"></script>

    <!-- Local jQuery-->
    <script src="/jquery/jquery-ui.min.js"></script>