<?php

// Require the test setup
require_once( 'setup.php' );

class DateTest extends \PHPUnit_Framework_TestCase {

	// We should add in a few more tests because the functionality is different

	/**
	 * [test_fuzzy_ago description]
	 *
	 * @covers \Alphred\Date::fuzzy_ago()
	 * @covers \Alphred\Date::diff_a_date()
	 * @return [type] [description]
	 */
	public function test_fuzzy_ago() {
		$a_minute_ago = time() - 60;
		$as_a_string = Alphred\Date::fuzzy_ago( $a_minute_ago );
		$this->assertSame( 'a minute ago', $as_a_string );
	}
	public function test2_fuzzy_ago() {
		$a_minute_ago = time() - 86390;
		$as_a_string = Alphred\Date::fuzzy_ago( $a_minute_ago );
		$this->assertSame( 'almost a day ago', $as_a_string );
	}

	/**
	 * @covers \Alphred\Date::ago()
	 * @return [type] [description]
	 */
	public function test_ago() {
		$string = 'one minute ago';
		$test_value = Alphred\Date::ago( time() - 60, true );
		$this->assertEquals( $string, $test_value );
	}
	public function test2_ago() {
		$string = 'in one minute';
		$test_value = Alphred\Date::ago( time() + 60, true );
		$this->assertEquals( $string, $test_value );
	}

	public function test_seconds_to_human_time() {
		$time = 123;
		$as_a_string = '2 minutes and 3 seconds';
		$test_value = Alphred\Date::seconds_to_human_time( $time );
		$this->assertSame( $test_value, $as_a_string );
	}

	public function test_seconds_to_human_time_with_words() {
		$time = 123;
		$as_a_string = 'two minutes and three seconds';
		$test_value = Alphred\Date::seconds_to_human_time( $time, true );
		$this->assertEquals( $test_value, $as_a_string );
	}



}
