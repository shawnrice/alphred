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

	public function test_scriptfilter() {
		$Alphred = new Alphred([ 'error_on_empty' => true ]);
		$Alphred->add_result([ 'title' => 'This is a title' ]);
		$Alphred->to_xml();
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

	public function test_keychain2() {

		$Alphred = new Alphred;
		$account  = 'averytestaccount';
		$password = 'test';
		// $this->setExpectedException( 'Alphred\PasswordNotFound' );
		$this->assertFalse( $Alphred->get_password( $account ) );
	}

	public function test_keychain3() {

		$Alphred = new Alphred;
		$account  = 'averytestaccount';
		$password = 'test';
		$test_password = $Alphred->get_password( $account, false );
		$this->assertFalse( $test_password );
		$this->assertEquals( $Alphred->get_password_dialog("Please enter: `{$password}`", 'title', 'note'), $password);
		$this->assertEquals( $Alphred->get_password_dialog(), $password);
		$Alphred->save_password( $account, $password );
		$this->assertEquals( $password, $Alphred->get_password( $account ) );
		$Alphred->delete_password( $account );
		$this->assertFalse( $Alphred->get_password( $account ) );
	}

	public function test_notify() {
		$Alphred = new Alphred;
		$Alphred->send_notification(['text' => 'This is a test notification', 'title' => 'Test Notification' ]);
	}

	public function test_get() {
		$Alphred = new Alphred;
		// $url, $options = false, $cache_ttl = 600, $cache_bin = true
		$options['user_agent'] = 'agent';
		$options['params'] = [ 'test' => 'what' ];
		$options['headers'] = [ 'one', 'two' ];
		$Alphred->get( 'http://localhost:8888', $options );

	}

	public function test_post() {
		$Alphred = new Alphred;
		$options = [];
		$options['user_agent'] = 'agent';
		$options['params'] = [ 'test' => 'what' ];
		$options['auth'] = [ 'username', 'password' ];
		$response = $Alphred->post( 'http://localhost:8888', $options, 0, 'something' );
		// Do it again to get the cache
		$response = $Alphred->post( 'http://localhost:8888', $options, 0, 'something' );
		$response = json_decode( $response, true );
		$this->assertTrue( is_array( $response ) );
		$this->assertTrue( $response['test'] == 'what' );

	}

	public function test_get_bad_params() {
		$this->setExpectedException( 'Alphred\Exception' );
		$Alphred = new Alphred;
		$options = [];
		$options['params'] = 'this should be an array';
		$Alphred->post( 'http://localhost:8888', $options, 0, 'something' );
	}

	public function test_get_bad_user_agent() {
		$this->setExpectedException( 'Alphred\Exception' );
		$Alphred = new Alphred;
		$options = [];
		$options['user_agent'] = ['agent'];
		$Alphred->post( 'http://localhost:8888', $options, 0, 'something' );
	}

	public function test_get_bad_headers() {
		$this->setExpectedException( 'Alphred\Exception' );
		$Alphred = new Alphred;
		$options = [];
		$options['headers'] = 'testing';
		$Alphred->post( 'http://localhost:8888', $options, 0, 'something' );
	}

	public function test_get_bad_auth() {
		$this->setExpectedException( 'Alphred\Exception' );
		$Alphred = new Alphred;
		$options = [];
		$options['auth'] = ['testing'];
		$Alphred->post( 'http://localhost:8888', $options, 0, 'something' );
	}

	public function test_bad_url() {
		$this->setExpectedException( 'Alphred\Exception' );
		$Alphred = new Alphred;
		$Alphred->get( 'badurl' );
	}

	public function test_log_console() {
		$Alphred = new Alphred;
		$Alphred->console( 'testing' );
	}

	public function test_log_file() {
		$Alphred = new Alphred;
		$Alphred->log( 'testing' );
		$Alphred->log( 'testing', 4 );
		$Alphred->log( 'testing', 4, 'a_unique_file', -1 );
	}

	public function test_activate() {
		$Alphred = new Alphred;
		$Alphred->activate( 'Google Chrome' );
		sleep(1);
		$front = $Alphred->get_active_window();
		$this->assertEquals( 'Google Chrome', $front['app'] );
		$Alphred->bring_to_front( 'iTerm' );
		sleep(1);
		$front = $Alphred->get_active_window();
		$this->assertEquals( 'iTerm', $front['app'] );

	}

	public function test_time_ago() {
		$Alphred = new Alphred;
		$result = $Alphred->time_ago( time() - 1 );
		$this->assertEquals( '1 second ago', $result );
		$result = $Alphred->time_ago( time() - 1, true );
		$this->assertEquals( 'one second ago', $result );
	}

	public function test_filter() {
		$Alphred = new Alphred;
		$array = [ 'uber', 'uber2' ];
		$results = $Alphred->filter( $array, 'ub' );
		$this->assertEquals( $array, $results );
		$results = $Alphred->filter( $array, '2', false, [ 'flags' => 2 ] );
		$this->assertEquals( $results, [ 'uber2' ] );
	}

	public function test_empty_filter() {
		$Alphred = new Alphred;
		$array = [ 'uber', 'uber2' ];
		$results = $Alphred->filter( $array, '' );
		$this->assertEquals( $array, $results );

	}

	public function test_get_filter() {
		$Alphred = new Alphred;
		// $options =
		// $response =
	}

	public function test_trigger() {
		$Alphred = new Alphred;
		$Alphred->call_external_trigger( Alphred\Globals::bundle(), 'test', 'test_value' );
		sleep(1);
		$this->assertEquals( 'test_value', trim( file_get_contents( Alphred\Globals::data() . '/test_external_trigger.txt' ) ) );
	}
}