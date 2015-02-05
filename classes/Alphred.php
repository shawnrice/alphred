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
	require_once( __DIR__ . '/../commands/cli-functions.php' );

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
		// Get the help command from the embedded text file
		$text = str_replace( 'ALPHRED_VERSION', ALPHRED_VERSION, file_get_contents( __DIR__ . '/../commands/help.txt' ) );
		// Update the copyright year
		$text = str_replace( 'ALPHRED_COPYRIGHT', ALPHRED_COPYRIGHT, $text );

		// Print the text of the help
		print $text;
		print "---------------------------\n";
		print "Commands:\n";
		foreach ( $commands as $command => $help ) :
			print "\t{$command}:\t {$help}\n";
		endforeach;
		// Exit with status 0
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
						$text = file_get_contents( __DIR__ . '/../commands/server-scripts.txt');
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




/**
 * Wrapper Class.
 *
 * This provides a simple wrapper for all of the important parts of the Alphred library
 *
 */
class Alphred {


	public function construct( $options = [], $plugins = false ) {

		// We did already parse the INI file on a global scale when loading the library, but
		// we're going to parse it again for some functionality that we need here, such as
		// loading the plugins.
		$this->parse_ini_file();

		// We'll always create a script filter object to use
		$this->filter = new \Alphred\ScriptFilter( $options );
	}


	private function parse_ini_file() {
		$ini = parse_ini_file( $_SERVER['PWD'] . '/workflow.ini', true );

		if ( isset( $ini['alphred:plugins'] ) ) {
			$this->load_plugins( $ini['alphred_plugins'] );
		}

	}

	/**
	 * [add description]
	 * @param array $item an array of values to parse that construct an Alphred\Response object
	 */
	public function add( $item ) {
		// Adds items to a script filter

	}


	public function request_get( $options, $cache_ttl = 600 ) {

	}
	public function request_post( $options, $cache_ttl = 600 ) {

	}
	public function request_clear_cache( $bin = false ) {

	}


	// Wrappers around the config class
	public function config_get() {

	}
	public function config_set() {

	}
	public function config_reset() {

	}



	public function add_commas( $list, $suffix = false ) {
		return \Alphred\Text::add_commas_to_list( $list, $suffix );
	}



	public function notification( $options ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $account, $options ] );
		}
		// Default functionality
		return \Alphred\Notification::notify( $options );
	}



	// Wrappers around the Keychain Class
	public function get_password( $account, $options = false ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $account, $options ] );
		}
		// Default functionality
		return \Alphred\Keychain::find_password( $account, null );
	}
	public function delete_password( $account, $options = false ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $account, $options ] );
		}
		// Default functionality
		return \Alphred\Keychain::delete_password( $account, null );
	}
	public function save_password( $account, $password, $options = false ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $account, $password, $options ] );
		}
		// Default functionality
		return \Alphred\Keychain::save_password( $account, $password, true, null );
	}


/// These two functions make the wrapper pluggable

/**
 * Gets the name of the function to run when a plugin overrides default functionality
 *
 *
 * @param  string  $function_call    the name of the function
 * @return string|boolean            the name of the function to call or false if no plugin is loaded
 */
	private function get_plugin_function( $function_call ) {
		if ( isset( $this->plugins[ $function_call ] ) ) {
			return $this->plugins[ $function_call ];
		} else {
			return false;
		}
	}

	/**
	 * Loads a plugin to override default functionality
	 *
	 * @todo 	 Update for custom exception
	 * @throws Exception
	 *
	 * @param  string $function_call     the name of the function to override (name of method in this wrapper)
	 * @param  string $function 				 the name of the new function to call
	 */
	private function load_plugin_function( $function_call, $function ) {
		// Check to see if the function is callable. If so, set it in the plugins array;
		// if not, throw an exception
		if ( is_callable( $function ) ) {
			$this->plugins[ $function_call ] = $function;
		} else {
			throw new Exception( 'Bad function plugin' );
		}
	}

	/**
	 * Loads the plugins
	 * @param  [type] $plugins [description]
	 * @return [type]          [description]
	 */
	private function load_plugins( $plugins ) {
		foreach( $plugins as $original => $new ) :
			$this->load_plugin_function( $orignal, $new );
		endforeach;

	}

	// Logging Functions

	/**
	 * [console description]
	 *
	 * @see \Alphred\Log::console()
	 *
	 * @param  [type]  $message [description]
	 * @param  string  $level   [description]
	 * @param  boolean $trace   [description]
	 * @return [type]           [description]
	 */
	public function console( $message, $level = 'INFO', $trace = false ) {

		\Alphred\Log::console( $message, $level, $trace );
	}

	/**
	 * [log description]
	 *
	 * @see \Alphred\Log::file()
	 *
	 * @param  [type]  $message  [description]
	 * @param  string  $level    [description]
	 * @param  string  $filename [description]
	 * @param  boolean $trace    [description]
	 * @return [type]            [description]
	 */
	public function log( $message, $level = 'INFO', $filename = 'workflow', $trace = false ) {
		\Alphred\Log::file( $message, $level, $filename, $trace );
	}


}