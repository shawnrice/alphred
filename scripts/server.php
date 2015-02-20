<?php
/**
 * Include file for when using Alphred through the cli-server SAPI
 *
 * If you write your workflow so that it takes advatage of the cli-server
 * made easier via Alphred, then just include this file near the top of
 * each script you use to query.
 *
 * Simply put, this file just routes certain global variables passed via a
 * POST request into the $_SERVER variable, which is where you expect to
 * find them when invoked regularly from the command line.
 *
 *
 * PHP version 5
 *
 * @package    Alphred
 * @subpackage Scripts
 * @copyright  Shawn Patrick Rice 2014
 * @license    http://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @author     Shawn Patrick Rice <rice@shawnrice.org>
 * @link       http://www.github.com/shawnrice/alphred
 * @link       http://shawnrice.github.io/alphred
 * @since      File available since Release 1.0.0
 *
 *
 *
 */

if ( 'cli-server' === php_sapi_name() ) {

	// If the `query` variable was set, then set it to $argv[1]
	if ( isset( $_POST['query'] ) ) {
		$argv[1] = urldecode( $_POST['query'] );
	}

	// These are all the potential global variables that can be set
	// by Alfred, and one extra from Alphred.
	$alfred_global_vars = [
		'alfred_preferences',
		'alfred_preferences_localhash',
		'alfred_theme',
		'alfred_theme_background',
		'alfred_theme_subtext',
		'alfred_version',
		'alfred_version_build',
		'alfred_workflow_bundleid',
		'alfred_workflow_cache',
		'alfred_workflow_data',
		'alfred_workflow_name',
		'alfred_workflow_uid',
		'ALPHRED_IN_BACKGROUND',
	];

	// Cycle through all the global variables and set them if already set
	foreach ( $alfred_global_vars as $var ) :
		if ( isset( $_POST[ $var ] ) ) {
			$_SERVER[ $var ] = $_POST[ $var ];
		}
	endforeach;
}

