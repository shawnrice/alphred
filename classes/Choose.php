<?php
/**
 * Contains Choose class for Alphred
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
 * Creates and executes AppleScript "Choose from..." dialogs.
 *
 * The ones that aren't included can be better served by regular PHP scripting.
 *
 * @since 1.0.0
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