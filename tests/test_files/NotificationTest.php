<?php

// Require the test setup
require_once( 'setup.php' );

class NotificationTest extends \PHPUnit_Framework_TestCase {

	public function test_notify() {
		Alphred\Notification::notify([
			'text' => 'This is a test notification',
			'title' => 'Test Notification',
			'sound' => 'Purr',
			'subtitle' => 'this is a subtitle'
		]);
	}

	public function test_notify2() {
		Alphred\Notification::notify( 'This is a test notification' );
	}

}
