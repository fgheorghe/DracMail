<?php
$application = new stdClass(); // Prepare application object
$application->enabled = false; // Enabled or not. By default: false
$application->name = "Notepad"; // Application name
$application->launcher = "api/notepad.class.php"; // Application API (optional), it MUST provide a public method called 'run'
$application->className = "NOTEPAD"; // Application's class name (optional), required if the launcher is configured
$application->javaScriptFiles = array( // Javascript files
	"js/notepad.js" // Application configuration
	,"js/browse.js" // Application configuration
	,"js/application.js" // Application configuration
);
$application->cssFiles = array();
?>
