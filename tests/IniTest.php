<?php

// Require the test setup
require_once( 'setup.php' );

class IniTest extends \PHPUnit_Framework_TestCase {

	public function test_all() {
		if ( file_exists( 'workflow-new.ini' ) ) {
			unlink( 'workflow-new.ini' );
		}
		$ini = Alphred\Ini::read_ini( 'workflow.ini' );
		Alphred\Ini::write_ini( $ini, 'workflow-new.ini' );
		$ini2 = Alphred\Ini::read_ini( 'workflow-new.ini' );
		$this->assertEquals( $ini, $ini2 );
		unlink( 'workflow-new.ini' );
	}




}