<?php
require("../applications/FeedReader/api/feed.class.php"); // The current path is API, so to include Feed Reader specific files we must point to ../applications/FeedReader/api/
require("../applications/FeedReader/api/rss.class.php"); // The current path is API, so to include Feed Reader specific files we must point to ../applications/FeedReader/api/
/**
 * Provides the link between DRACMAIL and the FeedReader application.
*/
class FEEDREADER {
	/**
	 * This function is being called by the webmail backend API, after the login is checked and before the email functionality is being served.
	*/
	public function run() {
		/**
		 * Prepare application specific classes
		*/
		$this->rss = new RSS();
		/**
		 * Handle FeedReader specific requests.
		*/
		if ( isset( $_GET['application'] ) && $_GET['application'] == "feedReader" ) {
			switch ( $_GET['action'] ) {
				case "loadMenu":
					$this->rss->loadMenu();
					break;
				case "addCategory":
					$this->rss->addCategory();
					break;
				case "deleteCategory":
					$this->rss->deleteCategory();
					break;
				case "renameCategory":
					$this->rss->renameCategory();
					break;
				case "addFeed":
					$this->rss->addFeed();
					break;
				case "deleteFeed":
					$this->rss->deleteFeed();
					break;
				case "renameFeed":
					$this->rss->renameFeed();
					break;
				case "loadArticles":
					$this->rss->loadArticles();
					break;
				case "loadArticle":
					$this->rss->loadArticle();
					break;
			}

			die(); // Look no further.
		}
	}
}
?>