<?php
/**
 * Contains Globals class for Alphred
 *
 * PHP version 5
 *
 * @package 	 Alphred
 * @copyright  Shawn Patrick Rice 2014
 * @license    http://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @author     Shawn Patrick Rice <rice@shawnrice.org>
 * @link       http://www.github.com/shawnrice/alphred
 * @link       http://shawnrice.github.io/alphred
 * @since      File available since Release 1.0.0
 *
 */

namespace Alphred;

/**
 * A class to reteive certain Global variables
 *
 * Basically, it gives you access to the subset of variables.
 *
 * This class was written so that CodeClimate would stop throwing a fit because
 * I was accessing the $_SERVER variable directly.
 *
 */
class Globals {

		/**
		 * An array of Global variables that can be accessed
		 *
		 * @since 1.0.0
		 *
		 * @var array
		 */
		private static $variables = [
			'alfred_theme_background',
			'alfred_theme_subtext',
			'alfred_version',
			'alfred_version_build',
			'alfred_workflow_bundleid',
			'alfred_workflow_cache',
			'alfred_workflow_data',
			'alfred_workflow_name',
			'alfred_workflow_uid',
			'ALPHRED_RUNNING_IN_BACKGROUND', // This is internal for background awareness
			'PWD',
			'USER'
		];

	/**
	 * Throws an exception if you try to instantiate it
	 *
	 * @throws \Alphred\UseOnlyAsStatic if you try to institate a Globals object
	 */
	public function __construct() {
		throw new UseOnlyAsStatic( 'The Globals class is to be used statically only.', 1 );
	}

	/**
	 * Retrieves a variable from the global $_SERVER array
	 *
	 * @since 1.0.0
	 * @throws \Alphred\RunningOutsideOfAlfred
	 *
	 * @param  string $name 	name of the variable
	 * @return string         value of the variable
	 */
	public static function get( $name ) {
		// Check if the variable is in the appropriate array
		if ( in_array( $name, self::$variables ) ) {
			// If the variable is set, then return it
			if ( isset( $_SERVER[ $name ] ) ) {
				return $_SERVER[ $name ];
			}
			// Special case for 'running in background': we do the workflow environment check otherwise,
			// so we'll just return false if not set.
			if ( 'ALPHRED_RUNNING_IN_BACKGROUND' == $name ) {
				return false;
			}
			// The variable is not set, so we'll throw an exception
			throw new RunningOutsideOfAlfred( 'The Globals can be accessed only within a workflow environment.', 4 );
		} else {
			// Should this be an exception?
			return false;
		}
	}

	/**
	 * Retrieves the bundle id of the workflow from the global $_SERVER array
	 *
	 * @since 1.0.0
	 *
	 * @return string 	the bundle id of the running workflow
	 */
	public static function bundle() {
		return self::get( 'alfred_workflow_bundleid' );
	}

	/**
	 * Retrieves the data directory of the running workflow
	 *
	 * @since 1.0.0
	 *
	 * @return string path to the workflow's data directory
	 */
	public static function data() {
		return self::get( 'alfred_workflow_data' );
	}

	/**
	 * Retrieves the cache directory of the running workflow
	 *
	 * @since 1.0.0
	 *
	 * @return string path to the workflow's cache directory
	 */
	public static function cache() {
		return self::get( 'alfred_workflow_cache' );
	}

	/**
	 * Checks if the script is running in the background
	 *
	 * This is aware __only__ of a script running in the background if
	 * it was launched by the Alphred wrapper's background() method.
	 *
	 * @since 1.0.0
	 * @see \Alphred::background() To see how to launch a background script.
	 *
	 * @return boolean [description]
	 */
	public static function is_background() {
		// This will trigger an exception if running outside of Alfred
		self::get( 'alfred_workflow_data' );
		if ( self::get( 'ALPHRED_RUNNING_IN_BACKGROUND' ) ) {
			return true;
		}
		// The variable is not set, but we're running inside of a workflow
		// environment, so that mean we aren't in the background, so return
		// false
		return false;
	}

}