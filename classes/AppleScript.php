<?php

namespace Alphred;

// So this file is an absolute mess.
// I might need to rethink if I will / should reorganize all the classes
// into something a bit better....

class AppleScript {

	// Returns the frontmost application and window name
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

	public function activate( $application ) {
		return self::exec(
			'tell application "' . addslashes( $application ) . '" to activate'
		);
	}

	public function bring_to_front( $process ) {
		return self::exec(
			"try\ntell application \"System Events\" to set frontmost of process \"{$process}\" to true\nend try"
		);
	}

	private function exec( $script ) {
		return exec( "osascript -e '{$script}'" );
	}

}