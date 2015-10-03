<?php
/**
 * Contains the Request library for Alphred
 *
 * PHP version 5
 *
 * @package 	 Alphred
 * @copyright  Shawn Patrick Rice 2014-2015
 * @license    http://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @author     Shawn Patrick Rice <rice@shawnrice.org>
 * @link       http://www.github.com/shawnrice/alphred
 * @link       http://shawnrice.github.io/alphred
 * @since      File available since Release 1.0.0
 *
 */


namespace Alphred;

/**
 * Generic, light-weight, low-functionality wrapper around PHP's cURL library
 *
 * This Requests library should be good enough for most requests, as long as you
 * aren't doing anything special or crazy. If you outgrow it, then you should either
 * (1) use Guzzle, or (2) write your own requests library that has better coverage.
 *
 * Granted, this should handle MOST use cases. I don't know if it handles file uploads.
 * Theoretically, it does, but I wouldn't bank on it, and, if it doesn't, I will not
 * expand the functionality to cover file uploads.
 *
 * With this, you can easily make GET or POST requests. Set extra headers. Easily set
 * a user-agent. Set parameters. And cache the data for later retrieval.
 *
 *
 */
class Request {

	/**
	 * The internal cURL handler
	 * @var Resource
	 */
	private $handler;

	/**
	 * An internal structuring of the request object for cache creation
	 * @var array
	 */
	private $object;

	/**
	 * Creates a request object
	 *
	 * Currently, all the options apply to caching. So the three that are understood are:
	 * 1. `cache`,
	 * 2. `cache_life`, and
	 * 3. `cache_bin`.
	 *
	 * `cache` is a boolean that can turn on/off caching. It is recommended that you turn it on.
	 * `cache_life` is how long the cache will live. In other words, no attempts to get new data
	 * will be made until the data saved is older than the cache life (in seconds). It defaults to
	 * `3600` (one hour).
	 * `cache_bin` is the sub-directory in the workflow's cache folder where the results are saved.
	 * If `cache_bin` is set to `false` while caching is turned on, then all the results will be saved
	 * directly into the workflow's cache directory.
	 *
	 * Cache files are saved as md5 hashes of the request object. So, if you change anything about the
	 * request, then it will be considered a new cache file. Data is saved to the cache _only_ if
	 * we receive an HTTP response code less than 400.
	 *
	 * My advice is not to touch these options and let the cache work with its default behavior.
	 *
	 * A further note on `cache_bin`: the 'cache_bin' option, if `true`, will create a cache_bin
	 * that is a directory in the cache directory named after the hostname. So if the url is
	 * `http://api.github.com/api....` then the `cache_bin` will be `api.github.com`, and all
	 * cached data will be saved in that directory. Otherwise, if you pass a string, then that will
	 * become the directory it will be saved under.
	 *
	 *
	 * @param string $url     the URL to request
	 * @param array   $options [description]
	 */
	public function __construct(
	  $url,
	  $options = array( 'cache' => true, 'cache_ttl' => 600, 'cache_bin' => true )
	) {

		// Create the cURL handler
		$this->handler = curl_init();
		// Default to `GET`, which is, (not) coincidentally, the cURL default
		$this->object['request_type'] = 'get';

		// Empty parameters array
		$this->object['parameters'] = [];
		// Empty headers array
		$this->headers = [];

		// If the request object was initialized with a URL, then set the URL
		$this->set_url( $url );

		// Set the cache options
		$this->set_caches( $options );

		// Set some reasonable defaults
		curl_setopt_array( $this->handler, [ CURLOPT_RETURNTRANSFER => 1, CURLOPT_FAILONERROR => 1 ]);

	}

	/**
	 * Sets the cache options
	 *
	 * @param array $options an array of cache options
	 */
	private function set_caches( $options ) {
		if ( ! isset( $options['cache_bin' ] ) ) {
			// exit early if no cache bin is set
			return;
		}
		// Here we can automatically set the cache bin to the URL hostname
		if ( true === $options[ 'cache_bin' ] ) {
			$options[ 'cache_bin' ] = parse_url( $this->object['url'], PHP_URL_HOST );
		}

		if ( isset( $options['cache'] ) && $options['cache'] ) {
			if ( isset( $options['cache_ttl'] ) ) {
				$this->cache_life = $options['cache_ttl'];
			}
			if ( isset( $options['cache_bin'] ) ) {
				$this->cache_bin = $options['cache_bin'];
			}
		}
	}


