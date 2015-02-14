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
 * @copyright  Shawn Patrick Rice 2014-2015
 * @license    http://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @author     Shawn Patrick Rice <rice@shawnrice.org>
 * @link       http://www.github.com/shawnrice/alphred
 * @link       http://shawnrice.github.io/alphred
 * @since      File available since Release 1.0.0
 *
 */

// Set the version of the library as a constant
define( 'ALPHRED_VERSION',   trim( file_get_contents( __DIR__ . '/commands/Version' ) ) );

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
	require_once( __DIR__ . '/classes/Alfred.php' );
	require_once( __DIR__ . '/classes/Alphred.php' );
	require_once( __DIR__ . '/classes/AppleScript.php' );
	require_once( __DIR__ . '/classes/Choose.php' );
	require_once( __DIR__ . '/classes/Notification.php' );
	require_once( __DIR__ . '/classes/Dialog.php' );
	require_once( __DIR__ . '/classes/Config.php' );
	require_once( __DIR__ . '/classes/Date.php' );
	require_once( __DIR__ . '/classes/Exceptions.php' );
	require_once( __DIR__ . '/classes/Filter.php' );
	require_once( __DIR__ . '/classes/Globals.php' );
	require_once( __DIR__ . '/classes/i18n.php' );
	require_once( __DIR__ . '/classes/Ini.php' );
	require_once( __DIR__ . '/classes/Keychain.php' );
	require_once( __DIR__ . '/classes/Log.php' );
	require_once( __DIR__ . '/classes/Request.php' );
	require_once( __DIR__ . '/classes/ScriptFilter.php' );
	require_once( __DIR__ . '/classes/Text.php' );

	// So, we need some Alfred set enviromental variables for this to work.
	// if ( $version = Alphred\Globals::get( 'alfred_version' ) ) {
	// 	$version = explode( '.', $version );
	// 	if ( $version[1] < 6 ) {
	// 		Alphred\Notification::notify([ 'title' => 'Please upgrade '])
	// 	}
	// } else {
	// 	throw new Alphred\RunningOutsideOfAlfred( "Alphred cannot run outside of a workflow enviroment. (Failed version check).", 4 );
	// }
} else {
	// Alphred was invoked as a command, so let's include the cli-functions file that contains, well
	// most of the functions for using Alphred.phar as a cli utility.
	require_once( __DIR__ . '/commands/cli-functions.php' );

	if ( '2014' === date( 'Y', time() ) ) {
		define( 'ALPHRED_COPYRIGHT', '2014' );
	} else {
		define( 'ALPHRED_COPYRIGHT', '2014–' . date( 'Y', time() ) );
	}

	// An array of possible commands...
	$commands = [
		'create-server-scripts' => 'Creates the scripts to run your workflow through the CLI server SAPI',
		'update-self-master'    => 'Updates Alphred.phar to the latest on the master branch'
	];

	// Parse the options
	$options = getopt( 'h', [ 'help' ] );

	// There was no command, so we'll just assume that they wanted the help
	if ( ! isset( $argv[1] ) ) {
		$options['h'] = true;
	}

	// If the command sent wasn't in the commands array, then show the help
	if ( isset( $argv[1] ) && ! isset( $commands[ trim( $argv[1] ) ] ) ) {
		$options['h'] = true;
	}

	// They asked for the help file.
	if ( isset( $options['h'] ) || isset( $options['help'] ) ) {
		print_alphred_help( $commands );
		exit(0);
	}

	// This just copies the `server.sh`, `kill.sh`, `server.php` scripts into the current directory, and
	// then displays the server help.
	if ( 'create-server-scripts' == trim( $argv[1] ) ) {
		switch ( strtolower( alphred_confirm_create_server_scripts() ) ):
			case 'y':
			case 'yes':
				switch ( strtolower( alphred_confirm_create_server_scripts_path() ) ):
					case 'y':
					case 'yes':
						// foreach( [ 'server.sh', 'kill.sh', 'server.php' ] as $file ) :
						// 	file_put_contents( $_SERVER['PWD'] . "/{$file}", file_get_contents( __DIR__ . "/../scripts/{$file}" ) );
						// endforeach;
						$text = file_get_contents( __DIR__ . '/commands/server-scripts.txt');
						$text = str_replace( 'ALPHRED_VERSION', ALPHRED_VERSION, $text );
						print $text;
						break;
					case 'n':
					case 'no':
					default:
						print "Canceled script creation.\n";
						break;
				endswitch;
				break;
			case 'n':
			case 'no':
				print "Canceled script creation.\n";
				break;
		endswitch;
	}

	if ( 'update-self-master' == trim( $argv[1] ) ) {
		update_alphred_from_master();
	}

}


// This is just a placeholder for now
function ALPHRED_PARSE_INI() {
	if ( ! file_exists( $_SERVER['PWD'] . '/workflow.ini' ) ) {
		// Exit early if the workflow.ini file does not exist
		return false;
	}
	$ini = parse_ini_file( $_SERVER['PWD'] . '/workflow.ini', true );
	// We can only about the Alphred section
	$ini = $ini['alphred'];

	if ( isset( $ini['log_level'] ) ) {
		define( 'ALPHRED_LOG_LEVEL', $ini['log_level'] );
	}
	if ( isset( $ini['log_size'] ) ) {
		define( 'ALPHRED_LOG_SIZE', $ini['log_size'] );
	}

}
