<?php

require_once( __DIR__ . '/../../Main.php' );

class NoEnvironmentTest extends \PHPUnit_Framework_TestCase {

	function setUp() {
		$variables = [
			'alfred_theme_background',
			'alfred_theme_subtext',
			'alfred_version',
			'alfred_version_build',
			'alfred_workflow_bundleid',
			'alfred_workflow_cache',
			'alfred_workflow_data',
			'alfred_workflow_name',
			'alfred_workflow_uid',
			'ALPHRED_IN_BACKGROUND', // This is internal for background awareness
		];
		foreach( $variables as $variable ) :
			if ( isset( $_SERVER[ $variable ] ) ) {
				unset( $_SERVER[ $variable ] );
			}
		endforeach;
	}

	function testFailGlobal() {
		$this->setExpectedException( 'Alphred\RunningOutsideOfAlfred' );
		$data = Alphred\Globals::data();
		print "Data: {$data}\n";
	}

}