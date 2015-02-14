<?php

require_once( __DIR__ . '/../../Main.php' );

class NoEnvironmentTest extends \PHPUnit_Framework_TestCase {

	function testFailGlobal() {
		$this->setExpectedException( 'Alphred\RunningOutsideOfAlfred' );
		$data = Alphred\Globals::data();
		print "Data: {$data}\n";
	}

}