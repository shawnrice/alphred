<?php

// Require the test setup
require_once( 'setup.php' );

class ScriptFilterTest extends \PHPUnit_Framework_TestCase {

	public function test_get_results() {
		$filter = new Alphred\ScriptFilter;
		$filter->add_result(new Alphred\Result(['title' => 'this is a title', 'subtitle' => 'this is a subtitle', 'valid' => true ]));
		$results = count( $filter->get_results() );
		$this->assertEquals( 1, $results );
	}

}