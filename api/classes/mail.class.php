<?php
/**
 * Provides mail functionality.
*/
class MAIL {
	protected $imap = null;
	/**
	 * Return the name of the FOLDER_TYPE_TRASH folder
	 * @return
	 *	String folder name or empty if not found
	*/
	private function getTrashFolder() { // TODO: These functions are reduntant
		global $folderMapping;
		foreach ( $folderMapping as $key => $value ) {
			if ( $value['type'] == FOLDER_TYPE_TRASH ) {
				return $key;
			}
		}
		return "";
	}

	/**
	 * Return the name of the FOLDER_TYPE_SENT folder
	 * @return
	 *	String folder name or empty if not found
	*/
	private function getSentFolder() {
		global $folderMapping;
		foreach ( $folderMapping as $key => $value ) {
			if ( $value['type'] == FOLDER_TYPE_SENT ) {
				return $key;
			}
		}
		return "";
	}
	
	/**
	 * Return the name of the FOLDER_TYPE_SENT folder
	 * @return
	 *	String folder name or empty if not found
	*/
	private function getDraftFolder() {
		global $folderMapping;
		foreach ( $folderMapping as $key => $value ) {
			if ( $value['type'] == FOLDER_TYPE_DRAFTS ) {
				return $key;
			}
		}
		return "";
	}

	/**
	 * Create Extjs grid paging.
	*/
	private function makePaging() {
		$limit = "";
		if ( isset( $_POST['start'] ) && isset( $_POST['limit'] ) ) {
			$limit = (int)$_POST['start'].",".(int)$_POST['limit'];
		} else {
			$limit = "0,50"; // Load default limit.
		}
		return "LIMIT ".$limit;
	}

	public function buildSort() {
		if ( !isset( $_POST['sort'] ) || !isset( $_POST['dir'] ) ) {
			return "";
		} else {
			switch ( $_POST['sort'] ) {
				case "subject":
					return " subject ".( $_POST['dir'] == "DESC" ? "DESC" : "ASC" );
					break;
				case "sender":
					return "sender ".( $_POST['dir'] == "DESC" ? "DESC" : "ASC" );
					break;
				case "date":
					return "date ".( $_POST['dir'] == "DESC" ? "DESC" : "ASC" );
					break;
				case "size":
					return "size ".( $_POST['dir'] == "DESC" ? "DESC" : "ASC" );
					break;
				case "attachments":
					return "attachments ".( $_POST['dir'] == "DESC" ? "DESC" : "ASC" );
					break;
				default:
					return "";
					break;
			}
		}
	}

	public function list_messages() {
		global $format;
		$folder_id = $_GET['folder_id'];
		$sort = $this->buildSort();
		$limit = $this->makePaging();
		$filter = ""; // See if we need to build a filter
		$includeBody = false;
		$show = "";
		if ( isset( $_POST['show'] ) ) {
			if ( $_POST['show'] == 2 ) {
				$show = " AND seen = 0 ";
			}
			if ( $_POST['show'] == 3 ) {
				$show = " AND seen = 1 ";
			}
		}
		if ( isset( $_POST['value'] ) && isset( $_POST['type'] ) && $_POST['value'] != "" ) {
			$type = 1;
			if ( $_POST['type'] != "" ) {
				$type = (int)$_POST['type'];
			}
			$filter = mysql_real_escape_string( $_POST['value'] );
			switch ( $type ) {
				case 1:
					$filter = " AND ( subject LIKE '%".$filter."%' OR sender LIKE '%".$filter."%' OR content LIKE '%".$filter."%' )";
					$includeBody = true;
					break;
				case 2:
					$filter = " AND subject like '%".$filter."%'";
					break;
				case 3:
					$filter = " AND sender like '%".$filter."%'";
					break;
				case 4:
					$filter = " AND content like '%".$filter."%'";
					$includeBody = true;
					break;
				default:
					$filter = "";
					$includeBody = false;
					break;
			}
		}
		// Count total messages, before paging
		$result = mysql_fetch_assoc( mysql_query("SELECT COUNT(*) AS total FROM folder_messages".( $includeBody == true ? ",message_body" : "" )." WHERE folder_messages.account_id = '".(int)$_SESSION['account_id']."' ".( $includeBody == true ? " AND message_body.message_id = folder_messages.id" : "" )." AND folder_messages.folder_id = '".(int)$folder_id."' ".$filter.$show ) );
		$total = $result['total'];
		$result = mysql_query("SELECT folder_messages.uid AS id, subject, sender, ccaddress, bccaddress, reply_toaddress, date, size, seen, answered, recipient, attachments FROM folder_messages".( $includeBody == true ? ",message_body " : "" )." WHERE folder_messages.account_id = '".(int)$_SESSION['account_id']."' ".( $includeBody == true ? " AND message_body.message_id = folder_messages.id" : "" )." AND folder_messages.folder_id = '".(int)$folder_id."' ".$filter.$show." ORDER BY ".( $sort != "" ? $sort." " : " folder_messages.date DESC " ).$limit );
		$response['total'] = $total;
		$mailList = array();
		for ( $i = 0; $i < mysql_num_rows( $result ); $i++ ) {
			$mailList[$i] = mysql_fetch_assoc( $result );
			$mailList[$i]['subject'] = $format->decodeSubject( $mailList[$i]['subject'] );
			$mailList[$i]['date'] = @strtotime( $mailList[$i]['date'] );
		}
		$response['mailList'] = $mailList;
		echo $format->jsonResponse( $response );
	}

