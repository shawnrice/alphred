<?php

// Require the test setup
require_once( 'setup.php' );

class IniTest extends \PHPUnit_Framework_TestCase {

	public function test_all() {
		$new = __DIR__ . '/test-ini.ini';
		$old = __DIR__ . '/resources/test.ini';
		if ( file_exists( $new ) ) {
			unlink( $new );
		}
		$ini = Alphred\Ini::read_ini( $old );
		Alphred\Ini::write_ini( $ini, $new );
		$ini2 = Alphred\Ini::read_ini( $new );
		$this->assertEquals( $ini, $ini2 );
		unlink( $new );
	}

	public function test_file_not_found() {
		$this->assertFalse( Alphred\Ini::read_ini( 'thisfiledoesnotexist.ini', false ) );
		$this->setExpectedException( 'Alphred\FileDoesNotExist' );
		Alphred\Ini::read_ini( 'thisfiledoesnotexist.ini' );
	}




}