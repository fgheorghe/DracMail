<?php
/**
 * Provider for objSynch.js
*/
class SYNCH {
	protected $imap = null;
	private function list_folders( $importing = false ) {
		global $format;

		$folders = $this->imap->imap_list();
		if ( $importing == true ) { // Check if folders exist, and create those that are needed
			global $folderMapping;
			foreach ( $folderMapping as $folderName => $attributes ) {
				$exists = false;
				foreach ( $folders as $folder => $name ) {
					if ( $name == $folderName ) {
						$exists = true;
					}
				}
				if ( !$exists && $attributes['create'] == true ) {
					$this->imap->imap_createmailbox( $folderName );
				}
			}
			$folders = $this->imap->imap_list(); // Fetch the new list
		}
		$response = array();
		$response['mailFolders'] = array();
		foreach ( $folders as $folder => $name ) {
			array_push( $response['mailFolders'], $name );
		}
		echo $format->jsonResponse( $response );
	}

	private function buildMailAddress( $object ) {
		$address = "";
		for ( $i = 0; $i < count( $object ); $i++ ) {
			if ( $i != 0 ) {
				$address .= "; ";
			}
			$address .= $object[$i]->mailbox."@".$object[$i]->host;
		}
		return $address;
	}

	public function import_messages( $folderId, $accountId, $checkOnly = false ) {
		// Import messages...
		if ( $this->imap->stream == null ) {
			return; // Connection lost...
		}
		$folderInfo = $this->imap->imap_check();
		if ( $folderInfo->Nmsgs == 0 ) {
			return false; // Nothing to import
		}
		$filter = "1:*";
		if ( $checkOnly == true ) {
			/* Get the last message id */
			$lastMsg = 1;
			$result = mysql_query("SELECT uid FROM folder_messages WHERE folder_id = '".(int)$folderId."' AND account_id = '".(int)$accountId."' ORDER by uid DESC LIMIT 1");
			if ( mysql_num_rows( $result ) == 1 ) {
				$lastMsg = mysql_fetch_assoc( $result );
				$lastMsg = $lastMsg['uid'];
			}
			$filter = $lastMsg.":*";
		}

		$messages = $this->imap->imap_fetch_overview( $filter );
		for ( $i = 0; $i < count( $messages ); $i++ ) {
			$header = $this->imap->imap_headerinfo( $messages[$i]->msgno );
			// Check for duplicates
			$result = mysql_query("SELECT * FROM folder_messages WHERE uid = '".(int)$messages[$i]->uid."' AND folder_id = '".(int)$folderId."' AND account_id = '".(int)$accountId."'");
			if ( mysql_num_rows( $result ) == 0 ) {
				// Check for attachments
				$attachmentsString = "";
				$attachments = $this->imap->get_attachments( $messages[$i]->uid );
				for ( $k = 0; $k < count( $attachments ); $k++ ) {
					mysql_query("INSERT INTO attachments
						SET
							uid = '".(int)$messages[$i]->uid."'
							,account_id = '".(int)$accountId."'
							,filename = '".mysql_real_escape_string( $attachments[$k]['filename'] )."'
					");
					if ( $k != 0 ) {
						$attachmentsString .= ",";
					}
					$attachmentsString .= $attachments[$k]['filename'];
				}
				mysql_query("INSERT INTO folder_messages
					SET
						folder_id = '".(int)$folderId."'
						,account_id = '".(int)$accountId."'
						,uid = '".mysql_real_escape_string( $messages[$i]->uid )."'
						,message_id = '".mysql_real_escape_string( $header->message_id )."'
						,subject = '".mysql_real_escape_string( ( isset( $header->subject ) ? $header->subject : "" ) )."'
						,sender = '".mysql_real_escape_string( $this->buildMailAddress( $header->from ) )."'
						,date = FROM_UNIXTIME('".mysql_real_escape_string( $header->udate )."')
						,size = '". (double) $header->Size ."'
						,seen = '0'
						,answered = '".( $header->Answered == "A" ? 1 : 0 )."'
						,recipient = '".mysql_real_escape_string( $this->buildMailAddress( $header->to ) )."'
						,reply_toaddress = '".mysql_real_escape_string( isset( $header->reply_to ) ? $this->buildMailAddress( $header->reply_to ) : "" )."'
						,ccaddress = '".mysql_real_escape_string( isset( $header->cc ) ? $this->buildMailAddress( $header->cc ) : "" )."'
						,bccaddress = '".mysql_real_escape_string( isset( $header->bcc ) ? $this->buildMailAddress( $header->bcc ) : "" )."'
						,attachments = '".mysql_real_escape_string( $attachmentsString )."'
				");
			}
		}
	}

	private function import_folder( $accountId ) {
		global $format;
		// Save folder
		mysql_query("INSERT INTO mail_folders SET name = '".mysql_real_escape_string( $_GET['folder'] )."', account_id = '".(int)$accountId."'");
		$folderId = mysql_insert_id();
		$this->imap = new IMAP( IMAP_SERVER_HOST, IMAP_SERVER_PORT, $_SESSION['username'], $_SESSION['password'], $_GET['folder'], IMAP_SERVER_VALIDATE_CERTIFICATE );
		$this->import_messages( $folderId, $accountId );

		echo $format->jsonResponse( array( "success" => true ) );
		die();
	}

	private function import_user( $username, $password ) {
		mysql_query("INSERT INTO accounts SET username = '".mysql_real_escape_string( $username )."', password = '".mysql_real_escape_string( $password )."'");
		return mysql_insert_id();
	}

	public function get_attachments( $uid, $filename = "" ) {
		return $this->imap->get_attachments( $uid, $filename );
	}

	private function checkMail( $accountId ) {
		// Get all DB folders
		$result = mysql_query("SELECT id, name FROM mail_folders WHERE account_id = '".(int)$accountId."'");
		$count = mysql_num_rows( $result );
		for ( $i = 0; $i < $count; $i++ ) {
			$row = mysql_fetch_assoc( $result );
			/* Open imap connection */
			$this->imap = new IMAP( IMAP_SERVER_HOST, IMAP_SERVER_PORT, $_SESSION['username'], $_SESSION['password'], $row['name'], IMAP_SERVER_VALIDATE_CERTIFICATE );
			$this->import_messages( (int) $row['id'], $accountId, true );
			$this->imap->close();
		}
		$GLOBALS['mail']->list_folders();
	}

	public function connect( $folder = "" ) {
		$username = $_SESSION['username'];
		$password = $_SESSION['password'];
		$this->imap = new IMAP( IMAP_SERVER_HOST, IMAP_SERVER_PORT, $username, $password, $folder, IMAP_SERVER_VALIDATE_CERTIFICATE );
	}

	public function doSynch( $step ) {
		$username = $_SESSION['username'];
		$password = $_SESSION['password'];
		// Step 1, import user.
		switch ( $step ) {
			case "0":
				$_SESSION['account_id'] = $this->import_user( $username, $password );
				$this->connect();
				$this->list_folders( true );
				break;
			case "1":
				$this->import_folder( $_SESSION['account_id'] );
				break;
			case "checkMail":
				$this->checkMail( $_SESSION['account_id'] );
				break;
		}
	}
}
?>