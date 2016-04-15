<?php
class MYSQL {
	/**
	 * Start a database connection.
	*/
	function __construct( $hostname, $user, $password, $database ) {
		$this->connection = mysql_connect( $hostname, $user, $password );
		mysql_select_db( $database );
	}
}
?>