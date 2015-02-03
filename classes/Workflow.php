<?php

/**
 * This is a sort of meta class that constructs the workflow as an object
 *
 * Maybe we can use this to work so that the library auto-updates (optional)
 * We could also have different plugins available if we want...
 */
class Workflow {

	public function init( $options = [] ) {

		// Initialize the workflow variable to be false
		$workflow = false;

		// Parse the INI file
		if ( file_exists( 'workflow.ini' ) ) {
			$workflow = parse_ini_file( 'workflow.ini', true );
		}


	}


}