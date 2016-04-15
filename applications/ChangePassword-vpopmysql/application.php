<?php
$application = new stdClass(); // Prepare application object
$application->enabled = false; // Enabled or not. By default: false
$application->name = "Change Password"; // Application name
$application->launcher = "api/password.class.php"; // Application API (optional), it MUST provide a public method called 'run'
$application->className = "PASSWORD"; // Application's class name (optional), required if the launcher is configured
$application->javaScriptFiles = array( // Javascript files
	"js/changePassword.js"
	,"js/application.js" // Application configuration
);
$application->cssFiles = array();
?>