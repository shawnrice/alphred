<?php

// Require the test setup
require_once( 'setup.php' );

class NotificationTest extends \PHPUnit_Framework_TestCase {

	public function test_notify() {
		Alphred\Notification::notify(['text' => 'This is a test notification', 'title' => 'Test Notification' ] );
	}

}