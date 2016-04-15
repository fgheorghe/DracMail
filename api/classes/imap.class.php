<?php
/**
 * Provides IMAP functionality.
*/
class IMAP {
	public $stream;
	protected $mailbox;
	/**
	 * Initiate connection.
	*/
	public function __construct( $hostname, $port, $username, $password, $folder = "", $validateCertificate = false ) {
			$this->mailbox = "{".$hostname.":".$port.( $validateCertificate == false ? "/novalidate-cert" : "/validate-cert" )."}".$folder;
			/* An error occured, set the stream to null */
			$this->stream = @imap_open(
				$this->mailbox
				,$username
				,$password
			);
			$error = imap_last_error();
			/* An error occured, set the stream to null */
			if ( $error != "" ) {
				$this->stream = null;
			}
	}

	public function cleanFolders( $folders ) {
		foreach ( $folders as $folder => $name ) {
			$folders[$folder] = str_replace( $this->mailbox, "", $name );
		}
		return $folders;
	}

	public function imap_list() {
		return $this->cleanFolders( imap_list( $this->stream, $this->mailbox, "*" ) );
	}

	public function imap_check() {
		return imap_check( $this->stream );
	}

	public function imap_fetch_overview( $filter ) {
		return imap_fetch_overview( $this->stream, $filter, FT_UID );
	}

	public function imap_headerinfo( $msgno ) {
		return imap_headerinfo( $this->stream, $msgno );
	}

	public function view_message( $msgno ) {
		echo $this->get_body( $this->stream, $msgno );
	}

	public function close() {
		if ( $this->stream != null ) {
			imap_close( $this->stream );
		}
	}

	public function imap_createmailbox( $folder ) {
		imap_createmailbox( $this->stream, $this->mailbox.$folder );
	}

	public function imap_expunge() {
		imap_expunge( $this->stream );
	}

	public function imap_delete( $uid ) {
		imap_delete( $this->stream, $uid, FT_UID );
	}

	public function imap_mail_move( $msglist, $folder ) {
		imap_mail_move( $this->stream, $msglist, $folder, FT_UID );
		$this->imap_expunge();
	}

	public function imap_append( $content, $flags = null ) {
		return imap_append( $this->stream, $this->mailbox, $content, $flags );
	}

	/* PHP.net */
	private function get_mime_type( &$structure ) {
		$primary_mime_type = array("TEXT", "MULTIPART","MESSAGE", "APPLICATION", "AUDIO","IMAGE", "VIDEO", "OTHER");
		if($structure->subtype) {
			return $primary_mime_type[(int) $structure->type] . '/' .$structure->subtype;
		}
		return "TEXT/PLAIN";
	}

	private function get_part($stream, $uid, $mime_type, $structure = false, $part_number = false) {
		if(!$structure) {
			$structure = imap_fetchstructure($stream, $uid, FT_UID);
		}
		if($structure) {
			if($mime_type == $this->get_mime_type($structure)) {
				if(!$part_number) {
					$part_number = "1";
				}
				$text = imap_fetchbody($stream, $uid, $part_number, FT_UID);
				if($structure->encoding == 3) {
					return imap_base64($text);
				} else if($structure->encoding == 4) {
					return imap_qprint($text);
				} else {
				return $text;
			}
		}

			if($structure->type == 1) /* multipart */ {
			while(list($index, $sub_structure) = each($structure->parts)) {
				if($part_number) {
					$prefix = $part_number . '.';
				} else {
					$prefix = "";
				}
				$data = $this->get_part($stream, $uid, $mime_type, $sub_structure,$prefix . ($index + 1));
				if($data) {
					return $data;
				}
			} // END OF WHILE
			} // END OF MULTIPART
		} // END OF STRUTURE
		return false;
	} // END OF FUNCTION

	public function get_body( $stream, $uid ) {
		// GET TEXT BODY
		$dataTxt = $this->get_part($stream, $uid, "TEXT/PLAIN");

		// GET HTML BODY
		$dataHtml = $this->get_part($stream, $uid, "TEXT/HTML");

		if ($dataHtml != "") {
			$msgBody = $dataHtml;
			$mailformat = "html";
		} else {
			$msgBody = $dataTxt;
			$mailformat = "text";
		}

		return array( "mime_type" => $mailformat, "content" => $msgBody );
	}

	/** See http://www.electrictoolbox.com/extract-attachments-email-php-imap/ for reference */
	public function get_attachments( $uid, $filename = "" ) {
		$structure = imap_fetchstructure( $this->stream, $uid, FT_UID );
		$attachments = array();
		$j = 0;
		if ( isset( $structure->parts ) && count( $structure->parts ) ) { // If the message body has any 'parts'
			for( $i = 0; $i < count( $structure->parts ); $i++ ) { // For every part
				$found = false;
				if ( $structure->parts[$i]->ifdparameters || $structure->parts[$i]->subtype == "RFC822" ) { // Search for filename, in the message parameters
					if ( $structure->parts[$i]->subtype == "RFC822"  ) {
						// Search PLAIN text attachements
						for ( $p = 0; $p < count( $structure->parts[$i]->parts ); $p++ ) {
							if ( $structure->parts[$i]->parts[$p]->subtype == "PLAIN" ) {
								$attachments[$j]['filename'] = $structure->parts[$i]->description;
								$found = true;
								$j++;
							}
						}
					} else {
						foreach( $structure->parts[$i]->dparameters as $object ) {
							if ( strtolower( $object->attribute ) == 'filename') { // If found, part is an attachment
								$attachments[$j]['filename'] = $object->value;
								$found = true;
								$j++;
							}
						}
					}
				}

				if ( $structure->parts[$i]->ifparameters && $found == false ) { // Same as above
					foreach ( $structure->parts[$i]->parameters as $object ) {
						if ( strtolower( $object->attribute ) == 'name' ) {
							$attachments[$j]['filename'] = $object->value;
							$found = true;
							$j++;
						}
					}
				}

				if ( $found == true && $filename != "" && $filename == $attachments[$j - 1]['filename'] ) {
					$attachments[$j - 1]['attachment'] = imap_fetchbody( $this->stream, $uid, $j + 1, FT_UID );
					if ( $structure->parts[$i]->encoding == 3 ) { // 3 = BASE64
						$attachments[$j - 1]['attachment'] = base64_decode( $attachments[$j - 1]['attachment'] );
					} elseif ( $structure->parts[$i]->encoding == 4 ) { // 4 = QUOTED-PRINTABLE
						$attachments[$j - 1]['attachment'] = quoted_printable_decode( $attachments[$j - 1]['attachment'] );
					}
					return $attachments[$j - 1]['attachment']; // Return content
				}
				$found = true;
			}
		}
		if ( $filename == "" ) {
			return $attachments;
		} else {
			return false;
		}
	}
}
?>