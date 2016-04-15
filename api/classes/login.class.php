<?php
/**
 * Provides main login functionality.
*/
class LOGIN {
	/**
	 * Start session.
	*/
	function __construct() {
		session_start();
	}

	/**
	 * Check login against the database.
	 * @param username
	 *	String username
	 * @param password
	 *	String password
	 * @return
	 *	Mixed false if username/password is invalid, integer account id if valid.
	*/
	public function checkDatabaseLogin( $username, $password ) {
		$result = mysql_query("SELECT id FROM accounts WHERE username = '".mysql_real_escape_string( $username )."' AND password = '".mysql_real_escape_string( $password )."' LIMIT 1");
		if ( mysql_num_rows( $result ) == 1 ) {
			$row = mysql_fetch_assoc( $result );
			return $row['id'];
		}
		return false;
	}

	/**
	 * Check login against IMAP server
	 * @param username
	 *	String username
	 * @param password
	 *	String password
	 * @return
	 *	Boolean true or false if valid or invalid
	*/
	public function checkIMAPLogin( $username, $password ) {
		$folder = "";
		if ( isset( $_GET['folder'] ) ) {
			$folder = $_GET['folder'];
		}
		$GLOBALS['imap'] = new IMAP( IMAP_SERVER_HOST, IMAP_SERVER_PORT, $username, $password, $folder, IMAP_SERVER_VALIDATE_CERTIFICATE );
		if ( $GLOBALS['imap']->stream == null ) {
			return false;
		}
		return true;
	}

	/**
	 * Check if the user is logged in.
	*/
	function checkLogin( $response = true ) {
		global $format;
		/* Fetch existing session */
		if ( isset( $_SESSION['authenticated'] ) && $_SESSION['authenticated'] == true ) {
			$username = $_SESSION['username'];
			$password = $_SESSION['password'];
			if ( !isset( $_GET['randomNumber'] ) || $_SESSION['randomNumber'] != $_GET['randomNumber'] ) {
				if ( $response == true ) {
					echo $format->jsonResponse( array( "success" => false ) ); // Invalid XSRF check.
					return;
				} else {
					return false;
				}
			}
			/* Do a database check first */
			if ( $this->checkDatabaseLogin( $username, $password ) != false ) {
				if ( $response == true ) {
					echo $format->jsonResponse( array( "success" => true, "firstTime" => false ) ); // We have a valid database login.
				} else {
					return true;
				}
			} elseif ( $this->checkIMAPLogin( $username, $password ) ) {
				if ( $response == true ) {
					echo $format->jsonResponse( array( "success" => true, "firstTime" => true ) ); // We have a valid imap login.
				} else {
					return true;
				}
			} else {
				if ( $response == true ) {
					echo $format->jsonResponse( array( "success" => false ) );
				} else {
					return false;
				}
			}
		} else {
			if ( $response == true ) {
				echo $format->jsonResponse( array( "success" => false ) );
			} else {
				return false;
			}
		}
	}

	/**
	 * Set session data.
	*/
	public function setSession( $username, $password, $account_id = "" ) {
		$_SESSION['authenticated'] = true;
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $password;
		$_SESSION['randomNumber'] = rand( rand( 8000000, 100000000 ), rand( 15000000000, 20000000000000 ) ); // A random number, for XSRF checks
		setcookie("randomNumber", $_SESSION['randomNumber'], time()+3600*24, "/");
		if ( $account_id != "" ) {
			$_SESSION['account_id'] = $account_id;
		}
	}

	function doLogin() {
		global $format;
		$username = $_POST['username'];
		$password = $_POST['password'];
		/* Do a database check first */
		if ( $this->checkDatabaseLogin( $username, $password ) != false ) {
			$this->setSession( $username, $password, $this->checkDatabaseLogin( $username, $password ) );
			echo $format->jsonResponse( array( "success" => true, "firstTime" => false ) ); // We have a valid database login.
		} elseif ( $this->checkIMAPLogin( $username, $password ) ) {
			$this->setSession( $username, $password );
			echo $format->jsonResponse( array( "success" => true, "firstTime" => true ) ); // We have a valid imap login.
		} else {
			echo $format->jsonResponse( array( "success" => false, "msg" => 'Invalid Username or Password.' ) ); // We have no valid username or password.
		}
	}

	function logout() {
		unset( $_SESSION );
		session_destroy();
		setcookie("randomNumber", $_SESSION['randomNumber'], time()-3600*24);
	}
}
?>