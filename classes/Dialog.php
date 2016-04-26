<?php
/**
 * Contains Dialog class for Alphred
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
 * Class to create standard AppleScript dialogs.
 *
 * This class is awkward.
 *
 * @todo Revisit developer experience of creating dialogs
 *
 */
class Dialog {

	/**
	 * Initializes the AppleScript dialog, optionally setting values necessary
	 *
	 * @since 1.0.0
	 * @todo Add in better error checking
	 *
	 * @param array 	$values 	an array of values to set
	 */
	public function __construct( $values = [] ) {
		// Has this been initialized with values in the array?
		if ( count( $values ) > 0 ) {
			// Cycle through the array and set the appropriate variables
			foreach ( $values as $k => $v ) :
				// Make sure that the method exists and then set the value
				if ( method_exists( $this, "set_{$k}" ) ) {
					$method = "set_{$k}";
					// Call the methods on this object
					$this->$method( $v );
				}
			endforeach;
		}

	}

	/**
	 * Assembles the AppleScript code for the dialog based on the object's values
	 *
	 * @since 1.0.0
	 *
	 * @return [type] [description]
	 */
	private function create_dialog() {

		// The script starts with this....
		$this->script = "display dialog \"{$this->text}\"";

		// Cycle through all the possible values...
		/**
		 * @todo Should I change this to a foreach loop with an array?
		 */
		if ( isset( $this->buttons_ ) ) {
			$this->script .= $this->buttons_;
		}
		if ( isset( $this->default_answer ) ) {
			$this->script .= $this->default_answer;
		}
		if ( isset( $this->title ) ) {
			$this->script .= $this->title;
		}
		if ( isset( $this->icon ) ) {
			$this->script .= $this->icon;
		}
		if ( isset( $this->hidden_answer ) ) {
			$this->script .= $this->hidden_answer;
		}
		if ( isset( $this->cancel ) ) {
			$this->script .= $this->cancel;
		}
		if ( isset( $this->timeout ) ) {
			$this->script .= $this->timeout;
		}
	}

	/**
	 * Executes the dialog
	 *
	 * @return text the text returned from the dialog (button press/answer)
	 */
	public function execute() {
		// Create the script
		$this->create_dialog();

		// Execute the script
		$result = exec( "osascript -e '{$this->script}' 2>&1" );

		// Each of these below processes the text returned
		if ( false !== strpos( $result, ', gave up:false' ) ) {
			$result = str_replace( ', gave up:false', '', $result );
		}
		if ( false !== strpos( $result, 'gave up:true' ) ) {
			// There was a timeout on the dialog, and it, well, timed out
			return 'timeout';
		}
		if ( false !== strpos( $result, 'execution error: User canceled. (-128)' ) ) {
			// The user pressed the cancel button
			return 'canceled';
		}
		if ( false !== strpos( $result, 'text returned:' ) ) {
			return substr( $result, strpos( $result, 'text returned:' ) + 14 );
		}
		return str_replace( 'button returned:', '', $result );
	}

	/**
	 * Sets an icon to use in the dialog box
	 *
	 * @since 1.0.0
	 * @todo Add in exceptions?
	 *
	 * @param string $icon the path to the icon or the name of the icon
	 */
	public function set_icon( $icon ) {
		// These are the default options
		$default_icons = ['stop', 'note', 'caution'];
		// The is icon one of the defualts?
		if ( in_array( strtolower( $icon ), $default_icons ) ) {
			// Set the "icon" text
			$this->icon = ' with icon ' . array_search( $icon, $default_icons );
			return true;
		}
		if ( ! file_exists( realpath( $icon ) ) ) {
			// The icon doesn't exist. Should I throw an exception?
			return false;
		}
		// Convert the POSIX path to the kind that AppleScript wants
		$icon = str_replace( '/', ':', realpath( $icon ) );
		// Set the "icon text"
		$this->icon = ' with icon file "' . substr( $icon, 1, strlen( $icon ) - 1 ) . '"';
		return true;
	}

	/**
	 * Sets the text for the dialog and adds slashes to ensure the dialog code does not break
	 *
	 * @since 1.0.0
	 * @todo Check to make sure that this is necessary
	 *
	 * @param string $text the text for the AppleScript dialog
	 */
	public function set_text( $text ) {
		$this->text = addslashes( $text ); // is the addslashes necessary?
	}

	/**
	 * Sets the buttons for the dialog box
	 *
	 * @since 1.0.0
	 * @todo Check for what I need to do to santize these
	 *
	 * @param array $buttons [description]
	 * @param string $default [description]
	 */
	public function set_buttons( $buttons, $default = '' ) {
		if ( empty( $buttons ) ) {
			// One could wonder why you're trying to give us an empty set of buttons. Give me an array, please.
			// Or a string. Just something. Give me something.
			return false;
		}
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

		// If the default button was also passed, then set the default button
		if ( ! empty( $default ) ) {
			$this->set_default_button( $default );
		}
	}

	/**
	 * Sets the default button
	 *
	 * @since 1.0.0
	 *
	 * @param sting $button the name of the button to set as default
	 */
	public function set_default_button( $button ) {
		// The button must be in the extant array of buttons. And AppleScript
		// wants the number of the button, but it starts with 1 and not 0, so
		// do a search, add 1, and set it as that.
		if ( $default = ( array_search( $button, $this->buttons ) + 1 ) ) {
			$this->buttons_ .= " default button {$default}";
			return true;
		}
		// The button wasn't found. Um.... what?
		return false;
	}

	/**
	 * Sets the title of the dialog
	 *
	 * @since 1.0.0
	 *
	 * @param string $title title for the dialog
	 */
	public function set_title( $title ) {
		// Add slashes so as not to break the dialog
		$title = addslashes( $title );
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
			// Since the input box will not show up unless there is a default answer
			// set, we'll go ahead and set a blank default answer if one hasn't been
			// set yet.
			if ( ! isset( $this->default_answer ) ) {
				$this->set_default_answer( '' );
			}
		}
	}

}