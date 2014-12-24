<?php

namespace Alphred;

/**
 * A class to reteive certain Global variables
 *
 *  Basically, it gives you access
 * to the subset
 *
 * This class was written so that CodeClimate would stop throwing a fit because
 * I was accessing the $_SERVER variable directly.
 *
 */
class Globals {

	/**
	 * Throws an exception if you try to instantiate it
	 *
	 */
	public function __construct() {
		throw new UseOnlyAsStatic( 'The Globals class is to be used statically only.', 1 );
	}

	/**
	 * Retrieves a variable from the $_SERVER global
	 *
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public static function get( $name ) {
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
			'PWD',
			'USER'
		];

		if ( in_array( $name, $variables ) ) {
			return $_SERVER[ $name ];
		} else {
			return false;
		}
	}

	public static function bundle() {
		return Globals::get( 'alfred_workflow_bundleid' );
	}

}