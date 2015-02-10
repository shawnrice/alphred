<?php

// Require the test setup
require_once( 'setup.php' );

class TextTest extends \PHPUnit_Framework_TestCase {

	/**
	 * [test_title_case description]
	 *
	 * @covers \Alphred\Text::title_case()
	 * @return [type] [description]
	 */
	public function test_title_case() {
		$string = 'A Good and Worthy Test';
		$test_value = Alphred\Text::title_case('a good and worthy test');
		$this->assertSame( $string, $test_value );
	}

	public function test_underscore() {
		$string = 'A_Good_and_Worthy_Test';
		$test_value = Alphred\Text::underscore('A Good and Worthy Test');
		$this->assertSame( $string, $test_value );
	}

	public function test_camel_case() {
		$string = 'aGoodAndWorthyTest';
		$test_value = Alphred\Text::camel_case('A Good and Worthy Test');
		$this->assertSame( $string, $test_value );
	}

	/**
	 * [test_add_commas_to_list description]
	 * @covers \Alphred\Text::add_commas_to_list()
	 * @return [type] [description]
	 */
	public function test_add_commas_to_list() {

		$string = '1, 2, and 3';
		$test_value = Alphred\Text::add_commas_to_list( [1, 2, 3] );
		$this->assertEquals( $string, $test_value );
	}

		/**
	 * [test_add_commas_to_list description]
	 * @covers \Alphred\Text::add_commas_to_list()
	 * @return [type] [description]
	 */
	public function test2_add_commas_to_list() {

		$string = '1 suffix, 2 suffix2, and 3 suffix3';
		$test_value = Alphred\Text::add_commas_to_list( [ 'suffix' => 1, 'suffix2' =>  2, 'suffix3' =>  3], true );
		$this->assertEquals( $string, $test_value );
	}

	public function test_hyphenate() {
		$string = '1-2-3-and-4';
		$test_value = Alphred\Text::hyphenate('1 2 3 and 4');
		$this->assertEquals( $string, $test_value );
	}

}