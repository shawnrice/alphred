<?php
/**
 * Contains Ini class for Alphred
 *
 * PHP version 5
 *
 * @package    Alphred
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
 * Extends INI parsing and writing for PHP
 *
 * This class allows to read and write `ini` files. It translates `ini` files into
 * associative PHP arrays and translates PHP arrays into `ini` files. It supports
 * sectioning as well as a kind of subsectioning.
 *
 * Colons (`:`) are considered separators for sub-sections and are represented
 * as multi-dimensional arrays. For instance, the following array:
 * ````php
 * $array = [
 *  'Alphred' => [
 *  'log_level' => 'DEBUG',
 * 	'log_size' => 10000,
 * 	'plugins'  => [ 'get_password' => 'my_new_function' ]
 * ]];
 * ````
 * will be represented as
 * ````ini
 * [Alphred]
 * log_level = DEBUG
 * log_size = 10000
 *
 * [Alphred:plugins]
 * get_password = my_new_function
 * ````
 *
 * If you are concerned, then make sure that `\r\n` is removed from the array values
 * before they move into the INI file, as they may break them.
 *
 * All of these are static functions. So, to use:
 * ````php
 * $ini_file = Alphred\Ini::read_ini( '/path/to/workflow.ini' );
 * ````
 * That's it.
 *
 * To write an `ini` file, just do:
 * ````php
 * Alphred\Ini::write_ini( $config_array, '/path/to/workflow.ini' );
 * ````
 *
 * @since 1.0.0
 *
 */
class Ini {

	/**
	 * Parses an INI
	 *
	 * This is a slightly better INI parser in that will read a section title of
	 * 'title:subtitle' 'subtitle' as a subsection of the section 'title'.
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $file      path to the ini file to read
	 * @param  boolean $exception whether or not to throw an exception on file not found
	 * @return array|boolean      an array that represents the ini file
	 */
	public function read_ini( $file, $exception = true ) {
		if ( ! file_exists( $file ) ) {
			if ( $exception ) {
				throw new FileDoesNotExist( "File `{$file}` not found." );
			} else {
				return false;
			}
		}

		// Parse the INI files
		$ini = parse_ini_file( $file, true );
		$array = [];
		foreach( $ini as $key => $value ) :
			if ( is_array( $value ) ) {
				$array = array_merge_recursive( $array, self::parse_section( $key, $value ) );
			} else {
				$array[ $key ] = $value;
				// array_unshift( $array, [ $key => $value ] );
			}
		endforeach;

		return $array;

	}

	/**
	 * Writes an INI file from an array
	 *
	 * @since 1.0.0
	 * @todo Do filesystem checks
	 *
	 * @param  array  $array  the array to be translated into an ini file
	 * @param  string $file  the full path to the ini file, should have '.ini'
	 */
	public function write_ini( $array, $file ) {
		// Collapse the arrays into writeable sections
		$sections = self::collapse_sections( $array );
		// Separate out the things that need to be in the global space from the things
		// that need to be in sectioned spaces
		$sections = self::separate_non_sections( $sections );
		$global = $sections[0];
		$sections = $sections[1];

		// sort the sections
		ksort( $sections );

		$base = basename( $file );

	  // Write a header
		$contents = ";;;;;\r\n";
		$contents .= "; `{$base}` generated by Alphred v" . ALPHRED_VERSION . "\r\n";
		$contents .= "; at " . date( 'Y-M-d H:i:s', time() ) . "\r\n";
		$contents .= ";;;;;\r\n\r\n";

		// Write things in the global space first
		foreach( $global as $value ) :
			// There should really be only one item in each array, but this is easy
			foreach ( $value as $k => $v ) :
				$contents .= "{$k} = \"{$v}\"\r\n";
			endforeach;
		endforeach;

		// Now write out the sections
		foreach ( $sections as $title => $section ) :

			// Print the section
			if ( is_array( $section ) ) {
				if ( ! is_integer( $title ) ) {
					$contents .= "\n[$title]\n";
				}
				$contents .= self::print_section( $section );
			} else {
				// Okay, the names are a bit weird here. This is
				// actually key => value rather than title => section
				// This is actually a deprecated part now, and we should
				// never quite get here.
				$contents .= "{$title} = \"{$section}\"\r\n";
			}

		endforeach;

		file_put_contents( $file, mb_convert_encoding( $contents, 'UTF-8', 'auto' ) );
	}

	/**
	 * Separates out bits from the global space and from sections
	 *
	 * @param  array $array array of values to write to an ini file
	 * @return array        a sorted array
	 */
	private function separate_non_sections( $array ) {
		// Bad name for the method

		// The global space
		$global = [];
		// Sectioned space
		$sections = [];
		foreach ( $array as $key => $value ) :
			if ( is_array( $value ) ) {
				// If it is an array, then we assume that it's a
				// section, so put it in the sections array
				$sections[ $key ] = $value;
			} else {
				// If it's not an array, then we assume that it needs
				// to go in the global space, so put it in the global
				// array
				$global[] = [ $key => $value ];
			}
		endforeach;
		// Return the sorted array
		return [ $global, $sections ];

	}

