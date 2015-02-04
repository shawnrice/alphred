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

class Notification {

	// The notification will always have the script editor icon on it.
	// Use CocoaDialog for better notifications.
	public function notify( $options ) {
		if ( is_string( $options ) ) {
			exec( "osascript -e 'display notification \"{$options}\"'" );
			return true;
		}
		if ( ! isset( $options['text'] ) ) {
			// throw exception
			return false;
		}

		// These are the available, built-in sounds that you can use
		$sounds = [
				'Basso',
				'Bottle',
				'Funk',
				'Hero',
				'Ping',
				'Purr',
				'Submarine',
				'Blow',
				'Frog',
				'Glass',
				'Morse',
				'Pop',
				'Sosumi',
				'Tink'
		];

		$script = "osascript -e 'display notification \"{$options['text']}\"";
		foreach ( $options as $field => $option ) :
			switch ( $field ) :
				case 'title' :
					$script .= " with title \"{$option}\"";
					break;
			case 'subtitle' :
				$script .= " subtitle \"{$option}\"";
					break;
			case 'sound' :
				if ( in_array( $option, $sounds ) ) {
					$script .= " sound name \"{$option}\"";
				}
				break;
			default:
				break;
			endswitch;
		endforeach;
		$script .= "'";
		exec( $script );
	}
}

// This damn thing is really long and not so awesome.
class Dialog {

	public function __construct( $values = [] ) {
		if ( count( $values ) > 0 ) {
			foreach ( $values as $k => $v ) :
				if ( method_exists( $this, "set_{$k}" ) ) {
					$method = "set_{$k}";
					$this->$method( $v );
				}
			endforeach;
		}

	}

	private function create_dialog() {
		$this->script = "display dialog \"{$this->text}\"";
		if ( isset( $this->buttons_ ) ) {       $this->script .= $this->buttons_; }
		if ( isset( $this->default_answer ) ) { $this->script .= $this->default_answer; }
		if ( isset( $this->title ) ) {          $this->script .= $this->title; }
		if ( isset( $this->icon ) ) {           $this->script .= $this->icon; }
		if ( isset( $this->hidden_answer ) ) {  $this->script .= $this->hidden_answer; }
		if ( isset( $this->cancel ) ) {         $this->script .= $this->cancel; }
		if ( isset( $this->timeout ) ) {        $this->script .= $this->timeout; }
	}

	public function execute() {
		$this->create_dialog();
		$result = exec( "osascript -e '{$this->script}' 2>&1" );

		if ( false !== strpos( $result, ', gave up:false' ) ) {
			$result = str_replace( ', gave up:false', '', $result );
		}
		if ( false !== strpos( $result, 'gave up:true' ) ) {
			return 'timeout';
		}
		if ( false !== strpos( $result, 'execution error: User canceled. (-128)' ) ) {
			return 'canceled';
		}
		if ( false !== strpos( $result, 'text returned:' ) ) {
			return substr( $result, strpos( $result, 'text returned:' ) + 14 );
		}
		return str_replace( 'button returned:', '', $result );
	}

	public function set_icon( $icon ) {
		$default_icons = [ 'stop', 'note', 'caution' ];
		if ( in_array( strtolower( $icon ), $default_icons ) ) {
			$this->icon = ' with icon ' . array_search( $icon, $default_icons );
			return true;
		}
		if ( ! file_exists( realpath( $icon ) ) ) { return false; }
		$icon = str_replace( '/', ':', realpath( $icon ) );
		$this->icon = ' with icon file "' . substr( $icon, 1, strlen( $icon ) - 1 ) . '"';
	}

	public function set_text( $text ) {
		$this->text = addslashes( $text ); // is the addslashes necessary?
	}

	public function set_buttons( $buttons, $default = '' ) {
		if ( empty( $buttons ) ) { return false; }
		$this->buttons = $buttons; // to use later if setting the default.
		if ( is_array( $buttons ) && ( count( $buttons ) > 0 ) ) {
			$this->buttons_ = 'buttons {';
			foreach ( $buttons as $b ) :
				$this->buttons_ .= "\"{$b}\",";
			endforeach;
			$this->buttons_ = substr( $this->buttons_, 0, -1 ) . '}';
		} else if ( is_string( $buttons ) ) {
			$this->buttons_ = " buttons {\"{$buttons}\"}";
		}

		if ( ! empty( $default ) ) {
			$this->set_default_button( $default );
		}
	}

