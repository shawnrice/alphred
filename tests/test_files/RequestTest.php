<?php

// Require the test setup
require_once( 'setup.php' );

// Note, this tests the requests against a local server that's run with the php cli binary. It's setup to
// give certain responses. The script `test_runner.sh` in the root of the repo launches and kills it for
// testing purposes.

class RequestTest extends \PHPUnit_Framework_TestCase {

	private $base_url = 'http://localhost:8888';

	public function test_get() {
		// $request = new Alphred\Request( $this->base_url );
		// $results = $request->execute();
		// Alphred\Log::console( print_r( $results, true ) );
		// $this->assertTrue( is_array( json_decode( $results, true ) ) );
	}

}