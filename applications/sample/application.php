<?php
$application = new stdClass(); // Prepare application object
$application->enabled = false; // Enabled or not. By default: false
$application->name = "Sample Application"; // Application name
$application->launcher = "api/sample.php"; // Application API
$application->javaScriptFiles = array( // Javascript files
	"js/sampleApplication.js" // Application code
	,"js/application.js" // Application configuration
);
$application->cssFiles = array( // Optional CSS files
	array(
		"title" => "sampleStyleSheet" // Optional name
		,"file" => "css/sampleApplicationStyle.css" // File name
	)
);
?>
