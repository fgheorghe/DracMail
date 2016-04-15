<?php
/**
 * Provides file manipulation functionality for the Notepad application.
 * Makes use of the existing DATABASE connection.
*/
class FILES {
	public function listFiles( $filter = "" ) {
		global $format;
		$accountId = $_SESSION['account_id'];
		$result = mysql_query("SELECT id, filename FROM application_notepad WHERE account_id = '".(int)$accountId."'");
		$response = array();
		for ( $i = 0; $i < mysql_num_rows( $result ); $i++ ) {
			$row = mysql_fetch_assoc( $result );
			$response[$i]['text'] = $row['filename'];
			$response[$i]['file_id'] = (int)$row['id'];
			$response[$i]['leaf'] = true;
			$response[$i]['type'] = "file";
		}

		echo $format->jsonResponse( $response );
	}

	public function openFile( $id ) {
		$accountId = $_SESSION['account_id'];
		$result = mysql_query("SELECT content FROM application_notepad WHERE account_id = '".(int)$accountId."' AND id = '".(int)$id."' LIMIT 1");
		$row = mysql_fetch_assoc( $result );

		echo $row['content'];
	}

	public function saveFile( $filename, $content ) {
		$accountId = $_SESSION['account_id'];
		// Check if file exists
		$result = mysql_query("SELECT id FROM application_notepad WHERE account_id = '".(int)$accountId."' AND filename = '".mysql_real_escape_string( $filename )."' LIMIT 1");
		if ( mysql_num_rows( $result ) == 1 ) {
			$id = mysql_fetch_assoc( $result );
			$id = $id['id'];
			mysql_query("UPDATE application_notepad SET content = '".mysql_real_escape_string( $content )."' WHERE account_id = '".(int)$accountId."' AND id = '".(int)$id."' LIMIT 1");
		} else {
			mysql_query("INSERT INTO application_notepad SET content = '".mysql_real_escape_string( $content )."', account_id = '".(int)$accountId."', filename = '".mysql_real_escape_string( $filename )."'");
		}
	}

	public function deleteFile( $id ) {
		$accountId = $_SESSION['account_id'];
		mysql_query("DELETE FROM application_notepad WHERE account_id = '".(int)$accountId."' AND id = '".(int)$id."' LIMIT 1");
	}

	public function renameFile( $id, $filename ) {
		$accountId = $_SESSION['account_id'];
		$result = mysql_query("SELECT id FROM application_notepad WHERE account_id = '".(int)$accountId."' AND filename = '".mysql_real_escape_string( $filename )."' LIMIT 1");
		if ( mysql_num_rows( $result ) == 1 ) { // Rename a file: delete the old and rename selected
			$id = mysql_fetch_assoc( $result );
			$id = $id['id'];
			$this->deleteFile( $id );
		}
		mysql_query("UPDATE application_notepad SET filename = '".mysql_real_escape_string( $filename )."' WHERE id = '".(int)$id."' AND account_id = '".(int)$accountId."' LIMIT 1");
	}
}
?>