	/**
	 * Executes the cURL request
	 *
	 * If you set `$code` to `true`, then this function will return an associative array as:
	 * ````php
	 * [ 'code' => HTTP_RESPONSE_CODE,
	 *   'data' => RESPONSE_DATA
	 * ];
	 *````
	 * If you get cached data, then the code will be "faked" as a 302, which is appropriate.
	 *
	 * If there is an error, then the code will be 0. So, if you manage to get expired cache
	 * data, then the code will be 0 and there will be data. If there is no expired cache data,
	 * then you will receive an array of `[ 0, false ]`.
	 *
	 * This method does not cache data unless the response code is less than 400. If you need
	 * better data integrity than that, use Guzzle or write your own request library. Or improve
	 * this one by putting in a pull request on the Github repo.
	 *
	 * @param  boolean $code whether or not to return an HTTP response code
	 * @return string|array        the response data, or an array with the code
	 */
	public function execute( $code = false ) {

		// Set a preliminary HTTP response code of 0 (not defined)
		$this->code = 0;

		// Build the fields
		$this->build_fields();

		// By now, the cURL request should be entirely built, so let's see proceed. First, we'll look to see if there
		// is valid, cached data. If so, return that. If not, try to get new data. If that fails, try to return expired
		// cache data. If that fails, then fail.

		if ( isset( $this->cache_life ) ) {
			if ( $data = $this->get_cached_data() ) {
				// Debug-level log message
				Log::console( "Getting the data from cache, aged " . $this->get_cache_age() . " seconds.", 0 );

				// Close the cURL handler for good measure; we don't need it anymore
				curl_close( $this->handler );

				if ( false === $code ) {
					// Just return the data
					return $data;
				} else {
					// They wanted an HTTP code, and we don't have a real one for them because we're getting this
					// from the internal cache, so we'll just fake a 302 response code
					return [ 'code' => 302, 'data' => $data ];
				}
			}
		}

		// Well, we need to actually ask the server for some data, so let us go ahead and make the request
		$this->results = curl_exec( $this->handler );

		// This is the error message.
		$error = curl_error( $this->handler );
		// This is the error number; anything greater than 0 means something went wrong
		$errno = curl_errno( $this->handler );

		// Let's do some error checking on the request now, and then try to execute some fallbacks if there
		// actually was a problem. First, check to make sure that the errno (cURL error code) is 0, which
		// indicates success.
		if ( $errno === 0 ) {
			// The cURL request was successful, so log a debug message of "success"
			Log::console( "cURL query successful.", 0 );
		} else if ( $data = $this->get_cached_data_anyway() ) {
			// Try to get expired cached results....
			// This could work with error code 6 (cannot resolve) because that _could_
			// indicate that we just don't have an internet connection right now.

			// Let the console know we're using old data, with a level of `WARNING`
			Log::console( 'Could not complete request, but, instead, using expired cache data.', 2 );
			// Close the handler
			curl_close( $this->handler );

			if ( false === $code ) {
	   		// Just return the results
				return $data;
			} else {
				// The requested the code as well, so we'll return an array with the code:
				return [ 'code' => 0, 'data' => $data ];
			}
		} else {
			// Let them know what debuggin information follows
			Log::console( 'Request completely failed, and no cached data exists. cURL debug information follows:', 3 );
			// Log the error number (if that helps them)
			Log::console( "cURL error number: {$errno}", 3 );
			// Log the error message
			Log::console( "cURL error message: `{$error}`.", 3 );
			// We might as well close the handler
			curl_close( $this->handler );
			if ( false === $code ) {
				// And let's just return false to get out of this failed function. Alas...
				return false;
			} else {
				// But they also wanted the code, so we'll return 0 for the code
				return [ 'code' => 0, 'data' => false ];
			}
		}

		// Get the information about the last request
		$info = curl_getinfo( $this->handler );
		// This might bug out if connection failed.
		$this->code = $info['http_code'];

		// Close the cURL handler
		curl_close( $this->handler );

		// Cache the data if the cache life is greater than 0 seconds ...AND... the HTTP code is less than 400
		if ( isset( $this->cache_life ) && ( $this->cache_life > 0 ) && ( $this->code < 400 ) ) {
			$this->save_cache_data( $this->results );
		}

		if ( false === $code ) {
   		// Just return the results
			return $this->results;
		} else {
			// The requested the code as well, so we'll return an array with the code:
			return [ 'code' => $this->code, 'data' => $this->results ];
		}
	}

