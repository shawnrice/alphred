<?php

// Require the test setup
require_once( 'setup.php' );

class KeychainTest extends \PHPUnit_Framework_TestCase {


	public function test_save_password() {
		Alphred\Keychain::save_password( 'afakeaccount', 'afakepassword' );
		$password = Alphred\Keychain::find_password( 'afakeaccount' );
		$this->assertEquals( 'afakepassword', $password );
		Alphred\Keychain::delete_password( 'afakeaccount' );
		$this->setExpectedException( 'Alphred\PasswordNotFound' );
		Alphred\Keychain::find_password( 'afakeaccount' );
	}

	public function test_empty_account_exception() {
		$this->setExpectedException( 'Alphred\InvalidKeychainAccount' );
		Alphred\Keychain::find_password( ' ' );
	}

	public function test_attempt_delete_whole_keychain() {
		$this->setExpectedException( 'Alphred\InvalidKeychainAccount' );
		Alphred\Keychain::delete_password( ' ' );
	}

	public function test_prevent_constructor() {
		$this->setExpectedException( 'Alphred\UseOnlyAsStatic' );
		$keychain = new Alphred\Keychain;
	}


}