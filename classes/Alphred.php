<?php
/**
 * Contains Globals class for Alphred
 *
 * PHP version 5
 *
 * @package 	 Alphred
 * @copyright  Shawn Patrick Rice 2014
 * @license    http://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @author     Shawn Patrick Rice <rice@shawnrice.org>
 * @link       http://www.github.com/shawnrice/alphred
 * @link       http://shawnrice.github.io/alphred
 * @since      File available since Release 1.0.0
 *
 */



/**
 * Wrapper Class.
 *
 * This provides a simple wrapper for all of the important parts of the Alphred library.
 *
 * Most of the default wrapper functionality can easily be replaced with plugins. Currently, you
 * need to create a `workflow.ini` file that will live next to the Alfred-generated `info.plist`
 * in the workflow root. Create a section in the `ini` file called `[alphred:plugins]`. For each
 * function you want to override in the wrapper, create a single line that maps onto the other function.
 * For example:
 * ````ini
 * [alphred:plugins]
 * get_password = my_new_get_password_function
 * config_read = MyClass::config_read
 * ````
 * That will load a different function for `Alphred::get_password` and `Alphred::config_read`. An extra
 * argument is available for most plugin functions called `$options` that you can use to pass any other
 * variables.
 *
 * To run plugins on load, then pass them to the `Alphred` object on creation as the second argument. They
 * should all be passed as an array defined as:
 * ````php
 * $alphred = new Alphred( [], [ 'my_on_load_plugin' => [ 'arg1', 'arg2' ], 'my_other_plugin' => null ] );
 * ````
 *
 */
class Alphred {

	/**
	 * Initializes the wrapper object
	 *
	 * @param array  				$options options that can be configured
	 *                            currently, only two options are available:
	 *                            1. error_on_empty - displays a script filter item when empty
	 *                            2. no_filter      - initializes object without a script filter
	 * @param array|boolean $plugins plugins to be run at load
	 */
	public function __construct( $options = [ 'error_on_empty' => true ], $plugins = false ) {

		// We did already parse the INI file on a global scale when loading the library, but
		// we're going to parse it again for some functionality that we need here, such as
		// loading the plugins.
		$this->parse_ini_file();

		if ( $plugins ) {
			$this->run_on_load_plugins( $plugins );
		}

		// Create a script filter object unless explicitly turned off
		if ( ! isset( $options[ 'no_filter' ] ) || true !== $options[ 'no_filter' ] ) {
			$this->filter = new \Alphred\ScriptFilter( $options );
		}


	}

	/**
	 * Execute a php script in the background
	 *
	 * @todo Check this to make sure it fully works
	 * @todo Work on argument escaping
	 * @todo see if we can set ALPHRED_RUNNING_IN_BACKGROUND for background awareness
	 *
	 * @param  string  $script path to php script
	 * @param  mixed 	 $args   args to pass to the script
	 */
	public function background( $script, $args = false ) {
		// Make sure that the script
		if ( ! file_exists( $script ) ) {
			throw new Alphred\FileDoesNotExist( "Script `{$script}` does not exist.", 4 );
		}
		if ( $args ) {
			if ( is_array( $args ) ) {
				// Turn $args into a string if we were passed an array
				$args = implode( ' ', $args );
			} else {
				// Quote args if it is a string
				$args = "'{$args}'";
			}
			$args = str_replace( '"', '\"', $args );
		}
		exec( "/usr/bin/nohup php '{$script}' {$args}  >/dev/null 2>&1 &", $output, $return );
	}

	private function parse_ini_file() {
		$ini = parse_ini_file( $_SERVER['PWD'] . '/workflow.ini', true );

		if ( isset( $ini['alphred:plugins'] ) ) {
			$this->load_plugins( $ini['alphred:plugins'] );
		}

	}


	public function filter() {

	}

	/*****************************************************************************
	 * Wrapper methods for script filters
	 ****************************************************************************/

