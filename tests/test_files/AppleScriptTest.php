<?php

// Require the test setup
require_once( 'setup.php' );

class AppleScriptTest extends \PHPUnit_Framework_TestCase {

	public function test_activate() {

		Alphred\Applescript::activate( 'Google Chrome' );
		sleep(1);
		$front = Alphred\Applescript::get_front();
		$this->assertEquals( 'Google Chrome', $front['app'] );
		Alphred\Applescript::bring_to_front( 'iTerm' );
		sleep(1);
		$front = Alphred\Applescript::get_front();
		$this->assertEquals( 'iTerm', $front['app'] );

	}

}