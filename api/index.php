<?php

test fail

/**
 * dracMail API
*/

// Load configuration
require("configuration.php");

// Load DB functionality
require(CLASSES_PATH."mysql.class.php");

// Load IMAP functionality
require(CLASSES_PATH."imap.class.php");

// Load Login functionality
require(CLASSES_PATH."login.class.php");

// Load Synch functionality
require(CLASSES_PATH."synch.class.php");

// Load Format functionality
require(CLASSES_PATH."format.class.php");

// Load Mail functionality
require(CLASSES_PATH."mail.class.php");

// Load Application mechanism
require(CLASSES_PATH."application.class.php");

// Prepare classes
$login = new LOGIN();
$synch = new Synch();
$mail = new MAIL();
$format = new FORMAT();
$database = new MYSQL( MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB );

/* Serve login functionality */
if ( isset( $_GET['action'] ) ) {
	switch ( $_GET['action'] ) {
		case 'checkLogin':
			$login->checkLogin();
			break;
		case 'doLogin':
			$login->doLogin();
			break;
	}
}

/* Check if the user is logged in */
if ( $login->checkLogin( false ) == false ) {
	die();
}

/* Call applications here */
$application = new APPLICATION( "api" ); // Load the Application API.
if ( isset( $application->applications ) && is_array( $application->applications ) ) {
	// Load every main file ($application->launcher) required, for every enabled application.
	for ( $i = 0; $i < count( $application->applications ); $i++ ) {
		require( $application->applications[$i]["file"] );

		if ( isset( $application->applications[$i]["className"] ) ) {
			$className = strtolower( $application->applications[$i]["className"] );
			$variableName = $application->applications[$i]["className"];
			$$className = new $variableName();
			$$className->run();
		}
	}
}

/* Serve UI functionality */
if ( isset( $_GET['action'] ) ) {
	switch ( $_GET['action'] ) {
		case 'logout': // For doing a logout, you must be logged in, and have a valid 'randomNumber'
			$login->logout();
			break;
		case 'listFolders':
			$mail->list_folders();
			break;
		case "listMessages":
			$mail->list_messages();
			break;
		case "viewMessage":
			$mail->view_message( $_GET['id'], $_GET['folder_id'] );
			break;
		case "doSynch":
			$synch->doSynch( $_GET['step'] );
			break;
		case "checkMail":
			$synch->doSynch( $_GET['action'] );
			break;
		case "mark":
			$mail->mark_message( $_GET['as'], $_GET['ids'], $_GET['folder_id'] );
			break;
		case "sendMail":
			$mail->compose( $_POST['draft'], $_POST['to'], $_POST['subject'],$_POST['content'], $_POST['cc'], $_POST['bcc'] );
			break;
		case "delete":
			$mail->delete( $_GET['ids'], $_GET['folder_id'] );
			break;
		case "move":
			$mail->move( $_GET['ids'], $_GET['folder_id'], $_GET['to_folder_id'] );
			break;
		case "fetchAttachment":
			$mail->fetchAttachment( $_GET['folder_id'], $_GET['message_id'], $_GET['filename'] );
			break;
	}
}
?>
