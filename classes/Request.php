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

	public function __construct( $url = false, $options = array( 'cache' => true, 'cache_life' => 3600 ) ) {

		$this->handler = curl_init();
		$this->object['request_type'] = 'get';

		if ( isset( $options['cache'] ) && $options['cache'] ) {
			if ( isset( $options['cache_life'] ) ) {
				$this->cache_life = $options['cache_life'];
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
				file_put_contents( 'php://stderr', 'Getting the data from the cache, age: ' . $this->get_cache_age() );
				curl_close( $this->handler );
				return $data;
			}
		}

		$this->results = curl_exec( $this->handler );
		curl_close( $this->handler );
		$this->save_cache_data( $this->results );
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


	public function add_user_agent( $agent ) {
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
		if ( ! file_exists( $this->cache_file() ) ) {
			return false;
		}

		// Is the cache life set?
		if ( ! isset( $this->cache_life ) ) {
			return false;
		}

		// Has the has expired?
		if ( $this->cache_life < $this->get_cache_age() ) {
			// Delete the old cached entry
			unlink( $this->cache_file() );
			return false;
		}

		// Return the contents of the cached file
		return file_get_contents( $this->cache_file() );

	}

	private function save_cache_data( $data ) {
		// Make sure that the cache directory exists
		$this->create_cache_dir();
	  file_put_contents( 'php://stderr', 'Saving data to "' . $this->cache_file() . '"' );
		// Save the data
		file_put_contents( $this->cache_file(), $data );
	}

	private function cache_key() {
		return md5( json_encode( $this->object ) );
	}

	/**
	 * [cache_file description]
	 *
	 * @todo Allow for cache bins (basically, sub-folders that are specified)
	 * @return [type] [description]
	 */
	private function cache_file() {
		return \Alphred\Globals::get( 'alfred_workflow_cache' ) . '/' . $this->cache_key();
	}

	private function create_cache_dir() {
		if ( ! \Alphred\Globals::get( 'alfred_workflow_cache' ) ) {
			throw new Exception( "Whoops... trying to get the cache outside of a workflow environment" );
		}
		if ( ! file_exists( \Alphred\Globals::get( 'alfred_workflow_cache' ) ) ) {
			return mkdir( \Alphred\Globals::get( 'alfred_workflow_cache' ), 0775, true );
		}
	}


	private function get_cache_age() {
		if ( ! file_exists( $this->cache_file() ) ) {
			// Cache does not exist
			return false;
		}
		return time() - filemtime( $this->cache_file() );
	}


	// So, what we need to do is to try to find a way to set the parameters and the url
	// together to form a stable cache key that I can then save the data with.
	//



	public function add_parameter( $key, $value ) {
		$this->object['parameters'][$key] = $value;
	}

}