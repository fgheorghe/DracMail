<?php
require("api/classes/application.class.php");
$application = new APPLICATION( "index" ); // Load the Application API.
?>
<html>
<head>
	<title>dracMail - webmail</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" type="text/css" href="ext-3.3.0/resources/css/ext-all-notheme.css" />
	<link rel="stylesheet" type="text/css" href="ext-3.3.0/examples/ux/fileuploadfield/css/fileuploadfield.css" />
	<!-- Theme includes -->
	<link rel="stylesheet" type="text/css" title="blue" href="ext-3.3.0/resources/css/xtheme-blue.css" />
	<link type="text/css" rel="stylesheet" href="styles.css"/>
	<link type="text/css" rel="stylesheet" href="menu.css"/>
	<!-- Application CSS -->
	<?php echo $application->CSS(); ?>
	<!-- End of application CSS files -->
	<script type="text/javascript" src="ext-3.3.0/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="ext-3.3.0/ext-all-debug.js"></script>
	<script type="text/javascript" src="ext-3.3.0/examples/ux/statusbar/StatusBar.js"> </script>
	<script type="text/javascript" src="ext-3.3.0/examples/ux/fileuploadfield/FileUploadField.js"> </script>
	<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="ext-3.3.0/Ext.ux.TinyMCE.js"></script>
	<script type="text/javascript" src="objLoginWindow.js"></script>
	<script type="text/javascript" src="objMainUI.js"></script>
	<script type="text/javascript" src="objMailFolders.js"></script>
	<script type="text/javascript" src="objFolderContent.js"></script>
	<script type="text/javascript" src="fnAjax.js"></script>
	<script type="text/javascript" src="objLoad.js"></script>
	<script type="text/javascript" src="fnGeneric.js"></script>
	<script type="text/javascript" src="objSynch.js"></script>
	<script type="text/javascript" src="fnCheckMail.js"></script>
	<script type="text/javascript" src="objUIMenu.js"></script>
	<script type="text/javascript" src="objComposer.js"></script>
	<script type="text/javascript" src="fnXSRF.js"></script>
	<script type="text/javascript" src="objGeneric.js"></script>
	<script type="text/javascript" src="objApplicationMenu.js"></script>
	<script>
		var applications = {}; // Holder for application registration.
	</script>
	<!-- Application JavaScript files -->
	<?php echo $application->JavaScript(); ?>
	<!-- End of JavaScript files -->
	<script>
		// Prepare global variables
		var mailFolders;
		var mainUI;
		var UIMenu;
		var loadUI;
		var themeTitle;
		var mailAddress;
		var applicationMenu;
		var applicationMenuItems = []; // To be populated by the application mechanism
		// Load TinyMCE
		Ext.ux.TinyMCE.initTinyMCE();

		// Load the UI
		Ext.onReady(function(){
			loadUI = new objLoad();
			loadUI.init();
		});
	</script>
</head>
<body onContextMenu="return false;">
</body>
</html>
