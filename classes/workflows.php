<?php

namespace Alphred;

class Workflows {

	public function __construct() {
		if ( ! isset( Globals::get( 'alfred_workflow_data' ) ) ) {
			// should throw an exception
			return false;
		}
		$this->map_file = Globals::get( 'alfred_workflow_data' ) . '/workflow_map.json';
	}

	public function find( $bundle ) {
		$base = Globals::get( 'PWD' );
		if ( ! file_exists( $this->map_file ) ) {
			$this->map();
		}
		$workflows = json_decode( file_get_contents( $this->map_file ), true );
		if ( isset( $workflows['bundle'] ) ) {
			return "{$base}/{$workflows['bundle']}";
		}
		return false;
	}

	public function map() {
		$wfs = array_diff( scandir( '..' ),  [ '.', '..', '.DS_Store' ] );
		$PlistBuddy = '/usr/libexec/PlistBuddy';
		$workflows = [];
		foreach ( $wfs as $workflow ) :
			if ( 0 === strpos( $workflow, 'user.workflow.' ) ) {
				if ( ! file_exists( "{$workflow}/info.plist" ) ) { continue; }
				// I need to alter this to protect from errors
				$bundle = exec( "{$PlistBuddy} -c 'print :bundleid' '{$workflow}/info.plist'" );
				$name   = exec( "{$PlistBuddy} -c 'print :name' '{$workflow}/info.plist'" );
				$uid    = $workflow;
				$workflows[ $bundle ] = array(
					'bundle' => $bundle,
					'name'   => $name,
					'dir'    => $workflow,
				);
			}
		endforeach;

		file_put_contents( $this->map_file, json_encode( $workflows, true ) );
	}

}