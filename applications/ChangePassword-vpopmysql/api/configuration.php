<?php
/* Application specific configuration */

// Database connection configuration
define('VPOPMAIL_DATABASE_HOSTNAME', 'HOSTNAME'); // Vpopmail database server hostname
define('VPOPMAIL_DATABASE_USERNAME', 'USERNAME'); // Vpopmail database username
define('VPOPMAIL_DATABASE_PASSWORD', 'PASSWORD'); // Vpopmail database user password
define('VPOPMAIL_DATABASE_NAME', 'DATABASE_NAME'); // Vpopmail database name

// Allowed domain names
// NOTE: This domain names are used as table name.
// For example localhost.localdomain = localhost_localdomain table. Be careful when using, since this is not being escaped.
// See password.class.php, function changePassword() and find this string: 'Attention to this query'. The query below it should explain the possbile risks.
$allowedDomains = array(
	"domain.name.tld" => true
);
?>