<?php
class APPLICATION {
	public $jsFiles = array();
	public $cssFiles = array();
	public $applications = array();

	private function getCSSFiles( $application, $folder ) {
		if ( isset( $application->cssFiles ) && is_array( $application->cssFiles ) ) {
			$this->cssFiles[$application->name] = array();
			for ( $i = 0; $i < count( $application->cssFiles ); $i++ ) {
				$application->cssFiles[$i]["file"] = $folder ."/". $application->cssFiles[$i]["file"];
				array_push( $this->cssFiles[$application->name], $application->cssFiles[$i] );
			}
		}
	}

	private function getJavaScriptFiles( $application, $folder ) {
		if ( isset( $application->javaScriptFiles ) && is_array( $application->javaScriptFiles ) ) {
			$this->jsFiles[$application->name] = array();
			for ( $i = 0; $i < count( $application->javaScriptFiles ); $i++ ) {
				array_push( $this->jsFiles[$application->name], $folder."/".$application->javaScriptFiles[$i] );
			}
		}
	}

	private function scan( $path ) {
		// Open application directory, if possible, else ignore.
		if ( $resource = opendir( $path ) ) {
			while ( ( $name = readdir( $resource ) ) !== false) {
				if ( filetype( $path . $name ) == "dir" && $name != "." && $name != ".." ) {
					if ( file_exists( $path . $name . "/application.php" ) ) {
						require( $path . $name . "/application.php" ); // Load the file
						if ( $application->enabled == true ) {
							if ( $this->uiFiles == true ) {
								$this->getJavaScriptFiles( $application, $name );
								$this->getCSSFiles( $application, $name );
							} else {
								if ( isset( $application->launcher ) && file_exists( $path . $name . "/" . $application->launcher ) ) {
									array_push( $this->applications, array(
										"file" => $path . $name . "/" . $application->launcher
									) );
									if ( isset( $application->className ) ) {
										$this->applications[count($this->applications) - 1]["className"] = $application->className;
									}
								}
							}
						}
					}
				}
			}
			closedir($resource);
		}
	}
	/**
	 * Scan for applications.
	 * @param location
	 *	String location where the application mechanism is being called from. Allowed values: "api" (default) and "index"
	*/
	function __construct( $location = "api" ) { // Scan for applications
		$path = "";
		switch ( $location ) {
			case "index":
				$path = "applications/";
				$this->uiFiles = true;
				break;
			case "api":
				$path = "../applications/";
				$this->uiFiles = false;
				break;
		}
		$this->scan( $path );
	}

	function CSS() { // Dump application CSS files
		$result = "";
		foreach ( $this->cssFiles as $name => $files ) {
			$result .= "<!-- Application: ".htmlspecialchars( $name )." -->\n";
			for ( $i = 0; $i < count( $files ); $i++ ) {
				$title = "";
				if ( isset( $files[$i]["title"] ) ) {
					$title = "title=\"".htmlspecialchars( $files[$i]["title"] )."\"";
				}
				$result .= "<link rel=\"stylesheet\" type=\"text/css\" ".$title." href=\"applications/".$files[$i]["file"]."\" />\n";
			}
		}

		return $result;
	}

	function JavaScript() { // Dump application JavaScript files
		$result = "";
		foreach ( $this->jsFiles as $name => $files ) {
			$result .= "<!-- Application: ".htmlspecialchars( $name )." -->\n";
			for ( $i = 0; $i < count( $files ); $i++ ) {
				$result .= "<script type=\"text/javascript\" src=\"applications/".$files[$i]."\"></script>\n";
			}
		}

		return $result;
	}
}
?>