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
 * @todo Migrate all Alfred environmental variables. Must be done in coordination
 *       with server.sh
 *
 * @todo Make available, somehow, through the PHAR package (do I need to implement
 *       this as a function to do so?)
 *
 */

if ( 'cli-server' === php_sapi_name() ) {
		if ( isset( $_POST['query'] ) ) {
			$argv[1]                             = $_POST['query'];
		}

		foreach ( [ 'alfred_workflow_bundleid', 'alfred_workflow_bundleid', 'alfred_workflow_cache' ] as $var ) :
			if ( isset( $_POST[ $var ] ) ) {
				$_SERVER[ $var ] = $_POST[ $var ];
			}
		endforeach;
}

