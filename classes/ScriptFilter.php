<?php
/**
 * Contains ScriptFilter and Result class for Alphred to work with script filters
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
 *
 *
 * @uses Result           Result items are stored in the script filter
 *
 */
class ScriptFilter {
	/**
	 * Constructs a script filter container
	 *
	 * @since 1.0.0
	 *
	 * @param array $options specify options:... see>>>?
	 */
	public function __construct( $options = [] ) {

		$this->i18n = false;
		foreach ( ['localize', 'localise', 'il8n' ] as $localize ) :
			if ( isset( $options[ $localize ] ) && $options[ $localize ] ) {
				$this->initializei18n();
				break;
			}
		endforeach;

		// We'll just save all the options for later use if necessary
		$this->options = $options;

		// Create an array to hold the results
		$this->results = [];

		// Create the XML writer
		$this->xml = new \XMLWriter();

	}

	/**
	 * Initializes a i18n Alphred object to use internally
	 *
	 * @since 1.0.0
	 * @see \Alphred\i18n
	 *
	 */
	private function initializei18n() {
		if ( class_exists( '\Alphred\i18n' ) ) {
			$this->il18 = new i18n;
		} else {
			\Alphred\Log::console( 'Error: cannot find i18n class.', 0 );
		}
	}

	/**
	 * Translates a string using the i18n class
	 *
	 * @since 1.0.0
	 * @see \Alphred\i18n
	 *
	 * @param  string $string a string to translate
	 * @return string         the string, translated if possible
	 */
	private function translate( $string ) {
		// Check if the translation is turned on
		if ( ! $this->i18n ) {
			// No translation, so just return the string
			return $string;
		}
		// Try to return the translation
		return $this->i18n->translate( $string );
	}


	/**
	 * Adds a result into the script filter
	 *
	 * @since 1.0.0
	 * @see \Alphred\Result
	 *
	 * @param \Alphred\Result $result an Alphred\Result object
	 */
	public function add_result( \Alphred\Result $result ) {
		if ( ! ( is_object( $result ) && ( 'Alphred\Result' == get_class( $result ) ) ) ) {
			// Double-check that the namespacing doesn't affect the return value of "get_class"
			// raise an exception instead
			return false;
		}
		array_push( $this->results, $result );
	}


	/**
	 * Returns an array of the results
	 *
	 * @since 1.0.0
	 *
	 * @return array an array of the current result items
	 */
	public function get_results() {
		return $this->results;
	}

	/**
	 * Alias of to_xml()
	 *
	 * @since 1.0.0
	 * @see \Alphred\ScriptFilter::to_xml()
	 */
	public function print_results() {
		$this->to_xml();
	}

	/**
	 * Outputs the script filter in Alfred XML
	 *
	 * @since 1.0.0
	 *
	 */
	public function to_xml() {

		// If the user requested to have an item when the script filter was empty, then we'll
		// supply a very generic one
		if ( isset( $this->options['error_on_empty'] ) ) {
			if ( 0 === count( $this->get_results() ) ) {
				// A generic "no results found" response
				$result = new Result( [
					'title'    		 => 'Error: No results found.',
					'icon'     		 => '/System/Library/CoreServices/CoreTypes.bundle/Contents/Resources/Unsupported.icns',
					'subtitle' 	   => 'Please try a different query.',
					'autocomplete' => '',
					'valid'        => false
				]);
				$this->add_result( $result );
			}
		}

		// Begin the XML generation
		$this->xml->openMemory();
		$this->xml->setIndent( 4 );
		$this->xml->startDocument( '1.0', 'UTF-8' );
		$this->xml->startElement( 'items' );

		// Cycle through all results and generate the XML
		foreach ( $this->results as $result ) :
			$this->write_item( $result );
		endforeach;
		// End the xml document
		$this->xml->endDocument();
		// Print out the XML
		print $this->xml->outputMemory();
	}

