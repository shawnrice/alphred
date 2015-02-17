<?php
/**
 * Contains AppleScript class for Alphred, just some php wrappers around some AppleScript
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
 * Provides limited functionality to AppleScript
 *
 * Think of this as a wrapper for some AppleScript
 */
class AppleScript {

	/**
	 * Gets the frontmost window name and application
	 *
	 * @return array an array with the front application name and window name
	 */
	public function get_front() {
		// This is just inelegantly embedding a long AppleScript into the library
		// https://stackoverflow.com/questions/5292204/macosx-get-foremost-window-title
		$script = '
		global frontApp, frontAppName, windowTitle
		set windowTitle to ""
		tell application "System Events"
			set frontApp to first application process whose frontmost is true
			set frontAppName to name of frontApp
			tell process frontAppName
				tell (1st window whose value of attribute "AXMain" is true)
					set windowTitle to value of attribute "AXTitle"
				end tell
			end tell
		end tell
		return {frontAppName, windowTitle}';
		$result = self::exec( $script );
		return [
			'app'    => substr( $result, 0, strpos( $result, ', ' ) ),
			'window' => substr( $result, strpos( $result, ', ' ) + 2 )
		];

	}

	/**
	 * Brings an application to the front, opening it if necessary
	 *
	 * @param  string $application the name of the application
	 */
	public function activate( $application ) {
		return self::exec(
			'tell application "' . addslashes( $application ) . '" to activate'
		);
	}

	/**
	 * Brings an application to the front, but only if it is open
	 *
	 * @param  string $process name of application
	 */
	public function bring_to_front( $process ) {
		return self::exec(
			"try\ntell application \"System Events\" to set frontmost of process \"{$process}\" to true\nend try"
		);
	}

	/**
	 * Executes some AppleScript code
	 *
	 * @param  string $script the script to execute
	 * @return mixed          whatever the script returns
	 */
	private function exec( $script ) {
		return exec( "osascript -e '{$script}'" );
	}

}