	public function view_message( $uid, $folderId ) {
		$result = mysql_query("SELECT name FROM mail_folders WHERE id = '".(int)$folderId."' AND account_id = '".(int)$_SESSION['account_id']."' LIMIT 1");
		$result = mysql_fetch_assoc( $result );
		$folderName = $result['name'];
		/* Check if chached */
		$result = mysql_query("SELECT * FROM message_body WHERE folder_id = '".(int)$folderId."' AND account_id = '".(int)$_SESSION['account_id']."' AND uid = '".(int)$uid."' LIMIT 1");

		/* Mark as read */
		mysql_query("UPDATE folder_messages SET seen = 1 WHERE folder_id = '".(int)$folderId."' AND account_id = '".(int)$_SESSION['account_id']."' AND uid = '".(int)$uid."' LIMIT 1");
		if ( mysql_num_rows( $result ) == 1 ) {
			/* Return cached message */
			$content = mysql_fetch_assoc( $result );
		} else {
			$this->imap = new IMAP( IMAP_SERVER_HOST, IMAP_SERVER_PORT, $_SESSION['username'], $_SESSION['password'], $folderName, IMAP_SERVER_VALIDATE_CERTIFICATE );
			$content = $this->imap->get_body( $this->imap->stream, $uid );
			/* Cache */
			$result = mysql_query("SELECT id FROM folder_messages WHERE folder_id = '".(int)$folderId."' AND account_id = '".(int)$_SESSION['account_id']."' AND uid = '".(int)$uid."' LIMIT 1");
			$result = mysql_fetch_assoc( $result );
			mysql_query("INSERT INTO message_body SET account_id = '".(int)$_SESSION['account_id']."', folder_id = '".(int)$folderId."', mime_type = '".mysql_real_escape_string( $content['mime_type'] )."', content = '".mysql_real_escape_string( $content['content'] )."', uid = '".(int)$uid."', message_id = '".(int)$result['id']."'");
		}

		switch ( $content['mime_type'] ) {
			case "text":
				header('Content-Type: text/html; charset=utf-8');
				echo $GLOBALS['format']->formatTextMessage( $content['content'] );
				break;
			case "html":
				header('Content-Type: text/html; charset=UTF-8');
				echo $GLOBALS['format']->formatHtmlMessage( $content['content'] );
				break;
		}
	}

	public function list_folders() {
		global $folderMapping;
		global $format;
		$result = mysql_query("SELECT id, name, ( SELECT COUNT(*) FROM folder_messages WHERE folder_id = mail_folders.id AND seen = 0 ) AS `unread`, ( SELECT COUNT(*) FROM folder_messages WHERE folder_id = mail_folders.id AND seen = 1 ) AS `read` FROM mail_folders WHERE account_id = '".(int)$_SESSION['account_id']."' ORDER BY name ASC");
		$response = array();
		for ( $i = 0; $i < mysql_num_rows( $result ); $i++ ) {
			$row = mysql_fetch_row( $result );
			$response[$i]['text'] = $folderMapping[$row[1]]['text'] .' ('.(int)$row[2].'/'.( (int)$row[3] + (int)$row[2] ).')';
			$response[$i]['name'] = $folderMapping[$row[1]]['text'];
			$response[$i]['folder_type'] = $folderMapping[$row[1]]['type']; // NOTE: This is the _folder_ type.
			$response[$i]['folder_id'] = (int) $row[0];
			$response[$i]['leaf'] = true;
			$response[$i]['type'] = "clickable"; // NOTE: This is the _node_ type.
		}

		echo $format->jsonResponse( $response );
	}

