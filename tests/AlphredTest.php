<?php

// Require the test setup
require_once( 'setup.php' );

class AlphredTest extends \PHPUnit_Framework_TestCase {


	public function setUp() {

	}

	/**
	 * @covers \Alphred::__construct()
	 * @return [type] [description]
	 */
	public function test__construct() {
		$Alphred = new Alphred;
		$this->assertTrue( is_object( $Alphred ) && ( 'Alphred' == get_class( $Alphred ) ) );
	}

	public function test_fuzzy_ago() {
		$string = 'yesterday';
		$Alphred = new Alphred;
		$test_value = $Alphred->fuzzy_time_diff( time() - 60000 );
		$this->assertEquals( $string, $test_value );
	}

	public function test_add_commas() {
		$string = '1, 2, and 3';
		$Alphred = new Alphred;
		$test_value = $Alphred->add_commas( [1, 2, 3] );
		$this->assertEquals( $string, $test_value );
	}

}