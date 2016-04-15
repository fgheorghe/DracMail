<?php
class RSS extends FEED {
	private function validateCategoryName( $accountId, $name ) {
		global $format;

		if ( $name == "" ) {
			echo $format->jsonResponse( array( "success" => false, "reason" => 2 ) );
			return true;
		}
		$result = mysql_query("SELECT id FROM application_feedreader_categories WHERE account_id = '".(int)$accountId."' AND name = '".mysql_real_escape_string( $name )."' LIMIT 1");
		if ( mysql_num_rows( $result ) == 1 ) {
			echo $format->jsonResponse( array( "success" => false, "reason" => 1 ) );
			return true;
		}

		return true;
	}

	private function getFeedCategoryId( $accountId, $id ) {
		$result = mysql_query("SELECT category_id FROM application_feedreader_feeds WHERE account_id = '".(int)$accountId."' AND id = '".(int)$id."' LIMIT 1");
		if ( mysql_num_rows( $result ) == 1 ) {
			$result = mysql_fetch_assoc( $result );
			return $result['category_id'];
		} else {
			return false;
		}
	}

	public function addCategory() {
		$accountId = $_SESSION['account_id'];
		$name = trim( $_GET['name'] );
		mysql_query("INSERT INTO application_feedreader_categories SET account_id = '".(int)$accountId."', name = '".mysql_real_escape_string( $name )."'");
	}

	public function deleteCategory() {
		$accountId = $_SESSION['account_id'];
		$id = $_GET['id'];
		mysql_query("DELETE FROM application_feedreader_categories WHERE account_id = '".(int)$accountId."' AND id = '".(int)$id."' LIMIT 1");
		mysql_query("DELETE FROM application_feedreader_items WHERE account_id = '".(int)$accountId."' AND feed_id IN ( SELECT id FROM application_feedreader_feeds WHERE account_id = '".(int)$accountId."' AND category_id = '".(int)$id."' )");
		mysql_query("DELETE FROM application_feedreader_feeds WHERE account_id = '".(int)$accountId."' AND category_id = '".(int)$id."'");
	}

	public function renameCategory() {
		$accountId = $_SESSION['account_id'];
		$id = $_GET['id'];
		$name = trim( $_GET['name'] );

		if ( $this->validateCategoryName( $accountId, $name ) == false ) {
			return;
		}
		mysql_query("UPDATE application_feedreader_categories SET name = '".mysql_real_escape_string( $name )."' WHERE account_id = '".(int)$accountId."' AND id = '".(int)$id."' LIMIT 1");
	}

