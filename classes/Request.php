<?php

namespace Alphred;

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

	public function __construct( $url = false, $options = array( 'cache' => true, 'cache_life' => 3600, 'cache_bin' => true ) ) {

		$this->handler = curl_init();
		$this->object['request_type'] = 'get';

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

	public function execute() {
		if ( 'get' == $this->object['request_type'] ) {
			if ( isset( $this->object['parameters'] ) ) {
				$url = $this->object['url'] . '?';
				foreach ( $this->object['parameters'] as $key => $value ) :
					$url .= urlencode($key) . '=' .  urlencode($value) . "&";
				endforeach;
				$url = substr( $url, 0, -1 ); // strip off the last ampersand
				// Set the new URL that will include the parameters.
				curl_setopt( $this->handler, CURLOPT_URL, $url );
			}
		}
		if ( isset( $this->cache_life ) ) {
			if ( $data = $this->get_cached_data() ) {
				// Debug-level log message
				\Alphred\Log::log( "Getting the data from cache, aged " . $this->get_cache_age() . " seconds.", 0, 'debug' );

				// Close the cURL handler for good measure
				curl_close( $this->handler );

				// Return the data
				return $data;
			}
		}

		$this->results = curl_exec( $this->handler );
		curl_close( $this->handler );

		// Cache the data if the cache life is greater than 0 seconds
		if ( isset( $this->cache_life ) && ( $this->cache_life > 0 ) ) {
			$this->save_cache_data( $this->results );
		}

		return $this->results;

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

	/**
	 * Adds a header in the CURLOPT_HTTPHEADER...
	 * @todo this does nothing for now. I need to find out if there is a way to get the
	 *       headers that are already set and add one to the array rather than overwriting
	 *       them.
	 */
	public function add_header( ) {

	}

  /**
   * [set_post description]
   */
	public function use_post() {
		curl_setopt( $this->handler, CURLOPT_POST, 1 );
		$this->object['request_type'] = 'post';
	}

	/**
	 * [set_get description]
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
	private function get_cached_data() {

		// Does the cache file exist?
		if ( ! file_exists( $this->get_cache_file() ) ) {
			return false;
		}

		// Is the cache life set?
		if ( ! isset( $this->cache_life ) ) {
			return false;
		}

		// Has the has expired?
		if ( $this->cache_life < $this->get_cache_age() ) {

			// Debug-level log message
			\Alphred\Log::log( "Expiring cache file `" . $this->get_cache_file() . "`", 0, 'debug' );

			// Delete the old cached entry
			unlink( $this->get_cache_file() );
			return false;
		}

		// Return the contents of the cached file
		return file_get_contents( $this->get_cache_file() );

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

}