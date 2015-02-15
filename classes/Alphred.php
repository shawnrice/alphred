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
	public function __construct( $options = [ 'error_on_empty' => true ] ) {

		// Create a script filter object unless explicitly turned off
		if ( ! isset( $options[ 'no_filter' ] ) || true !== $options[ 'no_filter' ] ) {
			$this->filter = new \Alphred\ScriptFilter( $options );
		}


	}

	/**
	 * Calls an Alfred External Trigger
	 *
	 * @since 1.0.0
	 * @uses Alphred::call_external_trigger()
	 *
	 * @param  string  				$bundle   the bundle id of the workflow to trigger
	 * @param  string  				$trigger  the name of the trigger
	 * @param  string|boolean $argument an argument to pass
	 * @return null
	 */
	public function trigger( $bundle, $trigger, $argument = false ) {
		return $this->call_external_trigger( $bundle, $trigger, $argument );
	}

	/**
	 * Calls an Alfred External Trigger
	 *
	 * @since 1.0.0
	 *
	 * @param  string  				$bundle   the bundle id of the workflow to trigger
	 * @param  string  				$trigger  the name of the trigger
	 * @param  string|boolean $argument an argument to pass
	 */
	private function call_external_trigger( $bundle, $trigger, $argument = false ) {
		$script = "tell application \"Alfred 2\" to run trigger \"{$trigger}\" in workflow \"{$bundle}\"";
		if ( false !== $argument ) {
			$script .= "with argument \"{$argument}\"";
		}
		// Execute the AppleScript to call the trigger
		exec( "osascript -e '$script'" );
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
			// File does not exist, so throw an exception
			throw new Alphred\FileDoesNotExist( "Script `{$script}` does not exist.", 4 );
		}
		if ( $args ) {
			if ( is_array( $args ) ) {
				// Turn $args into a string if we were passed an array
				$args = implode( "' '", $args );
				// prepend and append the extra quotation marks... everything *should* be quoted now
				$args = "'{$args}'";
			} else {
				// Quote args if it is a string
				$args = "'{$args}'";
			}
			// Let's escape double-quotes
			$args = str_replace( '"', '\"', $args );
		}
		// Set a variable to let us know that we're in the background, and execute the script
		exec( "ALPHRED_IN_BACKGROUND=1 /usr/bin/nohup php '{$script}' {$args}  >/dev/null 2>&1 &", $output, $return );
	}

	/**
	 * Tells you whether or not a script is running in the background
	 *
	 * @since 1.0.0
	 *
	 * @return boolean true if in the background; false if not
	 */
	public function is_background() {
		return Alphred\Globals::is_background();
	}

	public function filter() {

	}



	/*****************************************************************************
	 * Wrapper methods for script filters
	 ****************************************************************************/

	/**
	 * Adds a result to the script filter
	 *
	 * @since 1.0.0
	 *
	 * @param array $item an array of values to parse that construct an Alphred\Result object
	 */
	public function add_result( $item ) {
		return $this->filter->add_result( new \Alphred\Result( $item ) );
	}

	/**
	 * Prints the script filter XML
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function to_xml() {
		$this->filter->to_xml();
	}

	/**
	 * Alias of to_xml
	 *
	 * @uses Alphred::to_xml()
	 *
	 * @return mixed
	 */
	public function print_results() {
		$this->to_xml();
	}


	/*****************************************************************************
	 * Wrapper methods for requests ( GET / POST )
	 ****************************************************************************/

	/**
	 * Makes a `GET` Request
	 *
	 * This method is good for simple `GET` requests. By default, requests are cached for
	 * 600 seconds (ten minutes), and all options are passed via the `$options` array. Here
	 * are the options:
	 *  params     (array as $key => $value)
	 *  auth       (array as [ username, password ] )
	 *  user_agent (string)
	 *  headers    (array as list of headers to add)
	 *
	 * Set only the options that you need.
	 *
	 * To turn caching off, just set `$cache_ttl` to 0.
	 *
	 * The `$cache_bin` is the subfolder within the workflow's cache folder. If set to `true`,
	 * then the cache bin will be named after the hostname of the URL. So, if you are requesting
	 * something from `http://api.github.com/v3/shawnrice/repos`, the `cache bin` would be
	 * `api.github.com`. If you were requesting `http://www.google.com`, then the `cache bin`
	 * would be `www.google.com`.
	 *
	 * @uses Alphred\Request
	 *
	 * @param  string  				$url       the URL
	 * @param  array|boolean  $options   an array of options for the request
	 * @param  integer 				$cache_ttl cache time to live in seconds
	 * @param  string|boolean $cache_bin cache bin
	 * @return string         the results
	 */
	public function get( $url, $options = false, $cache_ttl = 600, $cache_bin = true ) {
		$request = $this->create_request( $url, $options, $cache_ttl, $cache_bin, 'get' );
		return $request->execute();
	}

	/**
	 * Makes a `POST` request
	 *
	 * @see Alphred::get() See `get()` for details. The method is basically the same.
	 *
	 * @uses Alphred\Request
	 *
	 * @param  string  				$url       [description]
	 * @param  array|boolean  $options   an array of options for the request
	 * @param  integer 				$cache_ttl cache time to live in seconds
	 * @param  string|boolean $cache_bin cache bin
	 * @return string         the results
	 */
	public function post( $url, $options = false, $cache_ttl = 600, $cache_bin = true ) {
		$request = $this->create_request( $url, $options, $cache_ttl, $cache_bin, 'post' );
		return $request->execute();
	}

	/**
	 * Creates a request object
	 *
	 * @param  string  				$url       the URL
	 * @param  array|boolean  $options   an array of options for the request
	 * @param  integer 				$cache_ttl cache time to live in seconds
	 * @param  string|boolean $cache_bin cache bin
	 * @param  string 				$type      either `get` or `post`
	 * @return Alphred\Request           the request object
	 */
	private function create_request( $url, $options, $cache_ttl, $cache_bin, $type ) {

		if ( $cache_ttl > 0 ) {
			// Create an object with caching on
			$request = new Alphred\Request( $url, [ 'cache' => true,
			                               					'cache_ttl' => $cache_ttl,
			                               					'cache_bin' => $cache_bin ] );
		} else {
			// Create an object with caching off
			$request = new Alphred\Request( $url, [ 'cache' => false ] );
		}
		// Set it to `POST` if that's what they want
		if ( 'post' == $type ) {
			$requst->use_post();
		}
		// If there are options, then go through them and set everything
		if ( $options ) {
			if ( isset( $options['params'] ) ) {
				if ( ! is_array( $options['params'] ) ) {
					throw new Alphred\Exception( 'Parameters must be passed as an array', 4 );
				}
				// Add the parameters
				$request->add_parameters( $options['params'] );
			}
			// For basic http authentication
			if ( isset( $options['auth'] ) ) {
				// Make sure that there are two options in the auth array
				if ( ! is_array( $options['auth'] ) || ( 2 !== count( $options['auth'] ) ) ) {
					throw new Alphred\Exception( 'You need two arguments in the auth array.', 4 );
				}
				// Set the options
				$request->set_auth( $options['auth'][0], $options['auth'][1] );
			}
			// If we need a user agent
			if ( isset( $options['user_agent'] ) ) {
				// Make sure that the user agent is a string
				if ( ! is_string( $options['user_agent'] ) ) {
					// It's not, so throw an exception
					throw new Alphred\Exception( 'The user agent must be a string', 4 );
				}
				// Set the user agent
				$request->set_user_agent( $options['user_agent'] );
			}
			// If we need to add headers
			if ( isset( $options['headers'] ) ) {
				if ( ! is_array( $options['headers'] ) ) {
					throw new Alphred\Exception( 'Headers must be passed as an array', 4 );
				} else {
					$request->set_headers( $options['headers'] );
				}
			}
		}
		return $request;
	}

	/**
	 * Clears a cache bin
	 *
	 * Clears a cache bin. If you send it with no argument (i.e.: `$bin = false`), then
	 * it will attempt to clear the workflow's cache directory. Note: this will throw an
	 * exception if it encounters a sub-directory. While it would be easy to make this
	 * function clear sub-directories, it shouldn't. If you are storing data other than responses
	 * in your cache directory, then use a cache-bin with the requests.
	 *
	 * @since 1.0.0
	 * @throws Alphred\Exception when encountering a sub-directory
	 * @uses Alphred\Request::clear_cache()
	 *
	 * @param  string|boolean $bin the cache bin to clear
	 * @return null
	 */
	public function clear_cache( $bin = false ) {
		return Alphred\Request::clear_cache( $bin );
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
	public function config_read( $key, $handler = 'ini', $filename = 'config' ) {
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
	 * @return [type]            [description]
	 */
	public function config_set( $key, $value, $handler = 'ini', $filename = 'config' ) {
		$config = new Alphred\Config( $handler, $filename );
		$config->set( $key, $value );
	}

	/**
	 * [config_delete description]
	 * @param  [type]  $key      [description]
	 * @param  string  $handler  [description]
	 * @param  string  $filename [description]
	 * @return [type]            [description]
	 */
	public function config_delete( $key, $handler = 'ini', $filename = 'config' ) {
		$config = new Alphred\Config( $handler, $filename );
		$config->delete( $key );
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
	public function send_notification( $options ) {
		return \Alphred\Notification::notify( $options );
	}


	/*****************************************************************************
	 * Keychain Wrapper functions
	 ****************************************************************************/

	/**
	 * Gets a password from the keychain
	 *
	 * @uses \Alphred\Keychain::find_password()
	 *
	 * @param  [type]  $account [description]
	 * @param  boolean $options [description]
	 * @return [type]           [description]
	 */
	public function get_password( $account ) {
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
	public function delete_password( $account ) {
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
	public function save_password( $account, $password ) {
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
	public function get_password_dialog( $text = false, $title = false, $icon = false ) {
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
	public function console( $message, $level = 'INFO', $trace = false ) {
		\Alphred\Log::console( $message, $level, $trace );
	}


	/**
	 * Writes a log message to a log file
	 *
	 *
	 * @uses \Alphred\Log::file()
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

	/*****************************************************************************
	 * FuzzySearch / Indexing Methods
	 ****************************************************************************/

	/*****************************************************************************
	 * Text Processing Filters
	 ****************************************************************************/

	public function time_ago( $seconds ) {
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
		return Alphred\Date::fuzzy_ago( $seconds );
	}

	public function add_commas( $list, $suffix = false ) {
		return \Alphred\Text::add_commas_to_list( $list, $suffix );
	}

	/*****************************************************************************
	 * AppleScript Filters
	 ****************************************************************************/

	public function activate( $application ) {

	}

	public function get_active_window() {

	}


}