	/**
	 * Builds the post fields array
	 *
	 * @since 1.0.0
	 */
	private function build_post_fields() {
		if ( count( $this->object['parameters'] ) > 0 ) {
			curl_setopt( $this->handler, CURLOPT_POSTFIELDS, http_build_query( $this->object['parameters'] ) );
		}
	}

	/**
	 * Builds the post fields array
	 *
	 * @since 1.0.0
	 */
	private function build_get_fields() {
		// If parameters are set, then append them
		if ( count( $this->object['parameters'] ) > 0 ) {
			// Add the ? that is needed for an appropriate `GET` request, and append the params
			$url = $this->object['url'] . '?' . http_build_query( $this->object['parameters'] );
			// Set the new URL that will include the parameters.
			curl_setopt( $this->handler, CURLOPT_URL, $url );
		}
	}

	/**
	 * Builds the fields out of parameters array
	 *
	 * @since 1.0.0
	 */
	private function build_fields() {
		// If the method is `GET`, then we need to append the parameters to
		// the URL to make sure that it goes alright. Post parameters are included
		// separately and should already be set.
		if ( 'get' == $this->object['request_type'] ) {
			$this->build_get_fields();
		} else if ( 'post' == $this->object['request_type'] ) {
			$this->build_post_fields();
		} else {
			// This should never happen. I mean it. There is no way that I can think of that this exception will be
			// thrown. If it is, then please report it, and send me the code you used to make it happen.
			throw new Exception( "You should never see this, but somehow you are making an unsupported request", 4 );
		}
	}

	/**
	 * Sets basic authorization for a cURL request
	 *
	 * If you need more advanced authorization methods, and if you cannot make them happen with
	 * headers, then use a different library. I recommend Guzzle.
	 *
	 * @since 1.0.0
	 *
	 * @param string $username a username
	 * @param string $password a password
	 */
	public function set_auth( $username, $password ) {
		curl_setopt( $this->handler, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt( $this->handler, CURLOPT_USERPWD, "{$username}:{$password}" );
		$this->object['username'] = $username;
	}

	/**
	 * Sets the URL for the cURL request
	 *
	 * @todo 		Add in custom exception
	 * @since 1.0.0
	 *
	 * @throws 	Exception when $url is not a valid URL
	 * @param 	string $url a valid URL
	 */
	public function set_url( $url ) {
		// Validate the URL to make sure that it is one
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			throw new Exception("The url provided ({$url}) is not valid.");
		}
		curl_setopt( $this->handler, CURLOPT_URL, filter_var( $url, FILTER_SANITIZE_URL ) );
		$this->object['url'] = $url;
	}

	/**
	 * Sets the `user agent` for the cURL request
	 *
	 * @since 1.0.0
	 *
	 * @param string $agent a user agent
	 */
	public function set_user_agent( $agent ) {
		curl_setopt( $this->handler, CURLOPT_USERAGENT, $agent );
		$this->object['agent'] = $agent;
	}

	/**
	 * Sets the headers on a cURL request
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $headers sets extra headers for the cURL request
	 */
	public function set_headers( $headers ) {
		if ( ! is_array( $headers ) ) {
			// Just transform it into an array
			$headers = [ $headers ];
		}
		curl_setopt( $this->handler, CURLOPT_HTTPHEADER, $headers );
		$this->object['headers'] = $headers;

	}

	/**
	 * Adds a header into the headers array
	 *
	 * You can actually pass multiple headers with an array, or just pass a single
	 * header with a string.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $header the header to add
	 */
	public function add_header( $header ) {
		// Check the variable. We expect string, but let's be sure.
		if ( is_string( $header ) ) {
			// Since it's a string, just push it into the headers array.
			array_push( $this->headers, $header );
		} else if ( is_array( $header ) ) {
			// Well, they sent an array, so let's just assume that they want to set
			// multiple headers here.
			foreach( $header as $head ) :
				if ( is_string( $head ) ) {
					// Push each header into the headers array. We can't really check
					// to make sure that these headers are okay or fine. So we'll just
					// have to deal with failure later if they aren't.
					array_push( $this->headers, $head );
				} else {
					// We're going to assume it's an array, so we'll push them together
					// for the error message.
					$head = implode( "|", $head );
					// Bad header. Throw an exception.
					throw new Exception( "You can't push these headers ({$h}).", 4 );
				}
			endforeach;
		} else {
			// Bad header. Throw an exception.
			throw new Exception( "You can't push this header: `{$h}`", 4 );
		}
		// Set the headers in the cURL array.
		$this->set_headers( $this->headers );
	}