	public function addFeed() {
		global $format;
		/**
		 * IMPORTANT: THIS APPLICATION MIGHT POSE A SECURITY ISSUE:
		 * Rogue requests coming from your server's ip address. ( E.g.: The user could use this feature to exploit other websites via HTTP requests. )
		 * Users could download big files. Even if they will not be saved, it would consume the server's bandwidth.
		*/
		$url = trim( $_GET['url'] );
		$accountId = $_SESSION['account_id'];
		$category_id = $_GET['id'];

		if ( $url == "" ) {
			echo $format->jsonResponse( array( "success" => false, "reason" => 2 ) );
			return;
		}

		$update = false;
		$result = mysql_query( "SELECT id FROM application_feedreader_feeds WHERE account_id = '".(int)$accountId."' AND feed_url = '".mysql_real_escape_string( $url )."' AND category_id = '".(int)$category_id."' LIMIT 1" );
		if ( mysql_num_rows( $result ) == 1 ) { // Feed has already been imported, meaning we want to do an update
			$feedId = mysql_fetch_assoc( $result );
			$feedId = $feedId['id']; // Use existing id
			$update = true;
		}

		$options = array(
			'http' => array(
				'user_agent' => 'dracMail webmail FeedReader' // Set a user agent.
				,'method' => 'GET'
				,'request_fulluri' => true
				,'max_redirects' => 3
				,'timeout' => 1
			)
		);

		$context = stream_context_create( $options );
		libxml_set_streams_context( $context );

		$feedContent = $this->handle( "http://".$url );

		if ( $update === false ) {
			// Create a new feed:
			mysql_query("INSERT INTO
					application_feedreader_feeds
				SET
					account_id = '".(int)$accountId."'
					,category_id = '".(int)$category_id."'
					,title = '".mysql_real_escape_string( $this->title )."'
					,link = '".mysql_real_escape_string( $this->link )."'
					,description = '".mysql_real_escape_string( $this->description )."'
					,image = '".mysql_real_escape_string( $this->image )."'
					,url = '".mysql_real_escape_string( $this->url )."'
					,feed_url = '".mysql_real_escape_string( $url )."'
			");

			// Fetch it's id
			$feedId = mysql_insert_id();
		}

		// Save feed items:
		for ( $i = 0; $i < count( $this->items );$i++ ) {
			// Validate item:
			// - at least a non empty title
			// - and at least a non empty link
			if ( $this->items[$i]->title != "" && $this->items[$i]->link != "" ) {
				// Check if the items has already been imported:
				// - there is an identical link for the same user and part of the same feed
				$result = mysql_query("SELECT
						id
					FROM
						application_feedreader_items
					WHERE
						account_id = '".(int)$accountId."'
						AND feed_id = '".(int)$feedId."'
						AND link = '".mysql_real_escape_string( $this->items[$i]->link )."'
					LIMIT 1
				");

				if ( mysql_num_rows( $result ) == 0 ) {
					mysql_query("INSERT INTO
							application_feedreader_items
						SET
							account_id = '".(int)$accountId."'
							,feed_id = '".(int)$feedId."'
							,title = '".mysql_real_escape_string( $this->items[$i]->title )."'
							,link = '".mysql_real_escape_string( $this->items[$i]->link )."'
							,description = '".mysql_real_escape_string( $this->items[$i]->description )."'
							,image = '".mysql_real_escape_string( $this->items[$i]->image )."'
							,pubDate = '".mysql_real_escape_string( date("Y-m-d H:i:s", strtotime( $this->items[$i]->pubDate ) ) )."'
							,guid = '".mysql_real_escape_string( $this->items[$i]->guid )."'
					");
				}
			}
		}
	}

	public function deleteFeed() {
		$accountId = $_SESSION['account_id'];
		$id = $_GET['id'];

		mysql_query( "DELETE FROM application_feedreader_feeds WHERE account_id = '".(int)$accountId."' AND id = '".(int)$id."' LIMIT 1" );
		mysql_query( "DELETE FROM application_feedreader_items WHERE account_id = '".(int)$accountId."' AND feed_id = '".(int)$id."'" );

	}

	public function renameFeed() {
		global $format;
		$accountId = $_SESSION['account_id'];
		$id = $_GET['id'];
		$title = trim( $_GET['name'] );

		if ( $title == "" ) {
			echo $format->jsonResponse( array( "success" => false, "reason" => 2 ) );
			return;
		}

		$category_id = $this->getFeedCategoryId( $accountId, $id );
		if ( $category_id === false ) {
			echo $format->jsonResponse( array( "success" => false, "reason" => 3 ) );
			return;
		}
		$result = mysql_query( "SELECT id FROM application_feedreader_feeds WHERE account_id = '".(int)$accountId."' AND title = '".mysql_real_escape_string( $title )."' AND category_id = '".(int)$category_id."' LIMIT 1" );
		if ( mysql_num_rows( $result ) == 1 ) {
			echo $format->jsonResponse( array( "success" => false, "reason" => 1 ) );
			return;
		}

		mysql_query("UPDATE application_feedreader_feeds SET title = '".mysql_real_escape_string( $title )."' WHERE id = '".(int)$id."' AND account_id = '".(int)$accountId."' LIMIT 1");
	}

	private function loadCategoryFeeds( $accountId, $category_id ) {
		$result = mysql_query("SELECT id, title, feed_url FROM application_feedreader_feeds WHERE account_id = '".(int)$accountId."' AND category_id = '".(int)$category_id."'");
		$response = array();
		for ( $i = 0; $i < mysql_num_rows( $result ); $i++ ) {
			$row = mysql_fetch_assoc( $result );
			$response[$i]['feed_id'] = $row['id'];
			$response[$i]['text'] = $row['title'];
			$response[$i]['leaf'] = true;
			$response[$i]['type'] = "feed";
			$response[$i]['feed_url'] = $row['feed_url'];
		}

		return $response;
	}

	public function loadMenu() {
		global $format;
		$accountId = $_SESSION['account_id'];
		$result = mysql_query("SELECT
				id
				,name
				,(SELECT COUNT(id) FROM application_feedreader_feeds WHERE application_feedreader_feeds.category_id = application_feedreader_categories.id AND application_feedreader_feeds.account_id = application_feedreader_categories.account_id LIMIT 1 ) AS feeds
			FROM
				application_feedreader_categories
			WHERE
				account_id = '".(int)$accountId."'");
		$response = array();
		for ( $i = 0; $i < mysql_num_rows( $result ); $i++ ) {
			$row = mysql_fetch_assoc( $result );
			$response[$i]['id'] = $row['id'];
			$response[$i]['text'] = $row['name'];
			$response[$i]['leaf'] = $row['feeds'] == 0 ? true : false;
			$response[$i]['type'] = "category";
			if ( $row['feeds'] != 0 ) {
				$response[$i]['children'] = $this->loadCategoryFeeds( $accountId, $row['id'] );
				$response[$i]['expanded'] = true;
			}
		}

		echo $format->jsonResponse( $response );
	}

	public function loadArticles() {
		global $format;
		$accountId = $_SESSION['account_id'];

		$itemId = "";
		$search = "";
		$itemType = "";
		if ( isset( $_POST['itemId'] ) && isset( $_POST['search'] ) && isset( $_POST['itemType'] ) ) {
			$itemId = $_POST['itemId'];
			$search = $_POST['search'];
			$itemType = $_POST['itemType'];
		}

		$result = mysql_query("SELECT
				application_feedreader_items.title
				,application_feedreader_items.link
				,application_feedreader_items.id
				,application_feedreader_items.image
				,application_feedreader_items.description
				,application_feedreader_feeds.category_id
				,pubDate AS date
				,application_feedreader_feeds.title AS feed
			FROM
				application_feedreader_items
			LEFT JOIN
				application_feedreader_feeds
			ON
				application_feedreader_items.feed_id = application_feedreader_feeds.id
				AND application_feedreader_items.account_id = application_feedreader_feeds.account_id
			WHERE
				application_feedreader_items.account_id = '".(int)$accountId."'
				".( $itemId != "" ? " AND ".( $itemType == "1" ? "application_feedreader_feeds.category_id" : "feed_id" )." = '".(int)$itemId."'" : "" )."
				".( $search != "" ? " AND ( application_feedreader_items.title LIKE '%".mysql_real_escape_string( $search )."%' OR application_feedreader_items.description LIKE '%".mysql_real_escape_string( $search )."%' ) " : "" )."
			ORDER BY
				application_feedreader_items.pubDate DESC
		");

		$response = array();
		for ( $i = 0; $i < mysql_num_rows( $result ); $i++ ) {
			$row = mysql_fetch_assoc( $result );
			$response[$i]['title'] = $row['title'];
			$response[$i]['date'] = $row['date'];
			$response[$i]['feed'] = $row['feed'];
			$response[$i]['id'] = $row['id'];
			$response[$i]['category_id'] = $row['category_id'];
			$response[$i]['link'] = $row['link'];
			$response[$i]['image'] = $row['image'];
			$response[$i]['description'] = strip_tags( $row['description'] );
		}

		echo $format->jsonResponse( array( "articles" => $response ) );
	}
}
?>