	/**
	 * Adds a result to the script filter
	 *
	 * @param array $item an array of values to parse that construct an Alphred\Result object
	 * @param array $options array of arguments if using a plugin
	 */
	public function add_result( $item, $options = [] ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $item, $options ] );
		}
		// Default functionality
		return $this->filter->add_result( new \Alphred\Result( $item ) );
	}

	/**
	 * Prints the script filter XML
	 *
	 * @param  array|boolean $options options if using a plugin
	 * @return mixed
	 */
	public function to_xml( $options = false ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $options ] );
		}
		// Default functionality
		$this->filter->to_xml();
	}

	/**
	 * Alias of to_xml
	 *
	 * @see Alphred::to_xml()
	 *
	 * @param  boolean $options
	 * @return mixed
	 */
	public function print_results( $options = false ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $options ] );
		}
		// Default functionality
		$this->to_xml();
	}


	/*****************************************************************************
	 * Wrapper methods for requests ( GET / POST )
	 ****************************************************************************/

	public function request_get( $options, $cache_ttl = 600 ) {

	}
	public function request_post( $options, $cache_ttl = 600 ) {

	}
	public function request_clear_cache( $bin = false ) {

	}


	/*****************************************************************************
	 * Config functionality
	 ****************************************************************************/

	/**
	 * [config_read description]
	 * @param  [type] $key      [description]
	 * @param  string $handler  [description]
	 * @param  string $filename [description]
	 * @return [type]           [description]
	 */
	public function config_read( $key, $handler = 'ini', $filename = 'config', $options = false ) {
		// Check if a plugin is loaded to handle this functionality
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $key, $handler, $filename, $options ] );
		}
		// Default functionality
		// Create a new config object
		$config = new Alphred\Config( $handler, $filename );
		try {
			// Try to read it, and catch the exception if it is not set
			return $config->read( $key );
		} catch ( Alphred\ConfigKeyNotSet $e ) {
			// There is nothing, so return null
			return null;
		}
	}

	/**
	 * [config_set description]
	 * @param  [type]  $key      [description]
	 * @param  [type]  $value    [description]
	 * @param  string  $handler  [description]
	 * @param  string  $filename [description]
	 * @param  boolean $options  [description]
	 * @return [type]            [description]
	 */
	public function config_set( $key, $value, $handler = 'ini', $filename = 'config', $options = false ) {
		// Check if a plugin is loaded to handle this functionality
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $key, $handler, $filename, $options ] );
		}
		// Default functionality
		$config = new Alphred\Config( $handler, $filename );
		$config->set( $key, $value );
	}

	/**
	 * [config_delete description]
	 * @param  [type]  $key      [description]
	 * @param  string  $handler  [description]
	 * @param  string  $filename [description]
	 * @param  boolean $options  [description]
	 * @return [type]            [description]
	 */
	public function config_delete( $key, $handler = 'ini', $filename = 'config', $options = false ) {
		// Check if a plugin is loaded to handle this functionality
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $key, $handler, $filename, $options ] );
		}
		// Default functionality
		$config = new Alphred\Config( $handler, $filename );
		$config->delete( $key );
	}



	public function add_commas( $list, $suffix = false ) {
		return \Alphred\Text::add_commas_to_list( $list, $suffix );
	}


	/**
	 * Sends a system notification
	 *
	 * Use this for async notifications or when running code in the background. If you want
	 * regular "end-of-workflow" notifications, then use Alfred's built-in set.
	 *
	 * Since this uses AppleScript notifications, all of them will, unfortunately, have the
	 * icon for Script Editor in them, and this is not replaceable. If you want more control
	 * over your notifications, then use something like CocoaDialog or Terminal-Notifier.
	 *
	 * @since 1.0.0
	 * @uses Alphred\Notification::notify()
	 * @todo Check that return value is correct
	 * @see Alphred\Notification::notify() For more information on how to call with the correct options.
	 *
	 * @param  array $options   the list of options to construct the notification
	 * @return boolean          success
	 */
	public function notification( $options ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $options ] );
		}
		// Default functionality
		return \Alphred\Notification::notify( $options );
	}


	/*****************************************************************************
	 * Keychain Wrapper functions
	 ****************************************************************************/

	/**
	 * Gets a password from the keychain
	 *
	 * @param  [type]  $account [description]
	 * @param  boolean $options [description]
	 * @return [type]           [description]
	 */
	public function get_password( $account, $options = false ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $account, $options ] );
		}

		// Default functionality
		// \Alphred\Keychain::find_password throws an exception when the password does not
		// exist. This wrapper returns false if the password has not been found.
		try {
			return \Alphred\Keychain::find_password( $account, null );
		} catch ( \Alphred\PasswordNotFound $e ) {
			\Alphred\Log::console( "No password for account `{$account}` was found. Returning false.", 2 );
			return false;
		}
	}

	/**
	 * Deletes a password from the keychain
	 *
	 *
	 * @param  [type]  $account [description]
	 * @param  boolean $options [description]
	 * @return [type]           [description]
	 */
	public function delete_password( $account, $options = false ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $account, $options ] );
		}
		// Default functionality
		return \Alphred\Keychain::delete_password( $account, null );
	}

	/**
	 * Saves a password to the keychain
	 *
	 *
	 * @param  [type]  $account  [description]
	 * @param  [type]  $password [description]
	 * @param  boolean $options  [description]
	 * @return [type]            [description]
	 */
	public function save_password( $account, $password, $options = false ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $account, $password, $options ] );
		}
		// Default functionality
		return \Alphred\Keychain::save_password( $account, $password, true, null );
	}

	/**
	 * Creates an AppleScript dialog to enter a password securely
	 *
	 * Note: this will return 'canceled' if the user presses the 'cancel' button
	 *
	 * @uses Alphred\Dialog
	 *
	 * @param  string|boolean $text  		the text for the dialog
	 * @param  string|boolean $title 		the title of the dialog; defaults to the workflow name
	 * @param  string|boolean $icon  		An icon to use with the dialog box
	 * @param  array 					$options  Unused, but can be used by a plugin
	 * @return string         the result of the user-input
	 */
	public function get_password_dialog( $text = false, $title = false, $icon = false, $options = [] ) {
		// This makes the function pluggable, (i.e. overrideable)
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $text, $title, $icon, $options ] );
		}
		// Default functionality
		// Set the default text
		if ( ! $text ) {
			$text = 'Please enter the password.';
		}
		// Set the default title to be that of the workflow's name
		if ( ! $title ) {
			$title = \Alphred\Globals::get( 'alfred_workflow_name' );
		}
		// Create hidden answer AppleScript dialog
		$dialog = new \Alphred\Dialog([
		  'text' => $text,
		  'title' => $title,
		  'default_answer' => '',
		  'hidden_answer' => true
		]);
		// If there was an icon, then set it
		if ( $icon ) {
			$dialog->set_icon( $icon );
		}
		// Execute the dialog and return the result
		return $dialog->execute();
	}

	/*****************************************************************************
	 * Logging Functions
	 ****************************************************************************/

	/**
	 * Sends a log message to the console
	 *
	 * If the log level is set higher than the level that this function is called with,
	 * then nothing will happen.
	 *
	 * @see \Alphred\Log::console() More information on the console log
	 * @uses \Alphred\Log
	 *
	 * @param  string  					$message the message to log
	 * @param  string|integer   $level   the log level
	 * @param  integer|boolean  $trace   how far to go in the stacktrace. Defaults to the last level.
	 * @return mixed            default returns nothing
	 */
	public function console( $message, $level = 'INFO', $trace = false, $options = false ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $message, $level, $trace, $options ] );
		}
		// Default functionality
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
	public function log( $message, $level = 'INFO', $filename = 'workflow', $trace = false, $options = false ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $message, $level, $filename, $trace, $options ] );
		}
		// Default functionality
		\Alphred\Log::file( $message, $level, $filename, $trace );
	}

	/*****************************************************************************
	 * FuzzySearch / Indexing Methods
	 ****************************************************************************/

	/*****************************************************************************
	 * Text Processing Filters
	 ****************************************************************************/

	public function time_ago( $seconds ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $seconds ] );
		}
		// Default functionality
		return Alphred\Date::ago( $seconds );
	}

	/**
	 *
	 *
	 *
	 * @param  [type] $seconds [description]
	 * @return string          a string that represents an approximate time
	 */
	public function fuzzy_time_diff( $seconds ) {
		if ( $function = $this->get_plugin_function( __FUNCTION__ ) ) {
			return call_user_func_array( $function, [ $seconds ] );
		}
		// Default functionality
		return Alphred\Date::fuzzy_ago( $seconds );
	}


	/*****************************************************************************
	 * AppleScript Filters
	 ****************************************************************************/

	/*****************************************************************************
	 * These methods create and define the plugin functionality.
	 ****************************************************************************/

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
	 * @throws Alphred\PluginFunctionNotFound
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
			throw new Alphred\PluginFunctionNotFound( "Function `{$function}` is invalid.", 4 );
		}
	}

	/**
	 * Loads the plugins
	 * @param  [type] $plugins [description]
	 * @return [type]          [description]
	 */
	private function load_plugins( $plugins ) {
		foreach( $plugins as $original => $new ) :
			$this->load_plugin_function( $original, $new );
		endforeach;

	}

	/**
	 * [run_on_load_plugins description]
	 *
	 * @throws Alphred\PluginFunctionNotFound
	 *
	 * @param  [type] $plugins [description]
	 * @return [type]          [description]
	 */
	private function run_on_load_plugins( $plugins ) {
		if ( ! is_array( $plugins ) ) {
			throw new Exception( "Plugins passed on load needs to be an array defined as function => [ args ].", 4 );
		}
		// Cycle through the plugins array and call each function
		foreach ( $plugins as $function => $args ) :
			if ( ! is_callable( $function ) ) {
				throw new Alphred\PluginFunctionNotFound( "Function `{$function}` is invalid.", 4 );
			}
			call_user_func_array( $function, [ $args ] );
		endforeach;
	}

}