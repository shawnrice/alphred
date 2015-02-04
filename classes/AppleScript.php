<?php

namespace Alphred\AppleScript;

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


// This is a generic class that creates Choose... AppleScript dialogs
class Choose {
	/**
	 * Each of the public functions corresponds to a type of AppleScript "Chose..." dialog.
	 *
	 */


	// NOTE: THESE FUNCTIONS ARE SENSITIVE TO SINGLE and DOUBLE QUOTES and COMMAS

	// Choose from List
	// Returns false on cancel....
	public function from_list( $list, $options = false ) {
		if ( ! is_array( $list ) ) {
			return false;
		}
		$list  = '{"' . implode( '", "', $list ) . '"}';
		$start = "choose from list {$list}";
		$default_options = [
			'with title'                  => 'title',
			'with prompt'                 => 'text',
			'default items'               => 'default',
			'OK button name'              => 'ok',
			'cancel button name'          => 'cancel',
			'multiple selections allowed' => 'multiple',
			'empty selection allowed'     => 'empty'
		];
		$script = self::create( $start, $default_options, $options );

		if ( $script ) {
			return self::process( exec( $script ) );
		} else {
			return false;
		}
	}

	// Choose File(s)
	public function file( $options = false ) {
		$start = 'choose file';
		$default_options = [
			'with prompt'                 => 'text',
			'of type'                     => 'type',
			'default location'            => 'location',
			'invisibles'                  => 'invisibles',
			'multiple selections allowed' => 'multiple',
			'showing package contents'    => 'package_contents'
		];
		$script = self::create( $start, $default_options, $options );

		if ( $script ) {
			return self::process( exec( $script ), 'alias ', true );
		} else {
			return false;
		}
	}

	// Choose Filename
	public function filename( $options = false ) {
		$start = 'choose file name';
		$default_options = [
			'with prompt'      => 'text',
			'default name'     => 'default',
			'default location' => 'location'
		];
		$script = self::create( $start, $default_options, $options );

		if ( $script ) {
			return self::process( exec( $script ), 'file ', true );
		} else {
			return false;
		}
	}

	// Choose Folder(s)
	public function folder( $options = false ) {
		$start = 'choose folder';
		$default_options = [
			'with prompt'                 => 'text',
			'default location'            => 'location',
			'invisibles'                  => 'invisibles',
			'multiple selections allowed' => 'multiple',
			'showing package contents'    => 'package_contents'
		];
		$script = self::create( $start, $default_options, $options );

		if ( $script ) {
			return self::process( exec( $script ), 'alias ', true );
		} else {
			return false;
		}
	}

	// Factory
	private function create( $start, $options, $selections ) {
		if ( ! isset( $options ) || ! is_array( $options ) ) {
			return false;
		}
		$script = "osascript -e '{$start}";
		foreach ( $options as $key => $value ) :
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
				if ( $quotes ) {
					$script .= " {$key} \"{$selections[ $value ]}\"";
				} else {
					$script .= " {$key} {$selections[ $value ]}";
				}
			}
		endforeach;
		return $script .= "' 2>&1";
	}

	/**
	 * Processes the returned text from an AppleScript interaction
	 *
	 * @param  string  $result 	the text that comes out of the AppleScript interaction
	 * @param  mixed   $strip  	text to strip out of the result
	 * @param  boolean $path   	whether or not the result is a path
	 * @return string           the processed text
	 */
	private function process( $result, $strip = false, $path = false ) {
		// Make sure the user didn't cancel the selection
		if ( false !== strpos( $result, 'execution error: User canceled. (-128)' ) ) {
			// User canceled the operation, so return an explicit "canceled"
			return 'canceled';
		}
		if ( $strip ) {
			$result = str_replace( $strip, '', $result );
		}
		if ( false !== strpos( $result, ',' ) ) {
			$result = explode( ',', $result );
			// Just trim everything
			array_walk( $result, function( &$value, $key ) {
				$value = trim( $value );
			});
		} else {
			$result = [ $result ];
		}
		if ( $path ) {
			array_walk( $result, function( &$value, $key ) {
				$value = self::to_posix_path( $value );
			});
		}
		return $result;
	}

	/**
	 * Converts to a POSIX path
	 *
	 * @param  string 	$path 	the path
	 * @return string       		the path as POSIX
	 */
	private function to_posix_path( $path ) {
		return '/' . str_replace( ':', '/', $path );
	}

}


// Not written / not sure I'm going to write:
// ===========
// class ChooseApplication {
//     // this needs to be written
// }
// Note: "choose remote application" will not be included nor will "choose file name" as those
// use cases can be taken care of by the above and better php scripting.