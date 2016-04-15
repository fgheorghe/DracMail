<?php
/* See: http://cyber.law.harvard.edu/rss/rss.html */
class FEED {
	public $title = "";
	public $link = "";
	public $description = "";
	public $copyright = "";
	public $language = "";
	public $image = "";
	public $url = "";
	public $ttl = "";
	public $items = array();

	private $item = false;

	function handleMediaTag( $type, $tag ) {
		switch ( $type ) {
			case "content":
				if ( $tag->hasAttribute("medium") && $tag->hasAttribute("medium") == "image" ) {
					if ( $tag->hasAttribute( "url" ) ) {
						$this->items[count( $this->items ) - 1]->image = $tag->getAttribute( "url" );
					}
				}
				break;
			case "text":
				// Not implemented.
				break;
			case "credit":
				// Not implemented.
				break;
		}
	}

	function handleTag( $tag ) {
		if ( $tag->tagName == "item" ) {
			$this->item = true;
			$this->items[] = new stdClass;

			// Prepare class stub
			$this->items[count( $this->items ) - 1]->title = "";
			$this->items[count( $this->items ) - 1]->link = "";
			$this->items[count( $this->items ) - 1]->description = "";
			$this->items[count( $this->items ) - 1]->image = "";
			$this->items[count( $this->items ) - 1]->pubDate = "";
			$this->items[count( $this->items ) - 1]->guid = "";
			return; // Keep going...
		}

		if ( $this->item == false ) {
			switch ( $tag->tagName ) {
				case "title":
					$this->title = $tag->nodeValue;
					break;
				case "link":
					$this->link = $tag->nodeValue;
					break;
				case "description":
					$this->description = $tag->nodeValue;
					break;
				case "copyright":
					$this->copyright = $tag->nodeValue;
					break;
				case "language":
					$this->language = $tag->nodeValue;
					break;
				case "image":
					$this->image = $tag->nodeValue;
					break;
				case "url":
					$this->url = $tag->nodeValue;
					break;
				case "ttl":
					$this->ttl = $tag->nodeValue;
					break;
			}
		} else {
			switch ( $tag->tagName ) {
				case "title":
					$this->items[count($this->items) - 1]->title = $tag->nodeValue;
					break;
				case "description":
					$this->items[count($this->items) - 1]->description = $tag->nodeValue;
					break;
				case "link":
					if ( $this->items[count($this->items) - 1]->link == "" ) { // Check if the link was already set by the guid tag, via isPermaLink=true attribute
						$this->items[count($this->items) - 1]->link = $tag->nodeValue;
					}
					break;
				case "pubDate":
					$this->items[count($this->items) - 1]->pubDate = $tag->nodeValue;
					break;
				case "category":
					$this->items[count($this->items) - 1]->category = $tag->nodeValue;
					break;
				case "guid":
					if ( $tag->hasAttribute("isPermaLink") && $tag->hasAttribute("isPermaLink") == "true" ) {
						$this->items[count($this->items) - 1]->link = $tag->nodeValue;
					}
					$this->items[count($this->items) - 1]->guid = $tag->nodeValue;
					break;
				case "media:content":
					$this->handleMediaTag( "content", $tag );
					break;
				case "media:credit":
					$this->handleMediaTag( "credit", $tag );
					break;
				case "media:text":
					$this->handleMediaTag( "text", $tag );
					break;
			}
		}
	}

	function getChildren( $node ) {
		if ( $node->hasChildNodes() ) {
			$nodes = array();
			foreach ( $node->childNodes as $childNode ) {
				$nodes[] = $childNode;
			}
			for ( $i = 0; $i < count( $nodes ); $i++ ) {
				$childNode = $nodes[$i];
				if ( isset( $childNode->tagName ) ) { // If node is not a tag, ignore
					$this->handleTag( $childNode );
					$this->getChildren( $childNode ); // Iterate
				}

			}
		} else {
			// Get all attributes
			if ( isset( $childNode->tagName ) ) { // If node is not a tag, ignore
				$this->handleTag( $childNode );
			}
		}
	}

	function handle( $url ) {
		$feed = new DOMDocument();
		$feed->load( $url );

		// Loop through all tags
		$root = $feed->getElementsByTagName( "rss" );

		if ( isset( $root->length ) ) {
			for ( $i = 0; $i < $root->length; $i++ ) {
				$this->getChildren( $root->item( $i ) ); // Get all nodes
			}
		} else {
			$this->getChildren( $root ); // Get all nodes
		}
	}
}
?>