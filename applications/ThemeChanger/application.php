<?php
$application = new stdClass();
$application->enabled = false;
$application->name = "Theme Changer";
$application->javaScriptFiles = array(
	"js/fnStyleSwitcher.js"
	,"js/objThemes.js"
	,"js/themeChanger.js"
	,"js/application.js"
);
$application->cssFiles = array(
	array(
		"title" => "honey"
		,"file" => "css/Honey/css/xtheme-honey.css"
	)
	,array(
		"title" => "snake"
		,"file" => "css/Snake/css/xtheme-snake.css"
	)
	,array(
		"title" => "grey2"
		,"file" => "css/Light Grey/css/xtheme-grey2.css"
	)
	,array(
		"title" => "grey3"
		,"file" => "css/Dark Grey/css/xtheme-grey3.css"
	)
);
?>