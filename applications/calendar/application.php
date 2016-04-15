<?php
$application = new stdClass(); // Prepare application object
$application->enabled = false; // Enabled or not. By default: false
$application->name = "Calendar Application"; // Application name
$application->launcher = "api/calendar.class.php"; // Application API (optional), it MUST provide a public method called 'run'
$application->className = "CALENDAR"; // Application's class name (optional), required if the launcher is configured
$application->javaScriptFiles = array( // Javascript files
	"js/extensible-all-debug.js"
	,"js/MemoryEventStore.js"
	,"js/calendarApplication.js"
	,"js/application.js" // Application configuration
);
$application->cssFiles = array( // Optional CSS files
	array(
		"file" => "css/resources/css/extensible-all.css"
	)
);
?>
