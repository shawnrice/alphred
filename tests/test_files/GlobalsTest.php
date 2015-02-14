<?php

// Require the test setup
require_once( 'setup.php' );

/**
 * @covers Alphred\Globals
 */
class GlobalsTest extends \PHPUnit_Framework_TestCase {

	public function test_is_background() {
		$this->assertFalse( Alphred\Globals::is_background() );
	}

	public function test_data() {
		$this->assertEquals( $_SERVER['alfred_workflow_data'], Alphred\Globals::data() );
	}

	public function test_cache() {
		$this->assertEquals( $_SERVER['alfred_workflow_cache'], Alphred\Globals::cache() );
	}

	public function test_bundle() {
		$this->assertEquals( $_SERVER['alfred_workflow_bundleid'], Alphred\Globals::bundle() );
	}

	/**
	 * @covers Alphred\Globals::get
	 */
	public function test_get() {
		$this->assertEquals( $_SERVER['alfred_workflow_bundleid'], Alphred\Globals::get('alfred_workflow_bundleid') );
	}

	public function test_bad_get() {
		$this->assertFalse( Alphred\Globals::get('variablenotset') );
	}

	public function test_construct_exception() {
		$this->setExpectedException( 'Alphred\UseOnlyAsStatic' );
		$exception = new Alphred\Globals;
	}

}