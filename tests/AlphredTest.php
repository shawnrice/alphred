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

	public function test_filter() {
		$Alphred = new Alphred([ 'error_on_empty' => true ]);
		$Alphred->add_result([ 'title' => 'This is a title' ]);
		$Alphred->print_results();
	}

	/**
	 * @covers Alphred::config_read()
	 * @covers Alphred::config_delete()
	 * @covers Alphred::config_set()
	 */
	public function test_config() {
		$Alphred = new Alphred([ 'error_on_empty' => true ]);
		$username = 'shawn';
		$Alphred->config_set( 'username', $username );
		$this->assertEquals( $Alphred->config_read( 'username' ), $username );
		$Alphred->config_delete( 'username' );
		$this->assertNull( $Alphred->config_read( 'username' ) );
	}

	public function test_keychain() {

		$Alphred = new Alphred;
		$account  = 'averytestaccount';
		$password = 'test';
		$this->setExpectedException( 'Alphred\PasswordNotFound' );
		$Alphred->delete_password( $account );
		$this->setExpectedException( 'Alphred\PasswordNotFound' );
		$test_password = $Alphred->get_password( $account );
		$this->assertFalse( $test_password );
		$this->assertEquals( $Alphred->get_password_dialog("Please enter: `{$password}`"), $password);
		$Alphred->save_password( $account, $password );
		$this->assertEquals( $password, $Alphred->get_password( $account ) );
		$Alphred->delete_password( $account );
		$this->setExpectedException( 'Alphred\PasswordNotFound' );
		$Alphred->get_password( $account );
	}

}