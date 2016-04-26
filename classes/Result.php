<?php

/**
 * Result class
 *
 * Class object represents an item in the script filter array. The internals of the
 * class check for validity so that only correct methods can be set.
 *
 * @since 1.0.0
 * @see ScriptFilter::add_result() These items are part of the ScriptFilter
 *
 * @method void set_arg(            string $arg          ) the argument to pass
 * @method void set_autocomplete(   string $autocomplete ) autocomplete text
 * @method void set_icon(           string $icon         ) path to icon
 * @method void set_icon_fileicon(  string $fileicon     ) path to application
 * @method void set_icon_filetype(  string $filetype     ) filetype for icon
 * @method void set_subtitle(       string $subtitle     ) subtitle text
 * @method void set_subtitle_alt(   string $subtitle     ) alt subtitle text
 * @method void set_subtitle_cmd(   string $subtitle     ) cmd subtitle text
 * @method void set_subtitle_ctrl(  string $subtitle     ) ctrl subtitle text
 * @method void set_subtitle_fn(    string $subtitle     ) fn subtitle text
 * @method void set_subtitle_shift( string $subtitle     ) shift subtitle text
 * @method void set_text_copy(      string $text         ) text to pass when copying
 * @method void set_text_largetype( string $text         ) text to pass to large type
 * @method void set_title(          string $title        ) title of result
 * @method void set_uid(            string $uid          ) uid for result
 */
class Result {

	/**
	 * Possible string methods for a Result
	 *
	 * @var array
	 */
	private static $string_methods = [
		'title',
		'icon',
		'icon_filetype',
		'icon_fileicon',
		'subtitle',
		'subtitle_shift',
		'subtitle_fn',
		'subtitle_ctrl',
		'subtitle_alt',
		'subtitle_cmd',
		'uid',
		'arg',
		'text_copy',
		'text_largetype',
		'autocomplete',
	];

	/**
	 * @todo  for Alfred 3 and JSON,
	 *        	1 add in a deprecation for setting things that should be arrays as strings
	 *        	2 add in settings for array objects
	 *        	3 add in compatibility mode
	 */

	// "items": [{
	// 	"type": "file",
	// 	"icon": {
	// 		"type": "fileicon",
	// 		"path": "path"
	// 	},
	// 	"mods": {
	// 		"alt": {
	// 			"valid": true,
	// 			"arg": "marg",
	// 			"subtitle": "msubtext"
	// 		}
	// 	},
	// 	"text": {
	// 		"copy": "textforcopy",
	// 		"largetype": "textforlargetype"
	// 	}
	// }, {

	/**
	 * Possible boolean methods for a Result
	 * @var array
	 */
	private static $bool_methods = [ 'valid' ];

	/**
	 * Creates a Result object
	 *
	 * @param array|string $args the title if string; a list of arguments if an array
	 */
	public function __construct( $args ) {

		// Create the data storage variable
		$this->data = [];

		// If it is a string, then it's the title; if it's an array, then it's multiple values
		if ( is_string( $args ) ) {
			// Set the title
			$this->set_title( $args );
		} else if ( is_array( $args ) ) {
			// It's an array, so, cycle through each value and set it
			foreach ( $args as $key => $value ) :
				$this->set( [ $key => $value ] );
			endforeach;
		}

	}

	/**
	 * Sets a multiple values of a result object
	 *
	 * @throws \Alphred\InvalidScriptFilterArgument When trying to set an invalid script filter field
	 *
	 * @param array $options an array of possible options
	 */
	public function set( $options ) {
		// Options must be an array of 'key' => 'value', like: 'title' => 'This is a title'
		if ( ! is_array( $options ) ) {
			return false;
		}
		// Cycle through the options and see if they are in either $string_methods or $bool_methods;
		// if so, call them via the magic __call(); otherwise, thrown an exception.
		foreach ( $options as $option => $value ) :
			$method = "set_{$option}";
		  if ( in_array( $option, self::$string_methods ) || in_array( $option, self::$bool_methods ) ) {
		  	$this->$method( $value );
		  } else {
		  	// Not valid. Throw an exception.
				throw new InvalidScriptFilterArgument( "Error: `{$method}` is not valid.", 3 );
			}
		endforeach;
	}

	/**
	 * Magic method to set everything necessary
	 *
	 * @todo Convert the 'false' returns to thrown Exceptions
	 * @throws \Alphred\TooManyArguments when trying to use multiple values
	 * @throws \Alphred\InvalidXMLProperty when trying to set an invalid XML property
	 *
	 * @param  string $called    method called
	 * @param  array $arguments  array of arguments
	 * @return bool
	 */
	public function __call( $called, $arguments ) {
		// Make sure that the method is supposed to exist
		if ( 0 !== strpos( $called, 'set_' ) ) {
			// We should raise an exception here instead.
			return false;
		}
		// There should only be one argument in the arguments array
		if ( 1 === count( $arguments ) ) {
			// Remove the "set_" part of the 'method'
			$method = str_replace( 'set_', '', $called );
			// If the value is a bool, then check to make sure it's supposed to be a bool
			if ( is_bool( $arguments[0] ) && ( in_array( $method, self::$bool_methods ) ) ) {
				// Set the data
				$this->data[ $method ] = $arguments[0];
				return true;
			} else if ( in_array( $method, self::$string_methods ) ) {
				// Set the data
				$this->data[ $method ] = $arguments[0];
				return true;
			} else {
				if ( in_array( $method, self::$bool_methods ) ) {
					throw new ShouldBeBool( "`{$method}` should be passed as bool not string" );
				} else {
					throw new InvalidXMLProperty( "`{$method}` is not a valid property for a script filter.", 3 );
				}
			}
		} else {
			throw new TooManyArguments( "Expecting a single argument when trying to `{$called}` but got multiple.", 3 );
		}
	}
}