  /**
   * Sets the request to use `POST` rather than `GET`
   *
	 * @since 1.0.0
   */
	public function use_post() {
		// Update the curl handler to use post
		curl_setopt( $this->handler, CURLOPT_POST, 1 );
		// Update the internal object to use post
		$this->object['request_type'] = 'post';
	}

	/**
	 * Adds a parameter to the parameters array
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   the name of the parameter
	 * @param string $value the value of the parameter
	 */
	public function add_parameter( $key, $value ) {
		$this->object['parameters'][$key] = $value;
	}

	/**
	 * Adds parameters to the request
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception when passed something other than an array
	 * @param array $params an array of parameters
	 */
	public function add_parameters( $params ) {
		if ( ! is_array( $params ) ) {
			throw new Exception( 'Parameters must be defined as an array', 4 );
		}
		foreach( $params as $key => $value ) :
			$this->add_parameter( $key, $value );
		endforeach;
	}

	/**
	 * Gets cached data
	 *
	 * This method first checks if the cache file exists. If `$ignore_life` is true,
	 * then it will return the data without checking the life. Otherwise, we'll check
	 * to make sure that the `$cache_life` is set. Next, we check the age of the cache.
	 * If any of these fail, then we return false, which indicates we should get new
	 * data. Otherwise, we retrieve the cache.
	 *
	 * @since 1.0.0
	 *
	 * @return string|boolean the data saved in the cache or false
	 */
	private function get_cached_data( $ignore_life = false ) {

		// Does the cache file exist?
		if ( ! file_exists( $this->get_cache_file() ) ) {
			return false;
		}

		// We don't care if the cache is expired. If there is data, give it
		// to us anyway (this is good when there is no internet connection, but
		// we really want the data).
		if ( $ignore_life ) {
			return file_get_contents( $this->get_cache_file() );
		}

		// Is the cache life set?
		if ( ! isset( $this->cache_life ) ) {
			return false;
		}

		// Has the has expired?
		if ( $this->cache_life < $this->get_cache_age() ) {
			return false;
		}

		// Return the contents of the cached file
		return file_get_contents( $this->get_cache_file() );
	}

	/**
	 * Retrieves cached data regardless of cache life
	 *
	 * @since 1.0.0
	 *
	 * @return string|boolean returns the cached data or `false` if none exists
	 */
	private function get_cached_data_anyway() {
		return $this->get_cached_data( true );
	}

	/**
	 * Saves data to a cache file
	 *
	 * @since 1.0.0
	 *
	 * @param  string $data the data to save to the cache
	 */
	private function save_cache_data( $data ) {
		// Make sure that the cache directory exists
		$this->create_cache_dir();

		// Debug-level log message
		\Alphred\Log::log( "Saving cached data to `" . $this->get_cache_file() . "`", 0, 'debug' );

		// Save the data
		file_put_contents( $this->get_cache_file(), $data );
	}

	/**
	 * Creates a cache key based on the request object
	 *
	 * @since 1.0.0
	 *
	 * @return string 	a cache key
	 */
	private function get_cache_key() {
		return md5( json_encode( $this->object ) );
	}

	/**
	 * Returns the file cache
	 *
	 * @since 1.0.0
	 *
	 * @return string the full path to the cache file
	 */
	public function get_cache_file() {
		return $this->get_cache_dir() . '/' . $this->get_cache_key();
	}

	/**
	 * Returns the directory for the cache
	 *
	 * @since 1.0.0
	 *
	 * @return string full path to cache directory
	 */
	private function get_cache_dir() {
		$path = \Alphred\Globals::get( 'alfred_workflow_cache' );
		if ( isset( $this->cache_bin ) ) {
			$path .= '/' . $this->cache_bin;
		}
		return $path;
	}