	public function set_default_button( $button ) {
		if ( $default = ( array_search( $button, $this->buttons ) + 1 ) ) {
			$this->buttons_ .= " default button {$default}";
			return true;
		}
		return false;
	}

	public function set_title( $title ) {
		$this->title = " with title \"{$title}\"";
	}

	public function set_default_answer( $text ) {
		$this->default_answer = " default answer \"{$text}\"";
	}

	public function set_timeout( $seconds ) {
		$this->timeout = " giving up after {$seconds}";
	}

	public function set_cancel( $cancel ) {
		$this->cancel = " cancel button \"{$cancel}\"";
	}

	public function set_hidden_answer( $hidden = false ) {
		if ( $hidden ) {
			$this->hidden_answer = ' hidden answer true';
		}
	}

}


/**
 *
 * Creates and executes AppleScript "Choose from..." dialogs. The ones that aren't included
 * can be better served by regular PHP scripting.
 *
 * @since 1.0.0
 *
 */
class Choose {

	/**
	 * Creates and executes a "Choose from list" AppleScript dialog
	 *
	 * @since 1.0.0
	 * @todo Inure this to single-quote, double-quotes, and commas
	 *
	 * @param  array   				$list     An array of list items
	 * @param  array|boolean  $options  An array of options to customize the dialog
	 * @return array          an array of items chosen
	 */
	public function from_list( $list, $options = false ) {
		// Check is the list is an array. If not, return false, because, well, we need
		// an array in order for this thing to work.
		if ( ! is_array( $list ) ) {
			return false;
		}
		// The osascript interpreter needs this to look like an AppleScript list, so
		// convert the PHP array into an AppleScript list.
		$list  = '{"' . implode( '", "', $list ) . '"}';
		// This is what the beginning of the AppleScript needs to look like
		$start = "choose from list {$list}";
		// An array of options. This is basically a translation table in that the keys
		// are the AppleScript text, and the values are the options that can be passed
		// to this function.
		$default_options = [
			'with title'                  => 'title',
			'with prompt'                 => 'text',
			'default items'               => 'default',
			'OK button name'              => 'ok',
			'cancel button name'          => 'cancel',
			'multiple selections allowed' => 'multiple',
			'empty selection allowed'     => 'empty'
		];
		// Send the values to the AppleScript "Choose from..." factory to get some working
		// AppleScript code.
		$script = self::create( $start, $default_options, $options );

		if ( $script ) {
			// Run the script, process the output, and return what is usable
			return self::process( exec( $script ) );
		} else {
			// The script was bad, so return false
			return false;
		}
	}


	/**
	 * Creates and executes a "Choose file(s)" AppleScript dialog
	 *
	 * @since 1.0.0
	 *
	 * @param  array|boolean  $options  An array of options to customize the dialog
	 * @return array           					an array of file path(s)
	 */
	public function file( $options = false ) {
		// This is what the beginning of the AppleScript needs to look like
		$start = 'choose file';
		// An array of options. This is basically a translation table in that the keys
		// are the AppleScript text, and the values are the options that can be passed
		// to this function.
		$default_options = [
			'with prompt'                 => 'text',
			'of type'                     => 'type',
			'default location'            => 'location',
			'invisibles'                  => 'invisibles',
			'multiple selections allowed' => 'multiple',
			'showing package contents'    => 'package_contents'
		];
		// Send the values to the AppleScript "Choose from..." factory to get some working
		// AppleScript code.
		$script = self::create( $start, $default_options, $options );

		if ( $script ) {
			// Run the script, process the output, and return what is usable
			return self::process( exec( $script ), 'alias ', true );
		} else {
			// The script was bad, so return false
			return false;
		}
	}

	/**
	 * Creates and executes a "Choose filename" AppleScript dialog
	 *
	 * @since 1.0.0
	 *
	 * @param  array|boolean  $options  An array of options to customize the dialog
	 * @return array            an array with the single item being a filename
	 */
	public function filename( $options = false ) {
		// This is what the beginning of the AppleScript needs to look like
		$start = 'choose file name';
		// An array of options. This is basically a translation table in that the keys
		// are the AppleScript text, and the values are the options that can be passed
		// to this function.
		$default_options = [
			'with prompt'      => 'text',
			'default name'     => 'default',
			'default location' => 'location'
		];
		// Send the values to the AppleScript "Choose from..." factory to get some working
		// AppleScript code.
		$script = self::create( $start, $default_options, $options );

		if ( $script ) {
			// Run the script, process the output, and return what is usable
			return self::process( exec( $script ), 'file ', true );
		} else {
			// The script was bad, so return false
			return false;
		}
	}


