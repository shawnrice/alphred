<?php
/**
 * Contains Alfred class for Alphred, a class to work with some Alfred stuff
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

class Alfred {

	/**
	 * Calls an Alfred External Trigger
	 *
	 * Single and double-quotes in the argument might break this method, so make sure that you
	 * escape them appropriately.
	 *
	 * @since 1.0.0
	 *
	 * @param  string  				$bundle   the bundle id of the workflow to trigger
	 * @param  string  				$trigger  the name of the trigger
	 * @param  string|boolean $argument an argument to pass
	 */
	public static function call_external_trigger( $bundle, $trigger, $argument = false ) {
		$script = "tell application \"Alfred 2\" to run trigger \"{$trigger}\" in workflow \"{$bundle}\"";
		if ( false !== $argument ) {
			$script .= "with argument \"{$argument}\"";
		}
		// Execute the AppleScript to call the trigger
		exec( "osascript -e '$script'" );
	}

	/**
	 * Tells you if the current theme is `light` or `dark`
	 *
	 * @uses Alphred\Globals::get()
	 * @return string either 'light' or 'dark'
	 */
  public static function light_or_dark() {
    // Regex pattern to parse the Alfred background variable
    $pattern = "/rgba\(([0-9]{1,3}),([0-9]{1,3}),([0-9]{1,3}),([0-9.]{4,})\)/";
    // Do the regex, matching everything in the $matches variable
    preg_match_all( $pattern, \Alphred\Globals::get( 'alfred_theme_background' ), $matches );
    // Pull the values into an $rgb array
    $rgb = array( 'r' => $matches[1][0], 'g' => $matches[2][0], 'b' => $matches[3][0] );

    // This calculates the luminance. Values are between 0 and 1.
    $luminance = ( 0.299 * $rgb[ 'r' ] + 0.587 * $rgb[ 'g' ] + 0.114 * $rgb[ 'b' ] ) / 255;

    if ( 0.5 < $luminance ) {
        return 'light';
    }
    return 'dark';
  }

}