	/**
	 * Prints the section of an INI file
	 *
	 * @since 1.0.0
	 *
	 * @param  array $section  an array
	 * @return string          the array as an ini section
	 */
	private function print_section( $section ) {
		$contents = '';
		foreach( $section as $key => $value ) :
			if ( is_array( $value ) ) {
				foreach( $value as $v ) :
						$contents .= "{$key}[] = \"{$v}\"\r\n";
				endforeach;
			} else {
				if ( empty( $key ) ) {
					continue;
				}
				$contents .= "{$key} = \"{$value}\"\r\n";
			}
		endforeach;
		return $contents;
	}

	/**
	 * Collapses arrays into something that can be written in the ini
	 *
	 * @since 1.0.0
	 *
	 * @param  array $array the array to be collapsed
	 * @return array        the collapsed array
	 */
	private function collapse_sections( $array ) {
		return self::step_back( self::flatten_array( $array ) );
	}

	/**
	 * Flattens an associate array
	 *
	 * @since 1.0.0
	 * @todo Better tests for numeric keys
	 *
	 * @param  array $array    an array to be flattened
	 * @param  string $prefix  a prefix for a key
	 * @return array           the array, but flattened
	 */
	private function flatten_array( $array, $prefix = '' ) {
			if ( ! is_array( $array ) ) {
				return $array;
			}
			if ( ! self::is_assoc( $array ) ) {
				return $array;
			}

	    $result = [];

	    foreach ( $array as $key => $value ) :

	        $new_key = $prefix . ( empty( $prefix ) ? '' : ':') . $key;

	      	if ( is_integer( $key ) ) {
	      		// Don't compound numeric keys; the assumption is that a numeric key will contain only
	      		// one array. @todo test this further
	      		foreach ( $value as $k => $v ) :
	      			$result[ $k ] = $v;
						endforeach;
	      	} else if ( is_array( $value ) && self::is_assoc( $value ) ) {
	            $result = array_merge( $result, self::flatten_array( $value, $new_key ) );
	        } else {
	            $result[ $new_key ] = $value;
	        }
	    endforeach;

	    return $result;
	}

	/**
	 * Slightly unflattens an array
	 *
	 * So, flatten_array goes one step too far with the flattening, but I
	 * don't know how many levels down I need to flatten (2, 97?), so we just flatten
	 * all the way and then step back one level, which is what this function does.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $array a flattened array
	 * @return array        a slightly less flat array
	 */
	private function step_back( $array ) {
		$new = [];
		foreach( $array as $key => $value ) :
		  if ( substr_count( $key, ':' ) >= 1 ) {
				$pos = strrpos( $key, ':' );
				$section = substr( $key, 0, $pos );
				$new_key = substr( $key, $pos + 1 );
				$new[ $section ][ $new_key ] = $value;
			} else {
				$new[ $key ] = $value;
			}
		endforeach;
		return $new;
	}


	/**
	 * Parses an ini section into its subsections
	 *
	 * @since 1.0.0
	 *
	 * @param  string $name   a string that should be turned into an array
	 * @param  mixed $values  the values for an array
	 * @return array          the newly-dimensional array with $values
	 */
	private function parse_section( $name, $values ) {
		if ( false !== strpos( $name, ':' ) ) {
			$pieces = explode( ':', $name );
			$pieces = array_filter( $pieces, 'trim' );
		} else {
			return [ $name => $values ];
		}
		return self::nest_array( $pieces, $values );
	}

	/**
	 * Recursively nests an array
	 *
	 * @since 1.0.0
	 *
	 * @param  array $array   the pieces to nest
	 * @param  mixed $values  the values for the bottom level of the newly dimensional array
	 * @return array          a slightly more dimensional array than we received
	 */
	private function nest_array( $array, $values ) {
	    if ( empty( $array ) ) {
	        return $values;
	    }
	    return [ array_shift( $array ) => self::nest_array( $array, $values ) ];
	}

	/**
	 * Checks if an array is associative
	 *
	 * Shamelessly stolen from http://stackoverflow.com/a/14669600/1399574
	 *
	 * @since 1.0.0
	 *
	 * @param  array  	$array an array
	 * @return boolean         whether it is associative
	 */
	private function is_assoc( $array ) {
	    // Keys of the array
	    $keys = array_keys( $array );

	    // If the array keys of the keys match the keys, then the array must
	    // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
	    return array_keys( $keys ) !== $keys;
	}
}