	/**
	 * Writes out the Alfred XML
	 *
	 * @since 1.0.0
	 * @todo Verify this works with icon filetype
	 *
	 * @param object $item An \Alphred\Result object
	 */
	private function write_item( $item ) {
		// The information we need is stored in the sub variable, so let's just get that
		$item = $item->data;
		// These go in the 'item' part as an attribute
		$attributes = [ 'uid', 'arg', 'autocomplete' ];
		// This is either true or false
		$bool = [ 'valid' ];

		// Start the element
		$this->xml->startElement( 'item' );

		// Cycle through all the attributes. If they are set, then write them out
		foreach ( $attributes as $v ) :
			if ( isset( $item[ $v ] ) ) {
				$this->xml->writeAttribute( $v, $item[ $v ] );
			}
		endforeach;

		// Translate 'valid' from a boolean to the 'yes' or 'no' value that Alfred wants to see
		if ( isset( $item['valid'] ) && in_array( strtolower( $item['valid'] ), ['yes', 'no', true, false] ) ) {
			if ( 'no' == strtolower( $item['valid'] ) ) {
				$item['valid'] = false;
			}
			$valid = $item['valid'] ? 'yes' : 'no';
			$this->xml->writeAttribute( 'valid', $valid );
		}

		// Cycle through the $item array and set everything. The keys are the, well, keys, and
		// the values are the values. ( $array => xml )
		foreach ( $item as $k => $v ) :
			// Make suure that the bit of data is not in either the $attributes or $bool array
			if ( ! in_array( $k, array_merge( $attributes, $bool ) ) ) {
				// Check to see, first, if we need to add attributes by parsing the key
				if ( false !== strpos( $k, '_' ) && 0 === strpos( $k, 'subtitle' ) ) {
					$this->xml->startElement( substr( $k, 0, strpos( $k, '_' ) ) );
					$this->xml->writeAttribute( 'mod', substr( $k, strpos( $k, '_' ) + 1 ) );
				} else if ( false !== strpos( $k, '_' ) ) {
					// Add in checks for icon filetype
					// These are the general sub-items
					$this->xml->startElement( substr( $k, 0, strpos( $k, '_' ) ) );
					$this->xml->writeAttribute( 'type', substr( $k, strpos( $k, '_' ) + 1 ) );
				} else {
					// There are no attributes, so just start the sub-element
					$this->xml->startElement( $k );
				}
				// Put in the text (value), and translate it for us if we're using the i18n class
				$this->xml->text( $this->translate( $v ) );
				// Close the sub-element
				$this->xml->endElement();
			}
		endforeach;
		// End the item
		$this->xml->endElement();
	}

}

/**
 * Result class
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

		private $string_methods = [
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
			'autocomplete'
		];
		private $bool_methods = [ 'valid' ];

	public function __construct( $args ) {

		$this->data = [];

		if ( is_string( $args ) ) {
			$this->set_title( $args );
		} else if ( is_array( $args ) ) {
			foreach ( $args as $key => $value ) :
				$fn = "set_{$key}";
				$this->$fn( $value );
			endforeach;
		}

	}

	/**
	 * Sets a multiple values of a result object
	 *
	 * @param array $options an array of possible options
	 */
	public function set( $options ) {
		if ( ! is_array( $options ) ) {
			return false;
		}

		foreach ( $options as $option => $value ) :
			$method = "set_{$option}";
			if ( method_exists( $this, $method ) ) {
				$this->$method( $value );
			} else {
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
			if ( is_bool( $arguments[0] ) && ( in_array( $method, $this->bool_methods ) ) ) {
				// Set the data
				$this->data[ $method ] = $arguments[0];
				return true;
			} else if ( in_array( $method, $this->string_methods ) ) {
				// Set the data
				$this->data[ $method ] = $arguments[0];
				return true;
			} else {
				if ( in_array( $method, $this->bool_methods ) ) {
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