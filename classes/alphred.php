<?php
/**
 * Entry point for the library
 *
 * `Alphred.phar` can be used in two main ways...
 *
 * PHP version 5
 *
 *
 * @package    Alphred
 * @copyright  Shawn Patrick Rice 2014
 * @license    http://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @author     Shawn Patrick Rice <rice@shawnrice.org>
 * @link       http://www.github.com/shawnrice/alphred
 * @link       http://shawnrice.github.io/alphred
 * @since      File available since Release 1.0.0
 *
 */

// Set the version of the library as a constant
define( 'ALPHRED_VERSION',   '0.1.0' );

// Define the log level if not already defined
if ( ! defined( 'ALPHRED_LOG_LEVEL' ) ) {
	define( 'ALPHRED_LOG_LEVEL', 2 );
}

ALPHRED_PARSE_INI();

// Check if Alphred.phar was included or run. Behavior differs based on that
if ( ! ( isset( $argv ) && ( 'Alphred.phar' === basename( $argv[0] ) || 'Alphred.php' === basename( $argv[0] ) ) ) ) {
	// Alphred was included and not run directly
	require_once( __DIR__ . '/Alfred.php' );
	require_once( __DIR__ . '/AppleScript.php' );
	require_once( __DIR__ . '/Config.php' );
	require_once( __DIR__ . '/Database.php' );
	require_once( __DIR__ . '/Date.php' );
	require_once( __DIR__ . '/Exceptions.php' );
	require_once( __DIR__ . '/Globals.php' );
	require_once( __DIR__ . '/i18n.php' );
	require_once( __DIR__ . '/Index.php' );
	require_once( __DIR__ . '/Keychain.php' );
	require_once( __DIR__ . '/Log.php' );
	require_once( __DIR__ . '/Request.php' );
	require_once( __DIR__ . '/Server.php' );
	require_once( __DIR__ . '/Text.php' );
	require_once( __DIR__ . '/Web.php' );
} else {
	// Alphred was invoked as a command, so....
	// Set date/time to avoid warnings/errors.
	if ( ! ini_get( 'date.timezone' ) ) {
		ini_set( 'date.timezone', exec( 'tz=`ls -l /etc/localtime` && echo ${tz#*/zoneinfo/}' ) );
	}

	if ( '2014' === date( 'Y', time() ) ) {
		define( 'ALPHRED_COPYRIGHT', '2014' );
	} else {
		define( 'ALPHRED_COPYRIGHT', '2014–' . date( 'Y', time() ) );
	}

	$options = getopt( 'h', [ 'help' ] );

	// They asked for the help file.
	if ( isset( $options['h'] ) || isset( $options['help'] ) ) {
		// Get the help command from the embedded text file
		$text = str_replace( 'ALPHRED_VERSION', ALPHRED_VERSION, file_get_contents( __DIR__ . '/../commands/help.txt' ) );
		// Update the copyright year
		$text = str_replace( 'ALPHRED_COPYRIGHT', ALPHRED_COPYRIGHT, $text );

		// Print the text of the help
		print $text;
		// Exit with status 0
		exit(0);
	}
}


// This is just a placeholder for now
function ALPHRED_PARSE_INI() {
	// if ( file_exists( $_SERVER['PWD'] . '/workflow.ini' ) ) {
	// 	print_r( parse_ini_file( $_SERVER['PWD'] . '/workflow.ini', true ) );
	// }
}