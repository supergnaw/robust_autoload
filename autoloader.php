<?php
	// directory of php classes; if multiple, point to single parent directory
	define( 'CLASSES_DIRECTORY', dirname( __FILE__ ));

	// show debug shenanigans
	define( 'AUTOLOAD_DEBUG', false );

	// define autoloader function
	function robust_autoload( $class, $dir = null ) {
		// start in the current directory
		$dir = ( !is_null( $dir )) ? $dir : CLASSES_DIRECTORY;

		// debug
		if( true === AUTOLOAD_DEBUG ) echo "<h2>Searching for <code>\"{$class}\"<code> in {$dir}</h2>\n";
		if( true === AUTOLOAD_DEBUG ) var_dump( scandir( $dir, SCANDIR_SORT_NONE ));

		// loop through directories
		foreach( scandir( $dir, SCANDIR_SORT_NONE ) as $file ) {
			// if is not directory, search files to see if it's the requested class
			$fullPath = $dir . DIRECTORY_SEPARATOR . $file;

			if( true === AUTOLOAD_DEBUG ) echo "<b>Scanning:</b>\t{$fullPath}<br>\n";

			if( !is_dir ( $fullPath )) {
				// if is a php file, check to see if it contains the requested class
				if( 'php' == pathinfo( $fullPath, PATHINFO_EXTENSION )) {
					// https://stackoverflow.com/questions/9059026/php-check-if-file-contains-a-string
					$regex = "/^(\s+)?class\s+{$class}(\s+extends\s+\w+)?(\s+\{)?(\s+)?$/m";
					if( preg_match( $regex, file_get_contents( $fullPath ), $matches ) === 1 ) {
						// include the class file
						require_once( $fullPath );

						if( true === AUTOLOAD_DEBUG ) {
							echo "<br><b>Match Found:</b> <code>{$fullPath}</code><br>\n<pre>";
							var_dump( $matches );
							echo "</pre><br><h3><span style='color: #009900'>Found <b>{$class}</b> in <b>{$dir}</b></span></h3><hr>";
						}

						// clear memory because it's 2021 and we still care apparently
						unset( $class );
						unset( $dir );
						unset( $file );
						unset( $fullPath );
						unset( $matches );

						// break
						return true;
					}
				} else {
					if( true === AUTOLOAD_DEBUG ) echo "------------- file ignored: {$fullPath}<br>\n";
				}
			}
			// if is directory, recursively search it now
			if( is_dir( $fullPath ) && '.' !== substr( $file, 0, 1 )) {
				if( true === AUTOLOAD_DEBUG ) echo "Opening subdirectory: {$fullPath}<hr>";
				if( true === robust_autoload( $class, $fullPath )) {
					// recursive breaks on success
					return true;
				}
			}
		}

		// failed to find class
		if( true === AUTOLOAD_DEBUG ) {
			die( "<hr><h3 style='color: #990000;'>Failed to find class <b>{$class}</b> in directory: " . CLASSES_DIRECTORY . "</h3>" );
		} else {
			die( "<h3 style='color: #990000;'>Failed to find class <b><code>{$class}</code></b></h3>" );
		}
	}

	// Register autoloader function
	spl_autoload_register( 'robust_autoload' );
