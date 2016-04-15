<?php
class DATA {
	public function load() { // Load all events
		global $format;
		$accountId = $_SESSION['account_id'];
		$result = mysql_query("SELECT * FROM application_calendar WHERE account_id = '".(int)$accountId."'");
		$response = array();
		for ( $i = 0; $i < mysql_num_rows( $result ); $i++ ) {
			$response[$i] = mysql_fetch_assoc( $result );
			$response[$i]['start'] = str_replace( " ", "T", $response[$i]['start'] ); // Convert to JS date
			$response[$i]['end'] = str_replace( " ", "T", $response[$i]['end'] );
		}

		echo $format->jsonResponse( array( "evts" => $response ) );
	}

	public function update() { // Update event
		$accountId = $_SESSION['account_id'];
		$id = $_POST['id'];
		$start = $_POST['start'];
		$end = $_POST['end'];
		$notes = $_POST['notes'];
		$url = $_POST['url'];
		$rem = $_POST['rem'];
		$title = $_POST['title'];
		$loc = $_POST['loc'];
		$recur_rule = $_POST['recur_rule'];
		$ad = $_POST['ad'];
		$ad = $ad == "true" ? 1 : 0; // Normalize

		$cid = 1; // Hard coded. To be enabled later, if adding more calendars.

		mysql_query("UPDATE
				application_calendar
			SET
				cid = '".(int)$cid."'
				,account_id = '".(int)$accountId."'
				,title = '".mysql_real_escape_string( $title )."'
				,start = '".mysql_real_escape_string( $start )."'
				,end = '".mysql_real_escape_string( $end )."'
				,notes = '".mysql_real_escape_string( $notes )."'
				,url = '".mysql_real_escape_string( $url )."'
				,rem = '".(int)$rem."'
				,loc = '".mysql_real_escape_string( $loc )."'
				,ad = '".(int)$ad."'
			WHERE
				id = '".(int)$id."'
				AND account_id = '".(int)$accountId."'
			LIMIT 1
		");
	}

	public function delete() { // Delete event
		$accountId = $_SESSION['account_id'];
		$id = $_POST['id'];
		mysql_query("DELETE FROM application_calendar WHERE id = '".(int)$id."' AND account_id = '".(int)$accountId."' LIMIT 1");
	}

	public function add() { // Add a new event
		$accountId = $_SESSION['account_id'];
		$start = $_POST['start'];
		$end = $_POST['end'];
		$notes = $_POST['notes'];
		$url = $_POST['url'];
		$rem = $_POST['rem'];
		$title = $_POST['title'];
		$loc = $_POST['loc'];
		$recur_rule = $_POST['recur_rule'];
		$ad = $_POST['ad'];
		$ad = $ad == "true" ? 1 : 0; // Normalize

		$cid = 1; // Hard coded. To be enabled later, if adding more calendars.

		mysql_query("INSERT INTO
				application_calendar
			SET
				cid = '".(int)$cid."'
				,account_id = '".(int)$accountId."'
				,title = '".mysql_real_escape_string( $title )."'
				,start = '".mysql_real_escape_string( $start )."'
				,end = '".mysql_real_escape_string( $end )."'
				,notes = '".mysql_real_escape_string( $notes )."'
				,url = '".mysql_real_escape_string( $url )."'
				,rem = '".(int)$rem."'
				,loc = '".mysql_real_escape_string( $loc )."'
				,ad = '".(int)$ad."'
		");
	}
}
?>