<?php
/**
 * Entry point for the library. Sets the environment.
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

// Parse the INI file early, if it exists
ALPHRED_PARSE_INI();

// This is needed because, Macs don't read EOLs well.
if ( ! ini_get( 'auto_detect_line_endings' ) ) {
	ini_set( 'auto_detect_line_endings', true );
}

// Set date/time to avoid warnings/errors.
if ( ! ini_get( 'date.timezone' ) ) {
	ini_set( 'date.timezone', exec( 'tz=`ls -l /etc/localtime` && echo ${tz#*/zoneinfo/}' ) );
}

// Define the log level if not already defined, either in code or in the INI file
if ( ! defined( 'ALPHRED_LOG_LEVEL' ) ) {
	define( 'ALPHRED_LOG_LEVEL', 2 );
}

if ( ! defined( 'ALPHRED_LOG_SIZE' ) ) {
	define( 'ALPHRED_LOG_SIZE', 1048576 );
}

// We might need to set the type: http://php.net/manual/en/function.iconv.php#74101
// setlocale( LC_CTYPE, exec('defaults read -g AppleLocale') );

# These replicate Alfred Workflow
# ####
# Match filter flags
#: Match items that start with ``query``
define( 'MATCH_STARTSWITH', 1 );
#: Match items whose capital letters start with ``query``
define( 'MATCH_CAPITALS', 2 );
#: Match items with a component "word" that matches ``query``
define( 'MATCH_ATOM', 4 );
#: Match items whose initials (based on atoms) start with ``query``
define( 'MATCH_INITIALS_STARTSWITH', 8 );
#: Match items whose initials (based on atoms) contain ``query``
define( 'MATCH_INITIALS_CONTAIN', 16 );
#: Combination of :const:`MATCH_INITIALS_STARTSWITH` and
#: :const:`MATCH_INITIALS_CONTAIN`
define( 'MATCH_INITIALS', 24 );
#: Match items if ``query`` is a substring
define( 'MATCH_SUBSTRING', 32 );
#: Match items if all characters in ``query`` appear in the item in order
define( 'MATCH_ALLCHARS', 64 );
#: Combination of all other ``MATCH_*`` constants
define( 'MATCH_ALL', 127 );

// Check if Alphred.phar was included or run. Behavior differs based on that
if ( ! ( isset( $argv ) && ( 'Alphred.phar' === basename( $argv[0] ) || 'Alphred.php' === basename( $argv[0] ) ) ) ) {
	// Alphred was included and not run directly
	require_once( __DIR__ . '/Alfred.php' );
	require_once( __DIR__ . '/AppleScript.php' );
	require_once( __DIR__ . '/Config.php' );
	require_once( __DIR__ . '/Database.php' );
	require_once( __DIR__ . '/Date.php' );
	require_once( __DIR__ . '/Exceptions.php' );
	require_once( __DIR__ . '/Filter.php' );
	require_once( __DIR__ . '/Globals.php' );
	require_once( __DIR__ . '/i18n.php' );
	require_once( __DIR__ . '/Index.php' );
	require_once( __DIR__ . '/Keychain.php' );
	require_once( __DIR__ . '/Log.php' );
	require_once( __DIR__ . '/Request.php' );
	require_once( __DIR__ . '/Server.php' );
	require_once( __DIR__ . '/ScriptFilter.php' );
	require_once( __DIR__ . '/Text.php' );
	require_once( __DIR__ . '/Web.php' );
} else {
	// Alphred was invoked as a command, so....

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