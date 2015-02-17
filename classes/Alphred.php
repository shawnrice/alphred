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
 * It also simplifies the usage of some of the internal components, so calls to this class
 * do not always mirror calls to the internal components.
 *
 * @todo make it so that config handler and filename can be set from workflow.ini
 * @todo possibly instantiate object with a config so that we don't have to create the object
 *       with each config call
 *
 */
class Alphred {

	/**
	 * Initializes the wrapper object
	 *
	 * @param array  				$options options that can be configured
	 *                            currently, only two options are available:
	 *                            1. error_on_empty  - displays a script filter item when empty
	 *                            2. no_filter       - initializes object without a script filter
	 *                            3. no_config       - creates without a config item
	 *                            4. config_filename - sets filename for the config (default: `config`)
	 *                            5. config_handler  - sets the handler for the config (default: `ini`)
	 * @param array|boolean $plugins plugins to be run at load
	 */
	public function __construct( $options = [ 'error_on_empty' => true ] ) {

		// We're going to parse the ini file (if it exists) and just merge what we find there
		// with the $options array. The original $options array will override the workflow.ini file.
		$options = array_merge( $options, $this->parse_ini_file() );

		// Create a script filter object unless explicitly turned off
		if ( ! isset( $options[ 'no_filter' ] ) || true !== $options[ 'no_filter' ] ) {
			$this->filter = new Alphred\ScriptFilter( $options );
		}

		if ( ! isset( $options[ 'no_config' ] ) || true !== $options[ 'no_config' ] ) {
			// Use `ini` as the default handler and `config` as the default filename
			$handler  = ( isset( $options['config_handler'] ) ) ? $options['config_handler'] : 'ini';
			$filename = ( isset( $options['config_filename'] ) ) ? $options['config_filename'] : 'config';
			// Create the config object
			$this->config = new Alphred\Config( $handler, $filename );
		}
	}

	/**
	 * Reads the `workflow.ini` file if it exists
	 *
	 * @return array an array of config values
	 */
	private function parse_ini_file() {
		// If the file does not exist, then exit early with an empty array
		if ( ! file_exists( 'workflow.ini' ) ) {
			return [];
		}

		// Read the ini file
		$ini = Alphred\Ini::read_ini( 'workflow.ini' );

		// Just return the alphred bit
		return $ini['alphred'];

	}

	/**
	 * Execute a php script in the background
	 *
	 * @todo Work on argument escaping
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

	/**
	 * Calls an Alfred External Trigger
	 *
	 * Single and double-quotes in the argument might break this method, so make sure that you
	 * escape them appropriately.
	 *
	 * @since 1.0.0
	 * @uses Alphred\Alfred::call_external_trigger()
	 *
	 * @param  string  				$bundle   the bundle id of the workflow to trigger
	 * @param  string  				$trigger  the name of the trigger
	 * @param  string|boolean $argument an argument to pass
	 */
	public function call_external_trigger( $bundle, $trigger, $argument = false ) {
		Alphred\Alfred::call_external_trigger( $bundle, $trigger, $argument );
	}

	/**
	 * Tells you if the current theme is `light` or `dark`
	 *
	 * @uses Alphred\Alfred::light_or_dark()
	 * @return string either 'light' or 'dark'
	 */
	public function theme_background() {
		return Alphred\Alfred::light_or_dark();
	}