	/**
	 * Creates and executes a "Choose folder(s)" AppleScript dialog
	 *
	 * @since 1.0.0
	 *
	 * @param  array|boolean  $options  An array of options to customize the dialog
	 * @return array          an array of folder path(s)
	 */
	public function folder( $options = false ) {
		// This is what the beginning of the AppleScript needs to look like
		$start = 'choose folder';
		// An array of options. This is basically a translation table in that the keys
		// are the AppleScript text, and the values are the options that can be passed
		// to this function.
		$default_options = [
			'with prompt'                 => 'text',
			'default location'            => 'location',
			'invisibles'                  => 'invisibles',
			'multiple selections allowed' => 'multiple',
			'showing package contents'    => 'package_contents'
		];
		// Send the values to the AppleScript "Choose from..." factory to get some working
		// AppleScript code.
		$script = self::create( $start, $default_options, $options );

		if ( $script ) {
			// Run the script, process the output, and return what is usable
			return self::process( exec( $script ), 'alias ', true );
		} else {
			// The script was bad, so return false
			return false;
		}
	}

	/**
	 * Factory function to create "Choose from..." dialogs.
	 *
	 * This function will take a few arrays and magically create the AppleScript code necessary
	 * for osascript to run a "Choose from..." dialog.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $start      the start of the script
	 * @param  array $options     all possible options
	 * @param  array $selections  the selected options
	 * @return string             the bash command to run an osascript for a "Choose from..." dialog
	 */
	private function create( $start, $options, $selections ) {

		// Are the options defined? If not, then return false
		if ( ! isset( $options ) || ! is_array( $options ) ) {
			return false;
		}

		// The beginning of the script
		$script = "osascript -e '{$start}";

		// Cycle through the options and append to the $script variable
		foreach ( $options as $key => $value ) :
			// The quotes are on
			$quotes = true;
			if ( isset( $selections[ $value ] ) ) {
				if ( is_array( $selections[ $value ] ) ) {
					$selections[ $value ] = '{"' . implode( '", "', $selections[ $value ] ) . '"}';
					$quotes = false;
				}
				if ( is_bool( $selections[ $value ] ) ) {
					if ( $selections[ $value ] ) {
						$selections[ $value ] = 'true';
					} else {
						$selections[ $value ] = 'false';
					}
				}
				// Are the "quotes" open?
				if ( $quotes ) {
					$script .= " {$key} \"{$selections[ $value ]}\"";
				} else {
					$script .= " {$key} {$selections[ $value ]}";
				}
			}
		endforeach;

		// Return the script. Note that we're redirecting STDERR to STDOUT
		return $script .= "' 2>&1";
	}

	/**
	 * Processes the returned text from an AppleScript interaction
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $result 	the text that comes out of the AppleScript interaction
	 * @param  mixed   $strip  	text to strip out of the result
	 * @param  boolean $path   	whether or not the result is a path
	 * @return array            the processed response
	 */
	private function process( $result, $strip = false, $path = false ) {
		// Make sure the user didn't cancel the selection
		if ( false !== strpos( $result, 'execution error: User canceled. (-128)' ) ) {
			// User canceled the operation, so return an explicit "canceled"
			return 'canceled';
		}
		// String out anything we need to strip out
		if ( $strip ) {
			$result = str_replace( $strip, '', $result );
		}
		// Are there commas in the response?
		if ( false !== strpos( $result, ',' ) ) {
			// There are commas, which means we are dealing with an array, so let's
			// explode the values as necessary
			$result = explode( ',', $result );
			// Just trim everything
			array_walk( $result, function( &$value, $key ) {
				$value = trim( $value );
			});
		} else {
			// Make the result an array for consistency
			$result = [ $result ];
		}
		// Are we dealing with a path?
		if ( $path ) {
			// Change all dumb Apple paths to POSIX paths
			array_walk( $result, function( &$value, $key ) {
				$value = self::to_posix_path( $value );
			});
		}
		// Return the usable result
		return $result;
	}

	/**
	 * Converts to a POSIX path
	 *
	 * @since 1.0.0
	 *
	 * @param  string 	$path 	the path
	 * @return string       		the path as POSIX
	 */
	private function to_posix_path( $path ) {
		// Basically, we just need to replace the semi-colons with forward slashses
		return '/' . str_replace( ':', '/', $path );
	}

}