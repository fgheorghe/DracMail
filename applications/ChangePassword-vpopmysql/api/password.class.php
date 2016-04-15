<?php
require("../applications/ChangePassword-vpopmysql/api/configuration.php"); // The current path is API, so to include Notepad specific files we must point to ../applications/ChangePassword-vpopmysql/api/
class PASSWORD {
	private function changePassword() {
		global $format;
		global $login;
		global $allowedDomains;
		global $database;

		$accountId = $_SESSION['account_id'];
		$username = $_SESSION['username'];

		$password = trim( $_POST['password'] );
		$newPassword = trim( $_POST['newpassword'] );
		$confirmPassword = trim( $_POST['confirmpassword'] );

		// Build user and domain data
		$temp = explode( "@", $username );
		$user = $temp[0];
		$domain = $temp[1];

		if ( $newPassword == "" ) {
			echo $format->jsonResponse( array( "success" => false, "msg" => "Please input a new password." ) );
			return;
		}

		if ( $newPassword != $confirmPassword ) {
			echo $format->jsonResponse( array( "success" => false, "msg" => "Password mismatch." ) );
			return;
		}

		if ( $password == "" ) {
			echo $format->jsonResponse( array( "success" => false, "msg" => "Please input your current password." ) );
			return;
		}

		if ( !ctype_alnum( $newPassword ) ) {
			echo $format->jsonResponse( array( "success" => false, "msg" => "New password may only containt alphanumeric characters." ) );
		}

		// Validate current password
		if ( $login->checkDatabaseLogin( $username, $password ) === false ) {
			echo $format->jsonResponse( array( "success" => false, "msg" => "Current password is wrong." ) );
			return;
		}

		// Check for allowed domain names
		if ( !array_key_exists( $domain, $allowedDomains ) || $allowedDomains[$domain] != true ) {
			echo $format->jsonResponse( array( "success" => false, "msg" => "Domain name not configured." ) );
			return;
		}

		// Connect to the vpop database
		@mysql_close();
		mysql_connect( VPOPMAIL_DATABASE_HOSTNAME, VPOPMAIL_DATABASE_USERNAME, VPOPMAIL_DATABASE_PASSWORD );
		mysql_select_db( VPOPMAIL_DATABASE_NAME );

		// Attention to this query:
		mysql_query("UPDATE ".str_replace( ".", "_", $domain )." SET
				pw_passwd = ENCRYPT( '".mysql_real_escape_string( $newPassword )."', CONCAT( \"$1$\", RIGHT( MD5( RAND() ), 8 ), \"$\") )
				,pw_clear_passwd = '".mysql_real_escape_string( $newPassword )."'
			WHERE
				pw_name = '".mysql_real_escape_string( $user )."'
			LIMIT 1
		" );

		$error = mysql_error();
		if ( $error != "" ) {
			echo $format->jsonResponse( array( "success" => false, "msg" => "Password change failed." ) );
			return;
		} else {
			// Connect back to the initial database server
			@mysql_close();
			$database = new MYSQL( MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB );
			// Update the cached password
			mysql_query("UPDATE
					accounts
				SET
					password = '".mysql_real_escape_string( $newPassword )."'
				WHERE
					id = '".(int)$accountId."'
			LIMIT 1");

			$_SESSION['password'] = $newPassword;
		}

		echo $format->jsonResponse( array( "success" => true ) );
		return;
	}

	public function run() {
		if ( isset( $_GET['application'] ) && $_GET['application'] == "changePassword" ) {
			switch ( $_GET['action'] ) {
				case "change":
					$this->changePassword();
					break;
			}

			die(); // Look no further.
		}
	}
}
?>