	/**
	 * Filters an array based on a query
	 *
	 * Passing an empty query ($needle) to this method will simply return the initial array.
	 * If you have `fold` on, then this will fail on characters that cannot be translitered
	 * into regular ASCII, so most Asian languages.
	 *
	 * The options to be set are:
	 * 	* max_results  -- the maximum number of results to return (default: false)
	 * 	* min_score    -- the minimum score to return (0-100) (default: false)
	 * 	* return_score -- whether or not to return the score along with the results (default: false)
	 * 	* fold         -- whether or not to fold diacritical marks, thus making
	 * 										`über` into `uber`. (default: true)
	 * 	* match_type	 -- the type of filters to run. (default: MATCH_ALL)
	 *
	 *  The match_type is defined as constants, and so you can call them by the flags or by
	 *  the integer value. Options:
	 *    Match items that start with the query
	 *    1: MATCH_STARTSWITH
	 *    Match items whose capital letters start with ``query``
	 *    2: MATCH_CAPITALS
	 *    Match items with a component "word" that matches ``query``
	 *    4: MATCH_ATOM
	 *    Match items whose initials (based on atoms) start with ``query``
	 *    8: MATCH_INITIALS_STARTSWITH
	 *    Match items whose initials (based on atoms) contain ``query``
	 *    16: MATCH_INITIALS_CONTAIN
	 *    Combination of MATCH_INITIALS_STARTSWITH and MATCH_INITIALS_CONTAIN
	 *    24: MATCH_INITIALS
	 *    Match items if ``query`` is a substring
	 *    32: MATCH_SUBSTRING
	 *    Match items if all characters in ``query`` appear in the item in order
	 *    64: MATCH_ALLCHARS
	 *    Combination of all other ``MATCH_*`` constants
	 *    127: MATCH_ALL
	 *
	 * @param  array  				$haystack the array of items to filter
	 * @param  string  				$needle   the search query to filter against
	 * @param  string|boolean $key      the name of the key to filter on if array is associative
	 * @param  array 					$options  a list of options to configure the filter
	 * @return array          an array of filtered items
	 */
	public function filter( $haystack, $needle, $key = false, $options = [] ) {
		return Alphred\Filter::Filter( $haystack, $needle, $key, $options );
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
			$request->use_post();
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
	 * Reads a configuration value
	 *
	 * @param  string $key      name of key
	 * @return mixed            the value of the key or null if not set
	 */
	public function config_read( $key ) {
		try {
			// Try to read it, and catch the exception if it is not set
			return $this->config->read( $key );
		} catch ( Alphred\ConfigKeyNotSet $e ) {
			// There is nothing, so return null
			return null;
		}
	}

	/**
	 * Sets a configuration value
	 *
	 * @param  string  $key      the name of the key
	 * @param  mixed   $value    the value for the key
	 */
	public function config_set( $key, $value ) {
		$this->config->set( $key, $value );
	}

	/**
	 * Deletes a config value
	 *
	 * @param  string  $key      name of the key
	 */
	public function config_delete( $key ) {
		$this->config->delete( $key );
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
	 * @param  string  $account the name of the account (key) for the password
	 * @return string|boolean   the password or false if not found
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
	 * @uses \Alphred\Keychain::delete_password()
	 *
	 * @param  string  $account the name of the account (key) for the password
	 * @return boolean          true if it existed and was deleted, false if it didn't exist
	 */
	public function delete_password( $account ) {
		return \Alphred\Keychain::delete_password( $account, null );
	}

	/**
	 * Saves a password to the keychain
	 *
	 * @uses \Alphred\Keychain::save_password()
	 *
	 * @param  string  $account  the name of the account (key) for the password
	 * @param  string  $password the password
	 * @return boolean
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
	 * @see \Alphred\Log::file() More information on the file log
	 * @uses \Alphred\Log
	 *
	 * @param  string  		 $message  message to log
	 * @param  string|int  $level    log level
	 * @param  string  		 $filename filename with no extension
	 * @param  boolean|int $trace    how far back to trace
	 */
	public function log( $message, $level = 'INFO', $filename = 'workflow', $trace = false ) {
		\Alphred\Log::file( $message, $level, $filename, $trace );
	}

	/*****************************************************************************
	 * Text Processing Filters
	 ****************************************************************************/

	/**
	 * Takes a unix epoch time and renders it as a string
	 *
	 * This also works for future times. If you set `$words` to `true`, then you will
	 * get "one" instead of "1". Past times are appended with "ago"; future times are
	 * prepended with "in ".
	 *
	 * @param  integer  $seconds unix epoch time value
	 * @param  boolean $words    whether to use words or numerals
	 * @return string
	 */
	public function time_ago( $seconds, $words = false ) {
		return Alphred\Date::ago( $seconds, $words );
	}

	/**
	 * Takes a time and gives you a fuzzy description of when it is/was relative to now
	 *
	 * So, something like "5 days, 16 hours, and 34 minutes ago" turns into "almost a week ago";
	 * Something like "16 hours from now" turns into "yesterday"; and something like "1 month from now"
	 * turns into "in a month"; it's fuzzy. Also, the first strings need to be a unix epoch time,
	 * so the number of seconds since 1 Jan, 1970 12:00AM.
	 *
	 * @param  int $seconds    a unix epoch time
	 * @return string          a string that represents an approximate time
	 */
	public function fuzzy_time_diff( $seconds ) {
		return Alphred\Date::fuzzy_ago( $seconds );
	}

	/**
	 * Implodes an array into a string with commas (uses an Oxford comma)
	 *
	 * If you set `$suffix` to `true`, then the function expects an associative array
	 * as 'suffix' => 'word', so an array like:
	 * ````php
	 * $list = [ 'penny' => 'one', 'quarters' => 'three', 'dollars' => 'five' ];
	 * ````
	 * will render as: "one penny, three quarters, and five dollars"
	 *
	 * @param array   $list    the array to add commas to
	 * @param boolean $suffix whether or not there is a suffix
	 * @return string 				the array, but as a string with commas
	 */
	public function add_commas( $list, $suffix = false ) {
		return \Alphred\Text::add_commas_to_list( $list, $suffix );
	}

	/*****************************************************************************
	 * AppleScript Actions
	 ****************************************************************************/

	/**
	 * Activates an application
	 *
	 * Brings an application to the front, launching it if necessary
	 *
	 * @param  string $application the name of the application
	 */
	public function activate( $application ) {
		Alphred\AppleScript::activate( $application );
	}

	/**
	 * Gets the active window
	 *
	 * @return array an array of [ 'app_name' => $name, 'window_name' => $name ]
	 */
	public function get_active_window() {
		return Alphred\AppleScript::get_front();
	}

	/**
	 * Brings an application to the front
	 *
	 * This is like `activate`, but it does not open the application if it is
	 * not already open.
	 *
	 * @param  string $application the name of an application
	 */
	public function bring_to_front( $application ) {
		Alphred\AppleScript::bring_to_front( $application );
	}



}