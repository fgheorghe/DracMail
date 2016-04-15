<?php
require("../applications/Notepad/api/files.class.php"); // The current path is API, so to include Notepad specific files we must point to ../applications/Notepad/api/
/**
 * Provides the link between DRACMAIL and the Notepad application.
*/
class NOTEPAD {
	/**
	 * This function is being called by the webmail backend API, after the login is checked and before the email functionality is being served.
	*/
	public function run() {
		/**
		 * Prepare application specific classes
		*/
		$this->files = new FILES();
		/**
		 * Handle Notepad specific requests.
		*/
		if ( isset( $_GET['application'] ) && $_GET['application'] == "notepad" ) {
			switch ( $_GET['action'] ) {
				case "files":
					$this->files->listFiles();
					break;
				case "open":
					$this->files->openFile( $_GET['id'] );
					break;
				case "save":
					$this->files->saveFile( $_GET['filename'], $_POST['content'] );
					break;
				case "delete":
					$this->files->deleteFile( $_GET['id'] );
					break;
				case "rename":
					$this->files->renameFile( $_GET['id'], $_GET['filename'] );
					break;
			}

			die(); // Look no further.
		}
	}
}
?>