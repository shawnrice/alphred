<?php

// Require the test setup
require_once( 'setup.php' );

class ScriptFilterTest extends \PHPUnit_Framework_TestCase {

	public function test_get_results() {
		$filter = new Alphred\ScriptFilter;
		$filter->add_result(new Alphred\Result(['title' => 'this is a title', 'subtitle' => 'this is a subtitle', 'valid' => true ]));
		$results = count( $filter->get_results() );
		$this->assertEquals( 1, $results );
		$filter->print_results();
	}

	/**
	* @expectedException PHPUnit_Framework_Error
	* @expectedExceptionMessage  Argument 1 passed to Alphred\ScriptFilter::add_result() must be an instance of Alphred\Result, string given, called in /Users/Sven/Documents/Alfred2/alphred/tests/ScriptFilterTest.php on line 23 and defined
	*/
	public function test_empty() {
		$filter = new Alphred\ScriptFilter(['error_on_empty' => true, 'i18n' => true ] );
		$filter->to_xml();
		$filter->add_result('test');
	}

}