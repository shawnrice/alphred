<?php

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
	 * [__construct description]
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
	 * @param boolean $url     [description]
	 * @param array   $options [description]
	 */
	public function __construct(
	  $url = false,
	  $options = array( 'cache' => true, 'cache_life' => 3600, 'cache_bin' => true )
	) {

		$this->handler = curl_init();
		$this->object['request_type'] = 'get';
		$this->parameters = [];

		// Here we can automatically set the cache bin to the URL hostname
		if ( true == $options[ 'cache_bin' ] && $url ) {
			$options[ 'cache_bin' ] = parse_url( $url, PHP_URL_HOST );
		}

		if ( isset( $options['cache'] ) && $options['cache'] ) {
			if ( isset( $options['cache_life'] ) ) {
				$this->cache_life = $options['cache_life'];
			}
			if ( isset( $options['cache_bin'] ) ) {
				$this->cache_bin = $options['cache_bin'];
			}
		}

		// If the request object was initialized with a URL, then set the URL
		if ( $url ) {
			$this->set_url( $url );
		}

		// Set some reasonable defaults
		curl_setopt_array( $this->handler, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FAILONERROR => 1,
		]);

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

		// If the method is `GET`, then we need to append the parameters to
		// the URL to make sure that it goes alright. Post parameters are included
		// separately and should already be set.
		if ( 'get' == $this->object['request_type'] ) {
			// If parameters are set, then append them
			if ( isset( $this->object['parameters'] ) ) {
				// Add the ? that is needed for an appropriate `GET` request
				$url = $this->object['url'] . '?';
				// Cycle through the set parameters and append them appropriately,
				// separating them with an ampersand.
				foreach ( $this->object['parameters'] as $key => $value ) :
					$url .= urlencode($key) . '=' .  urlencode($value) . "&";
				endforeach;
				// strip off the last ampersand, otherwise, who knows what will happen.
				$url = substr( $url, 0, -1 );
				// Set the new URL that will include the parameters.
				curl_setopt( $this->handler, CURLOPT_URL, $url );
			}
		} else if ( 'post' == $this->object['request_type'] ) {
			if ( count( $this->parameters ) > 0 ) {
				// Build the post fields from the $object->parameters array.
				$this->build_post_fields();
			}
		} else {
			// This should never happen. I mean it. There is no way that I can think of that this exception will be
			// thrown. If it is, then please report it, and send me the code you used to make it happen.
			throw new Exception( "You should never see this, but somehow you are making an unsupported request", 4 );
		}

		// By now, the cURL request should be entirely built, so let's see proceed. First, we'll look to see if there
		// is valid, cached data. If so, return that. If not, try to get new data. If that fails, try to return expired
		// cache data. If that fails, then fail.

		if ( isset( $this->cache_life ) ) {
			if ( $data = $this->get_cached_data() ) {
				// Debug-level log message
				\Alphred\Log::log( "Getting the data from cache, aged " . $this->get_cache_age() . " seconds.", 0, 'debug' );

				// Close the cURL handler for good measure; we don't need it anymore
				curl_close( $this->handler );

				if ( false === $code ) {
					// Just return the data
					return $data;
				} else {
					// They wanted an HTTP code, and we don't have a real one for them because we're getting this
					// from the internal cache, so we'll just fake a 302 response code
					return [
						'code' => 302,
						'data' => $data
					];
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
				return [
					'code' => 0,
					'data' => $data
				];
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
				return [
					'code' => 0,
					'data' => false
				];
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
			return [
				'code' => $this->code,
				'data' => $this->results
			];
		}
	}

	/**
	 * Builds the post fields array
	 *
	 * @return [type] [description]
	 */
	private function build_post_fields() {
		curl_setopt( $this->handler, CURLOPT_POSTFIELDS, http_build_query( $this->parameters ) );
	}


	public function set_auth( $username, $password ) {
		curl_setopt( $this->handler, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt( $this->handler, CURLOPT_USERPWD, "{$username}:{$password}" );
		$this->object['username'] = $username;
	}

	/**
	 * Sets the URL for the cURL request
	 *
	 * @todo 		Add in custom exception
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


	public function set_user_agent( $agent ) {
		curl_setopt( $this->handler, CURLOPT_USERAGENT, $agent );
		$this->object['agent'] = $agent;
	}


	public function set_headers( $headers ) {
		if ( ! is_array( $headers ) ) {
			// Just transform it into an array
			$headers = [ $headers ];
		}
		curl_setopt( $this->handler, CURLOPT_HTTPHEADER, $headers );
		$this->object['headers'] = $headers;

	}

	public function add_header( $header ) {
		// Check the variable. We expect string, but let's be sure.
		if ( is_string( $header ) ) {
			// Since it's a string, just push it into the headers array.
			array_push( $this->headers, $header );
		} else if ( is_array( $header ) ) {
			// Well, they sent an array, so let's just assume that they want to set
			// multiple headers here.
			foreach( $header as $h ) :
				if ( is_string( $h ) ) {
					// Push each header into the headers array. We can't really check
					// to make sure that these headers are okay or fine. So we'll just
					// have to deal with failure later if they aren't.
					array_push( $this->headers, $h );
				} else {
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
   * [set_post description]
   */
	public function use_post() {
		curl_setopt( $this->handler, CURLOPT_POST, 1 );
		$this->object['request_type'] = 'post';
	}

	/**
	 * Revert back to a `GET` request
	 *
	 * Really, you shouldn't ever use this function. I mean, if you do, then
	 * you have fucked up your code royally because we default to `GET`, and
	 * then you set it to `POST` and now you want `GET` again? Seriously? Write
	 * better code. Seriously. I mean it.
	 *
	 * @deprecated deprecated since version 0.0.0
	 *
	 */
	public function use_get() {
		curl_setopt( $this->handler, CURLOPT_POST, 0 );
		curl_setopt( $this->handler, CURLOPT_HTTPGET, 1 );
		$this->object['request_type'] = 'get';
	}

	public function set_opt( $opt, $value ) {
		curl_setopt( $this->handler, $opt, $value );
	}


	/**
	 * [get_cached_data description]
	 * @return [type] [description]
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
			// Yes.... but, we'll expire the cache only if we can get new data
			return false;
		}

		// Return the contents of the cached file
		return file_get_contents( $this->get_cache_file() );
	}

	/**
	 * Retrieves cached data regardless of cache life
	 *
	 * @return [type] [description]
	 */
	private function get_cached_data_anyway() {
		return $this->get_cached_data( true );
	}

	/**
	 * [expire_cache_data description]
	 *
	 * I moved this into its own function, but I'm not yet sure when to call it. Should
	 * we just let the caches exist until forcibly cleared or overwritten?
	 *
	 * @return [type] [description]
	 */
	private function expire_cache_data() {
		// Debug-level log message
		\Alphred\Log::log( "Expiring cache file `" . $this->get_cache_file() . "`", 0, 'debug' );

		// Delete the old cached entry
		unlink( $this->get_cache_file() );
	}

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
	 * @return string 	a cache key
	 */
	private function get_cache_key() {
		return md5( json_encode( $this->object ) );
	}

	/**
	 * Returns the file cache
	 *
	 * @todo Allow for cache bins (basically, sub-folders that are specified)
	 * @return [type] [description]
	 */
	private function get_cache_file() {
		return $this->get_cache_dir() . '/' . $this->get_cache_key();
	}

	private function get_cache_dir() {
		$path = \Alphred\Globals::get( 'alfred_workflow_cache' );
		if ( isset( $this->cache_bin ) && $this->cache_bin ) {
			$path .= '/' . $this->cache_bin;
		}
		return $path;
	}

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

	private function get_cache_age() {
		if ( ! file_exists( $this->get_cache_file() ) ) {
			// Cache does not exist
			return false;
		}
		return time() - filemtime( $this->get_cache_file() );
	}



	public function add_parameter( $key, $value ) {
		$this->object['parameters'][$key] = $value;
	}

	public function add_parameters( $params ) {
		foreach( $params as $key => $val ) :
			$this->add_parameter( $key, $value );
		endforeach;
	}

}