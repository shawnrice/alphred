<?php
/**
 * These functions handle Alphred when it is run as a command line tool rather than
 * included as a library.
 *
 * There is no documentation of the PHP internals of these.
 */


/**
 * Readline command to get confirmation to create scripts
 */
function alphred_confirm_create_server_scripts() {
	// Possible answers
	$answers = ['y', 'yes', 'n', 'no'];
	// Ask for confirmation
	$line = readline( "Do you want to create the server scripts to run this workflow from a CLI Server SAPI? (Y/n): " );
	if ( empty( $line ) ) {
		// Default is 'Yes'
		$line = 'Y';
	}
	// Make sure that the answer is in the array
	if ( in_array( strtolower( $line ), $answers ) ) {
		// It is, so return the answer
		return $line;
	}
	// The answer wasn't in the array, so ask again
	return alphred_confirm_create_server_scripts();
}

/**
 * Processes the readline command above
 */
function alphred_confirm_create_server_scripts_path() {
	$answers = [ 'y', 'yes', 'n', 'no' ];
	$path = $_SERVER['PWD'];
	$line = readline( "Files will be created at {$path}. Continue? (Y/n): " );
	if ( empty( $line ) ) {
		$line = 'Y';
	}
	if ( in_array( strtolower( $line ), $answers ) ) {
		return $line;
	} else {
		return alphred_confirm_create_server_scripts_location();
	}
}


/**
 * Updates Alphred.phar to the latest development snapshot from the Master branch of the GH repo
 */
function update_alphred_from_master() {
	// URL for Alphred.phar at the master branch
	$url = 'https://github.com/shawnrice/alphred/raw/master/build/Alphred.phar';
	// Download the copy with a different name
	file_put_contents( './alphred-tmp.phar', file_get_contents( $url ) );
	// Test it to make sure that it works
	$test = exec( 'php alphred-tmp.phar -h', $output, $return );
	// If the return code was 0 (success), then it works, so replace the current phar with this one
	if ( 0 === $return ) {
		// Get the filepath to the phar
		$me = Phar::running( false );
		// Make sure that the file exists
		if ( file_exists( $me ) ) {
			// Delete myself
			unlink( $me );
			// Rename the tmp to me.
			rename( 'alphred-tmp.phar', $me );
		} else {
			// This is an existential moment. Or, really, a non-existential moment.
			print "Error: I do not exist. That's really strange.\n";
		}
	} else {
		// The download was bad
		print 'Error: downloaded phar is bad.';
		// Delete the bad download
		unlink( 'alphred-tmp.phar' );
	}
}


/**
 * Prints the help message
 */
function print_alphred_help( $commands ) {
	// Get the help command from the embedded text file
	$text = str_replace( 'ALPHRED_VERSION', ALPHRED_VERSION, file_get_contents( __DIR__ . '/../commands/help.txt' ) );
	// Update the copyright year
	$text = str_replace( 'ALPHRED_COPYRIGHT', ALPHRED_COPYRIGHT, $text );

	// Print the text of the help
	print $text;
	print "---------------------------\n";
	print "Commands:\n";
	foreach ( $commands as $command => $help ) :
		print "* {$command}:\n\t{$help}\n";
	endforeach;
}

/**
 * Extracts Alphred into its base components
 */
function extract_alphred() {
	$me   = Phar::running( false );
	$phar = new Phar( $me );
	if ( ! file_exists( 'Alphred' ) ) {
		mkdir( 'Alphred', 0775 );
	}
	$phar->extractTo( './Alphred' );
	$basedir = realpath( '.' );
	print "Extracted Alphred to `{$basedir}/Alphred/`.\n";
}