	/**
	 * Creates a cache directory if it does not exist
	 *
	 * @since 1.0.0
	 *
	 * @throws \Alphred\RunningOutsideOfAlfred when environmental variables are not set
	 * @return boolean success or failure if directory has been made
	 */
	private function create_cache_dir() {
		if ( ! \Alphred\Globals::get( 'alfred_workflow_cache' ) ) {
			throw new \Alphred\RunningOutsideOfAlfred( 'Cache directory unknown', 4 );
		}
		if ( ! file_exists( $this->get_cache_dir() ) ) {
			// Debug-level log message
			\Alphred\Log::log( "Creating cache dir `" . $this->get_cache_dir() . "`", 0, 'debug' );
			return mkdir( $this->get_cache_dir(), 0775, true );
		}
	}

	/**
	 * Gets the age of a cache file
	 *
	 * @since 1.0.0
	 *
	 * @return integer the age of a file in seconds
	 */
	private function get_cache_age() {
		if ( ! file_exists( $this->get_cache_file() ) ) {
			// Cache does not exist
			return false;
		}
		return time() - filemtime( $this->get_cache_file() );
	}

	/**
	 * Clears a cache bin
	 *
	 * Call the file with no arguments if you aren't using a cache bin; however, this
	 * will choke on sub-directories.
	 *
	 * @throws Exception when encountering a sub-directory
	 *
	 * @param  string|boolean $bin the name of the cache bin (or a URL if you're setting them automatically)
	 * @return null
	 */
	public function clear_cache( $bin = false ) {
		// Get the cache directory
		$dir = \Alphred\Globals::cache();
		if ( ! $bin ) {
			return self::clear_directory( $dir );
		}
		if ( filter_var( $bin, FILTER_VALIDATE_URL ) ) {
			$dir = $dir . '/' . parse_url( $bin, PHP_URL_HOST );
		} else {
			$dir = "{$dir}/{$bin}";
		}
		// Clear the directory
		return self::clear_directory( $dir );
	}

	/**
	 * Clears all the files out of a directory
	 *
	 * @since 1.0.0
	 * @throws Exception when encountering a sub-directory
	 *
	 * @param  string $dir a path to a directory
	 */
	private function clear_directory( $dir ) {
		if ( ! file_exists( $dir ) || ! is_dir( $dir ) || '.' === $dir ) {
			// Throw an exception because this is a bad request to clear the cache
			throw new Exception( "Cannot clear directory: `{$dir}`", 3 );
		}

		\Alphred\Log::console( "Clearing cache directory `{$dir}`.", 1 );

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach( $files as $file ) :
			// Do not delete sub-directories
			if ( is_dir( $file ) ) {
				// We could expand this to support deleting sub-directories by just calling this method
				// recursively, but it is better just to use the cache_bin and keep the caches separate.
				throw new Exception( "Cannot delete subdirectory `{$file}` in `{$dir}`", 3 );
			} else {
				// Delete the file
				unlink( "{$dir}/{$file}" );
			}

		endforeach;
	}


	/**********
	 * Old functions
	 **********/

	// public function simple_download( $url, $destination = '', $mkdir = false ) {
	// 	// Function to download a URL easily.
	// 	$url = filter_var( $url, FILTER_SANITIZE_URL );
	// 	if ( empty( $destination ) ) {
	// 		return file_get_contents( $url ); }
	// 	else {
	// 		if ( file_exists( $destination ) && is_dir( $destination ) ) {
	// 			$destination = $destination . '/' . basename( parse_url( $url, PHP_URL_PATH ) );
	// 		}
	// 		file_put_contents( $destination, file_get_contents( $url ) );
	// 	}
	// 	return $destination;
	// }

	// public function get_favicon( $url, $destination = '', $cache = true, $ttl = 604800 ) {
	// 	$url = parse_url( $url );
	// 	$domain = $url['host'];
	// 	if ( $cache && $file = $this->cache( "{$domain}.png", 'favicons', $ttl ) ) {
	// 		return $file;
	// 	}
	// 	$favicon = file_get_contents( "https://www.google.com/s2/favicons?domain={$domain}" );
	// 	if ( empty( $destination ) ) {
	// 		$destination = Globals::get( 'alfred_workflow_cache' ) . '/favicons';
	// 	}
	// 	if ( ! file_exists( $destination ) && substr( $destination, -4 ) !== '.png' ) {
	// 		mkdir( $destination, 0755, true );
	// 	}
	// 	if ( file_exists( $destination ) && is_dir( $desintation ) ) {
	// 		$destination .= "/{$domain}.png";
	// 	}

	// 	file_put_contents( $destination, $favicon );
	// 	return $destination;
	// }


}