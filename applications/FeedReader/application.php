<?php
$application = new stdClass(); // Prepare application object
$application->enabled = false; // Enabled or not. By default: false
$application->name = "Feed Reader"; // Application name
$application->launcher = "api/feedReader.class.php"; // Application API
$application->className = "FEEDREADER"; // Application's class name (optional), required if the launcher is configured
$application->javaScriptFiles = array( // Javascript files
	"js/feedReader.js"
	,"js/application.js" // Application configuration
);
$application->cssFiles = array();
?>
