<?php
// create table application_calendar ( id int(10) auto_increment not null, account_id int(10), cid int(10), title varchar(1024), start datetime, end datetime, notes text, url varchar(255), rem int(10), loc varchar(255), ad int(1), primary key(id) );
require("../applications/calendar/api/data.class.php"); // The current path is API, so to include Notepad specific files we must point to ../applications/Notepad/api/
class CALENDAR {
	/**
	 * This function is being called by the webmail backend API, after the login is checked and before the email functionality is being served.
	*/
	public function run() {
		/**
		 * Prepare application specific classes
		*/
		$this->data = new DATA();
		/**
		 * Handle Calendar specific requests.
		*/
		if ( isset( $_GET['application'] ) && $_GET['application'] == "calendar" ) {
			switch ( $_GET['action'] ) {
				case "load":
					$this->data->load();
					break;
				case "add":
					$this->data->add();
					break;
				case "update":
					$this->data->update();
					break;
				case "delete":
					$this->data->delete();
					break;
			}

			die(); // Look no further.
		}
	}
}
?>