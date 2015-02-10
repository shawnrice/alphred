<?php

// Require the test setup
require_once( 'setup.php' );

class ConfigTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$data = Alphred\Globals::data();
		if ( file_exists( "{$data}/test.json" ) ) {
			unlink( "{$data}/test.json" );
		}
	}

	public function test_set_json() {
		$config = new Alphred\Config( 'json', 'test' );
		$config->set( 'username', 'shawnrice' );
		$this->assertEquals( 'shawnrice', $config->read( 'username' ) );
	}

	public function test_unset_json() {
		$config = new Alphred\Config( 'json', 'test' );
		$config->set( 'username', 'shawnrice' );
		$this->assertEquals( 'shawnrice', $config->read( 'username' ) );
		$config->delete( 'username' );
		$this->setExpectedException( 'Alphred\ConfigKeyNotSet' );
		$config->read( 'username' );
	}

	public function test_assert_not_set_exception_json() {
		$this->setExpectedException( 'Alphred\ConfigKeyNotSet' );
		$config = new Alphred\Config( 'json', 'test' );
		$config->read( 'somekeynotset' );
	}


	public function test_set_ini() {
		$config = new Alphred\Config( 'ini', 'test' );
		$config->set( 'username', 'shawnrice' );
		$this->assertEquals( 'shawnrice', $config->read( 'username' ) );
	}

	public function test_unset_ini() {
		$config = new Alphred\Config( 'ini', 'test' );
		$config->set( 'username', 'shawnrice' );
		$this->assertEquals( 'shawnrice', $config->read( 'username' ) );
		$config->delete( 'username' );
		$this->setExpectedException( 'Alphred\ConfigKeyNotSet' );
		$config->read( 'username' );
	}

	public function test_assert_not_set_exception_ini() {
		$this->setExpectedException( 'Alphred\ConfigKeyNotSet' );
		$config = new Alphred\Config( 'ini', 'test' );
		$config->read( 'somekeynotset' );
	}

	public function test_set_sqlite() {
		$config = new Alphred\Config( 'sqlite', 'test' );
		$config->set( 'username', 'shawnrice' );
		$this->assertEquals( 'shawnrice', $config->read( 'username' ) );
	}

	public function test_unset_sqlite() {
		$config = new Alphred\Config( 'sqlite', 'test' );
		$config->set( 'username', 'shawnrice' );
		$this->assertEquals( 'shawnrice', $config->read( 'username' ) );
		$config->delete( 'username' );
		$this->setExpectedException( 'Alphred\ConfigKeyNotSet' );
		$config->read( 'username' );
	}

	public function test_assert_not_set_exception_sqlite() {
		$this->setExpectedException( 'Alphred\ConfigKeyNotSet' );
		$config = new Alphred\Config( 'sqlite', 'test' );
		$config->read( 'somekeynotset' );
	}


	public function test_bad_handler() {
		$this->setExpectedException( 'Alphred\Exception' );
		$config = new Alphred\Config( 'handlerdoesnotexist' );
	}

}