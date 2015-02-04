<?php

namespace Alphred;

/**
 *
 *
 * @uses Result           Result items are stored in the script filter
 *
 */
class ScriptFilter {
	// Should we have the options of "modules" to enable here and have this as the main entry-point
	// for the entire usage?

	/**
	 * Constructs a script filter container
	 *
	 * @param array $options specify options:... see>>>?
	 */
	public function __construct( $options = [] ) {

		if ( isset( $options['config'] ) ) {
			$this->config = new Config( $options['config'] );
		}

		$this->il18 = false;
		foreach ( ['localize', 'localise', 'il8n' ] as $localize ) :
			if ( isset( $options[ $localize ] ) && $options[ $localize ] ) {
				$this->initializei18n();
				break;
			}
		endforeach;

		// We'll just save all the options for later use if necessary
		$this->options = $options;

		$this->results = [];
		$this->xml = new \XMLWriter();

	}

	/**
	 * Initializes a i18n Alphred object to use internally
	 *
	 * @return \Alphred\i18n object [description]
	 */
	private function initializei18n() {
		if ( class_exists( '\Alphred\i18n' ) ) {
			$this->il18 = new i18n;
		}
	}

	/**
	 * Translates a string using the i18n class
	 *
	 * @see i18n
	 *
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	private function t( $string ) {
		if ( ! isset( $this->i18n ) ) {
			return $string;
		}
		return $this->i18n->translate( $string );
	}


	/**
	 * Adds a result into the script filter
	 *
	 * @see \Alphred\Result
	 *
	 * @param \Alphred\Result $result [description]
	 */
	public function add_result( \Alphred\Result $result ) {
		if ( ! ( is_object( $result ) && ( 'Alphred\Result' == get_class( $result ) ) ) ) {
			// Double-check that the namespacing doesn't affect the return value of "get_class"
			// raise an exception instead
			return false;
		}
		array_push( $this->results, $result );
	}

	public function item( $props ) {
		$tmp = new Result( $props );
		$this->add_result( $tmp );
		return $tmp;
	}

	public function get_results() {
		return $this->results;
	}

	/**
	 * Outputs the script filter in Alfred XML
	 *
	 * @return [type] [description]
	 */
	public function to_xml() {

		if ( isset( $this->options['error_on_empty'] ) ) {
			if ( 0 === count( $this->get_results() ) ) {
				// Alter these strings below to make them more generic
				$result = new Result( [
					'title'    => 'Error: No results found.',
					'icon'     => '/System/Library/CoreServices/CoreTypes.bundle/Contents/Resources/Unsupported.icns',
					'subtitle' => 'Please search for something else.',
					'valid'    => false
				]);
				$this->add_result( $result );
			}
		}

		$this->xml->openMemory();
		$this->xml->setIndent( 4 );
		$this->xml->startDocument( '1.0', 'UTF-8' );
		$this->xml->startElement( 'items' );

		foreach ( $this->results as $result ) :
			$this->write_item( $result );
		endforeach;
		$this->xml->endDocument();
		echo $this->xml->outputMemory();
	}


	private function write_item( $item ) {
		$item = $item->data;
		$attributes = [ 'uid', 'arg', 'autocomplete' ];
		$bool = [ 'valid' ];
		$this->xml->startElement( 'item' );

		foreach ( $attributes as $v ) :
			if ( ! isset( $item[ $v ] ) ) {
				if ( ( 'autocomplete' !== $v ) && ( 'uid' !== $v ) ) {
					$this->xml->writeAttribute( $v, '' );
				}
			} else {
				$this->xml->writeAttribute( $v, $item[ $v ] );
			}
		endforeach;

		if ( isset( $item['valid'] ) && in_array( strtolower( $item['valid'] ), ['yes', 'no', true, false] ) ) {
			$valid = $item['valid'] ? 'yes' : 'no';
			$this->xml->writeAttribute( 'valid', $valid );
		} else {
			$this->xml->writeAttribute( 'valid', 'no' );
		}

		foreach ( $item as $k => $v ) :
			if ( ! in_array( $k, array_merge( $attributes, $bool ) ) ) {
				if ( false !== strpos( $k, '_' ) && 0 === strpos( $k, 'subtitle' ) ) {
					$this->xml->startElement( substr( $k, 0, strpos( $k, '_' ) ) );
					$this->xml->writeAttribute( 'mod', substr( $k, strpos( $k, '_' ) + 1 ) );
				} else if ( false !== strpos( $k, '_' ) ) {
					// Add in checks for icon filetype
					$this->xml->startElement( substr( $k, 0, strpos( $k, '_' ) ) );
					$this->xml->writeAttribute( 'type', substr( $k, strpos( $k, '_' ) + 1 ) );
				} else {
					$this->xml->startElement( $k );
				}
				$this->xml->text( $this->t( $v ) );
				$this->xml->endElement();
			}
		endforeach;

		$this->xml->endElement();
	}

}

/**
 * Result class
 *
 *
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

	public function __construct( $args ) {
		$this->string_methods = [
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
		$this->bool_methods = [ 'valid' ];

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

	// extra meta function that will let you set more than one thing at once
	public function set( $options ) {
		if ( ! is_array( $options ) ) {
			return false;
		}

		foreach ( $options as $option => $value ) :
			$method = "set_{$option}";
			$this->$method( $value );
		endforeach;
	}

	// Let's just make a common function for all the "set" methods
	public function __call( $called, $arguments ) {
		if ( 0 !== strpos( $called, 'set_' ) ) {
			// We should raise an exception here instead.
			return false;
		}
		if ( 1 === count( $arguments ) ) {
			$method = str_replace( 'set_', '', $called );
			if ( is_bool( $arguments[0] ) && ( in_array( $method, $this->bool_methods ) ) ) {
				$this->data[ $method ] = $arguments[0];
				return true;
			} else if ( is_string( $arguments[0] ) ) {
				if ( in_array( $method, $this->string_methods ) ) {
					$this->data[ $method ] = $arguments[0];
					return true;
				}
			}
		}
	}
}