	public function mark_message( $as, $ids, $folderId ) {
		switch ( $as ) {
			case 2:
				$as = 0; // Unread
				break;
			case 1:
				$as = 1; // Read
				break;
		}
		/* Build message SQL in */
		$idArray = explode( ",", $ids );
		$sqlIn = "";
		for ( $i = 0; $i < count( $idArray ); $i++ ) {
			if ( $i != 0 ) {
				$sqlIn .= ",";
			}
			$sqlIn .= (int)$idArray[$i];
		}
		/* Update messages */
		mysql_query("UPDATE folder_messages SET seen = '".(int)$as."' WHERE account_id = '".(int)$_SESSION['account_id']."' AND folder_id = '".(int)$folderId."' AND uid IN (".$sqlIn.")");
	}

	private function makeAttachments() {
		$attachments = array(); // Inneficient function, for fetching uploaded files
		if ( isset( $_FILES['attachment_1'] ) ) {
			if ( is_uploaded_file( $_FILES['attachment_1']['tmp_name'] ) ) {
				array_push( $attachments, array(
						"content" => file_get_contents( $_FILES['attachment_1']['tmp_name'] )
						,"filename" => $_FILES['attachment_1']['name']
					)
				);
			}
		}
		if ( isset( $_FILES['attachment_2'] ) ) {
			if ( is_uploaded_file( $_FILES['attachment_2']['tmp_name'] ) ) {
				array_push( $attachments, array(
						"content" => file_get_contents( $_FILES['attachment_2']['tmp_name'] )
						,"filename" => $_FILES['attachment_2']['name']
					)
				);
			}
		}
		if ( isset( $_FILES['attachment_3'] ) ) {
			if ( is_uploaded_file( $_FILES['attachment_3']['tmp_name'] ) ) {
				array_push( $attachments, array(
						"content" => file_get_contents( $_FILES['attachment_3']['tmp_name'] )
						,"filename" => $_FILES['attachment_3']['name']
					)
				);
			}
		}
		return $attachments;
	}

	private function makeBody( $to, $subject, $content, $cc, $bcc, $attachments ) {
		$envelope["from"] = $_SESSION['username'];
		$envelope["reply_to"] = $_SESSION['username'];
		$envelope["cc"] = $cc;
		$envelope["bcc"] = $bcc;
		$body[0]["type"] = count( $attachments ) == 0 ? TYPETEXT : TYPEMULTIPART;
		$body[0]["subtype"] = count( $attachments ) == 0 ? "html" : "mixed";
		if ( count( $attachments ) != 0 ) {
			array_push( $body, array (
					"type" => TYPETEXT
					,"subtype" => "html"
					,"contents.data" => $content
				)
			);
		}
		for ( $i = 0; $i < count( $attachments ); $i++ ) {
			array_push( $body, array(
					"type" => TYPEAPPLICATION
					,"type.parameters" => array( "name" => $attachments[$i]['filename'] )
					,"encoding" => ENCBASE64
					,"subtype" => "octet-stream"
					,"description" => $attachments[$i]['filename']
					,"disposition.type" => 'attachment'
					,"parameters.name" => $attachments[$i]['filename']
					,"dparameters.filename" => $attachments[$i]['filename']
					,"contents.data" => base64_encode( $attachments[$i]['content'] )
				) 
			);
		}
		return array( "body" => $body, "envelope" => $envelope );
	}

	private function cleanHeader( $string ) {
		$string = str_replace( "\n", "", $string );
		$string = str_replace( "\r", "", $string );

		return $string;
	}

	public function compose( $draft, $to, $subject, $content, $cc, $bcc ) {
		global $format;
		// Validate input strings
		$to = $this->cleanHeader( $to );
		$subject = $this->cleanHeader( $subject );
		$cc = $this->cleanHeader( $cc );
		$bcc = $this->cleanHeader( $bcc );

		$username = $_SESSION['username'];
		$password = $_SESSION['password'];
		if ( $draft == 0 ) {
			$folder = $this->getDraftFolder();
		} else {
			$folder = $this->getSentFolder();
		}
		$attachments = $this->makeAttachments();
		$temp = $this->makeBody( $to, $subject, $content, $cc, $bcc, $attachments );
		$body = $temp["body"];
		$envelope = $temp["envelope"];
		if ( $draft == 0 ) {
			// Nothing, for now.
		} else {
			$mail = imap_mail_compose( $envelope, $body );
			imap_mail( $to, $subject, $content, $mail );
		}
		unset( $body );
		unset( $envelope );
		unset( $mail );
		$temp = $this->makeBody( $to, $subject, $content, $cc, $bcc, $attachments );
		$body = $temp["body"];
		$envelope = $temp["envelope"];
		$this->imap = new IMAP( IMAP_SERVER_HOST, IMAP_SERVER_PORT, $username, $password, $folder, IMAP_SERVER_VALIDATE_CERTIFICATE );
		// Rebuild MIME message, for storing in the 'sent' folder
		$envelope["to"] = $to;
		$envelope["cc"] = $cc;
		$envelope["bcc"] = $bcc;
		$envelope["subject"] = $subject;
		$body[0]["contents.data"] = $content;
		$mail = imap_mail_compose( $envelope, $body );
		$this->imap->imap_append( $mail, $draft == 0 ? "\\Draft \\Seen" : null );
		echo $format->jsonResponse( array( "success" => true ) );
		die();
	}

