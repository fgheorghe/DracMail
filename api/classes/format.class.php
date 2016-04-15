<?php
/**
 * Generic format functions
*/
class FORMAT {
	/**
	 * Function for formatting a valid JSON ( or JS ) response. See code for more details.
	 * @param string
	 *	String value to format
	 * @return
	 *	String formatted string
	*/
	function jsonFormat( $string ) {
		$string = htmlspecialchars( $string ); // Handle special chars
		$string = str_replace( "\\", "\\\\", $string ); // Handle \
		$string = str_replace( "\n", "\\n", $string ); // Handle new lines
		$string = str_replace( "\r", "", $string );
		$string = str_replace( "'", "\\'", $string ); // Handle single quotes
		return $string;
	}

	/** Helper for jsonResponse */
	private function numericKeys( $array ) {
		$keys = array_keys( $array );
		for ( $i = 0; $i < count( $keys ); $i++ ) {
			if ( !is_numeric( $keys[$i] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Function for building a JSON response.
	 * @param entity
	 *	Mixed value to be JSON formatted. Can be either object or array.
	 * @return
	 *	String JSON 'object' (response)
	*/
	function jsonResponse( $entity, $recursive = false ) {
		if ( is_object( $entity ) ) { // Convert object to array.
			$entity = get_object_vars( $entity );
		}
		$numericKeys = $this->numericKeys( $entity );
		if ( $recursive == false && !$numericKeys ) {
			$output = "{\n"; // Begin object.
		} else {
			$output = "[\n"; // Begin array.
		}
		$count = 0;
		foreach ( $entity as $property => $value ) { // Iterate all array keys
			if ( $count != 0 ) {
				$output .= ",";
			}
			if ( is_numeric( $property ) && !$numericKeys ) {
				$output .= $this->jsonFormat( $property ).": "; // Insert key
			} elseif ( is_string( $property ) ) {
				$output .= "'".$this->jsonFormat( $property )."': "; // Insert property
			}
			// Insert value, which might be a string, integer or array
			if ( is_numeric( $value ) ) { // Value is integer
				$output .= $value."\n";
			} elseif ( is_array( $value ) || is_object( $value ) ) { // Value is either array or object, iterate.
				$output .= $this->jsonResponse( $value );
			} elseif ( is_string( $value ) ) { // Value is string
				$output .= "'".( $this->jsonFormat( $value ) )."'\n";
			} elseif ( is_bool( $value ) ) { // Value is boolean
				$output .= ( $value == true ? "true" : "false" )."\n";
			} else { // Value type is unknown.
				$output .= "''\n";
			}
			$count++;
		}
		if ( $recursive == false && !$numericKeys ) {
			$output .= "}\n"; // End object.
		} else {
			$output .= "]\n"; // End array.
		}
		return $output;
	}

	public function formatJs( $string ) {
		return nl2br( str_replace( "'", "\\'", str_replace( "\\", "\\\\", htmlspecialchars( $string ) ) ) );
	}

	/** Internal function */
	private function makeUrl( $content ) {
		return preg_replace( '/((ht|f)tps?:\/\/([\w\.]+\.)?[\w-]+(\.[a-zA-Z]{2,4})?[^\s\r\n\(\)"\'<>\,\!]+)/si',"<a target=\"_blank\" href=\"\\0\">\\0</a>", $content );
	}

	/**
	 * Format a TEXT message.
	 * @param content
	 *	String content
	 * @return
	 *	String 'pretty' output
	*/
	function formatTextMessage( $content ) {
		$content = $this->utf8encode( $content );
		$content = htmlspecialchars( $content );
		$content = $this->makeUrl( $content ); // Format links
		$content = nl2br( $content );

		return $content;
	}

	private function returnAllowedAttribute( $attributeName ) {
		$allowed = array( // TODO: Compile a list of allowed attributes
			"class" => true
			,"href" => true
			,"cellspacing" => true
			,"cellpadding" => true
			,"border" => true
			,"width" => true
			,"style" => true
			,"color" => true
			,"align" => true
			,"size" => true
			,"bgcolor" => true
			,"height" => true
			,"alt" => true
			,"colspan" => true
			,"valign" => true
			,"background" => true
			,"target" => true
		);

		if ( !isset( $allowed[$attributeName] ) ) {
			$allowed[$attributeName] = false;
		}

		return $allowed[$attributeName];
	}

	private function makeHref( $type, &$node, $attribute ) {
		$this->cleanAttributes( $type, $node, $attribute );
		$node->removeAttribute( "target" );
		$node->setAttribute( "target", "_blank" );
		if ( $node->hasAttribute( "href" ) ) {
			$link = $node->getAttribute("href");
			$link = str_replace( "javascript:", "", $link ); // Way too paranoid, this should be done properly.
			$node->setAttribute( "href", $link );
		}
	}
	
	private function makeImg( $type, &$node, $attribute ) {
		$this->cleanAttributes( $type, $node, $attribute );
		$node->setAttribute( "src", "../ico/blank.png" );
	}

	private function cleanAttributes( $type, &$node, $attribute ) {
		if ( !$this->returnAllowedAttribute( $attribute->name ) ) {
			$node->removeAttribute( $attribute->name );
		}
	}

	private function returnAllowedTags( &$node ) {
		$allowed = array(
			"a" => true
			,"abbr" => true
			,"acronym" => true
			,"address" => true
			,"area" => true
			,"b" => true
			,"base" => true
			,"basefont" => true
			,"bdo" => true
			,"big" => true
			,"blockquote" => true
			,"body" => true
			,"br" => true
			,"button" => true
			,"caption" => true
			,"center" => true
			,"cite" => true
			,"code" => true
			,"col" => true
			,"colgroup" => true
			,"dd" => true
			,"del" => true
			,"dfn" => true
			,"dir" => true
			,"div" => true
			,"dl" => true
			,"dt" => true
			,"em" => true
			,"fieldset" => true
			,"font" => true
			,"form" => true
			,"h1" => true
			,"h2" => true
			,"h3" => true
			,"h4" => true
			,"h5" => true
			,"h6" => true
			,"head" => true
			,"hr" => true
			,"html" => true
			,"i" => true
			,"img" => true
			,"input" => true
			,"ins" => true
			,"isindex" => true
			,"kbd" => true
			,"label" => true
			,"legend" => true
			,"li" => true
			,"link" => true
			,"map" => true
			,"menu" => true
			,"meta" => true
			,"noframes" => true
			,"noscript" => true
			,"ol" => true
			,"optgroup" => true
			,"option" => true
			,"p" => true
			,"param" => true
			,"pre" => true
			,"q" => true
			,"s" => true
			,"samp" => true
			,"select" => true
			,"small" => true
			,"span" => true
			,"strike" => true
			,"strong" => true
			,"style" => true
			,"sub" => true
			,"sup" => true
			,"table" => true
			,"tbody" => true
			,"td" => true
			,"textarea" => true
			,"tfoot" => true
			,"th" => true
			,"thead" => true
			,"title" => true
			,"tr" => true
			,"tt" => true
			,"u" => true
			,"ul" => true
			,"var" => true
			,"xmp" => true
		);
		if ( !$allowed[$node->tagName] ) {
			$node->parentNode->removeChild( $node );
			return false;
		}
		return true;
	}

	private function getAllAttributes( &$node ) {
		if ( $node->hasAttributes() ) {
			$attributes = $node->attributes;
			if( !is_null( $attributes ) ) {
				foreach ( $attributes as $key => $value ) { // Get attributes
					$values[] = $value;
				}
				for ( $i = 0; $i < count( $values ); $i++ ) { // Handle each attribute, for each type of tag
					switch ( $node->tagName ) {
						case "a":
							$this->makeHref( $node->tagName, $node, $values[$i] );
							break;
						case "img":
							$this->makeImg( $node->tagName, $node, $values[$i] );
							break;
						default:
							$this->cleanAttributes( $node->tagName, $node, $values[$i] );
							break;
					}
				}
			}
		}
	}

	private function getChildren( &$node ) {
		if ( $node->hasChildNodes() ) {
			$nodes = array();
			foreach ( $node->childNodes as $childNode ) {
				$nodes[] = $childNode;
			}
			for ( $i = 0; $i < count( $nodes ); $i++ ) {
				$childNode = $nodes[$i];
				if ( isset( $childNode->tagName ) && $this->returnAllowedTags( $childNode ) ) { // If node is not a tag or not allowed, ignore
					$this->getAllAttributes( $childNode ); // Get all attributes
					$this->getChildren( $childNode ); // Iterate
				}

			}
		} else {
			// Get all attributes
			if ( isset( $childNode->tagName ) && $this->returnAllowedTags( $childNode ) ) { // If node is not a tag or not allowed, ignore
				$this->getAllAttributes( $node );
			}
		}
	}

	private function getAllNodes( &$rootNode ) {
		if ( isset( $rootNode->length ) ) {
			for ( $i = 0; $i < $rootNode->length; $i++ ) {
				$this->getChildren( $rootNode->item( $i ) ); // Get all nodes
			}
		} else {
			$this->getChildren( $rootNode ); // Get all nodes
		}
	}

	/**
	 * Format an HTML message.
	 * @param content
	 *	String content
	 * @return
	 *	String 'pretty' output
	*/
	function formatHtmlMessage( $content ) {
		// Tidy up the code
		$tidy = new tidy;
		$tidy->parseString( $content, array('encoding' => mb_detect_encoding( $string ) ), 'utf8' );

		$tidy->cleanRepair();
		$content = $tidy->html()->value;

		// Remove all unwanted attributes, tags and so on.
		$dom = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $content );

		$root = $dom->getElementsByTagName( "html" );
		$this->getAllNodes( $root );
		$content = $dom->saveHTML();

		return $content;
	}

	/**
	 * Function for decoding subject or sender fields.
	 * @param subject
	 *	String to be decoded
	 * @return 
	 *	Decoded string
	*/
	function decodeSubject( $subject ) {
		$values = imap_mime_header_decode( $subject );
		$subject = "";
		for ( $i = 0; $i < count( $values ); $i++ ) {
			$subject .= $values[$i]->text;
		}
		return $subject;
	}

	/**
	 * Function for encoding any to UTF8
	 * @param string
	 *	String string to be encoded
	 * @return
	 *	Encoded string
	*/
	function utf8encode( $string ) {
		if ( !mb_check_encoding($string, 'UTF-8') ) {
			$encoding = mb_detect_encoding( $string );
			$target = "UTF-8";
			if ( $encoding == "" ) {
				$encoding = "ISO-8859-1";
			}
			$string = mb_convert_encoding( $string, $encoding, $target );
		}

		return $string;
	}
}
?>