	private function checkOwner( $message_id ) {
		$account_id = (int)$_SESSION['account_id'];
		$result = mysql_query("SELECT id FROM folder_messages WHERE uid = '".(int)$message_id."' AND account_id = '".(int)$account_id."' LIMIT 1");
		if ( mysql_num_rows( $result ) != 1 ) {
			return false;
		} else {
			return true;
		}
	}
	
	private function getFolderName( $folder_id ) { 
		$account_id = (int)$_SESSION['account_id'];
		$result = mysql_query("SELECT name FROM mail_folders WHERE id = '".(int)$folder_id."' AND account_id = '".(int)$account_id."' LIMIT 1");
		if ( mysql_num_rows( $result ) != 1 ) {
			return false;
		} else {
			$result = mysql_fetch_assoc( $result );
			return $result['name'];
		}
	}

	public function delete( $ids, $folder_id, $to_folder_id = "", $move = false ) {
		global $synch;
		$username = $_SESSION['username'];
		$password = $_SESSION['password'];
		$account_id = (int)$_SESSION['account_id'];
		/* Build message SQL IN and check owner */
		$idArray = explode( ",", $ids );
		$messageSequence = "";
		for ( $i = 0; $i < count( $idArray ); $i++ ) {
			if ( $i != 0 ) {
				$messageSequence .= ",";
			}
			if ( !$this->checkOwner( $idArray[$i] ) ) {
				die(); // Ignore the request.
			}
			$messageSequence .= (int)$idArray[$i];
		}

		$folderName = $this->getFolderName( $folder_id );
		if ( $move == false ) {
			$targetFolder = $this->getTrashFolder();
		} else {
			$targetFolder = $this->getFolderName( $to_folder_id );
		}

		if ( $folderName === false || $targetFolder === false ) {
			die(); // Ignore the request, if the folder does not exist, or does not belong to current account.
		}

		// Move to trash folder OR if in trash folder -> delete
		$this->imap = new IMAP( IMAP_SERVER_HOST, IMAP_SERVER_PORT, $username, $password, $folderName, IMAP_SERVER_VALIDATE_CERTIFICATE );

		if ( $move == false ) {
			if ( $folderName != $targetFolder ) {
				$this->imap->imap_mail_move( $messageSequence, $targetFolder );
			} else {
				for ( $i = 0; $i < count( $idArray ); $i++ ) {
					$this->imap->imap_delete( $idArray[$i] );
				}
				$this->imap->imap_expunge();
			}
			mysql_query("DELETE FROM folder_messages WHERE account_id = '".(int)$account_id."' AND uid IN (" . $messageSequence . ") AND folder_id = '".(int)$folder_id."'" );
			mysql_query("DELETE FROM message_body WHERE account_id = '".(int)$account_id."' AND uid IN (" . $messageSequence . ") AND folder_id = '".(int)$folder_id."'" );
			mysql_query("DELETE FROM attachments WHERE account_id = '".(int)$account_id."' AND uid IN (" . $messageSequence . ") AND folder_id = '".(int)$folder_id."'" );
		} else {
			$this->imap->imap_mail_move( $messageSequence, $targetFolder );
			$this->imap->close();
			mysql_query("DELETE FROM folder_messages WHERE account_id = '".(int)$account_id."' AND uid IN (" . $messageSequence . ") AND folder_id = '".(int)$folder_id."'" );
			$synch->connect( $targetFolder );
			$synch->import_messages( $to_folder_id, $account_id );
		}
	}

	public function move( $ids, $folder_id, $to_folder_id ) {
		$this->delete( $ids, $folder_id, $to_folder_id, true );
	}
	
	public function fetchAttachment( $folder_id, $message_id, $filename ) {
		$filename = urldecode( $filename );
		$folderName = $this->getFolderName( $folder_id );
		if ( $folderName === false || $this->checkOwner( $message_id ) === false ) {
			die(); // Folder or message does not belong to current user.
		}
		global $synch;
		$synch->connect( $folderName );
		$result = $synch->get_attachments( $message_id, $filename );
		if ( $result === false || $filename == "" ) {
			header("HTTP/1.0 404 Not Found");
			die("File not found.");
		} else {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.urlencode( basename( $filename ) ) );
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . strlen( $result ) );
			echo $result;
		}
	}
}
?>