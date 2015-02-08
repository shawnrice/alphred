<?php
/**
 * Contains Text class for Alphred
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
 * Methods to apply "filters" to text to transform them
 *
 * @todo Brainstorm what other filters should be written
 * @since 1.0.0
 *
 */
class Text {

	/**
	 * Convert a string to Title Case
	 *
	 * @param  string $string string to be converted
	 * @return string         the string in Title Case
	 */
	public static function title_case( $string ) {
		// This needs to be improved upon. Basically, it needs to account for sentence ending punctuation.
		// Words that are not capitalized. Well, articles, conjunctions, and prepositions.
		$lower = [
			'the','a','an','and','but','or','for','nor','aboard','about','above','across','after','against','along',
			'amid','among','anti','around','as','at','before','behind','below','beneath','beside','besides','between',
			'beyond','but','by','concerning','considering','despite','down','during','except','excepting','excluding',
			'following','for','from','in','inside','into','like','minus','near','of','off','on','onto','opposite',
			'outside','over','past','per','plus','regarding','round','save','since','than','through','to','toward',
			'towards','under','underneath','unlike','until','up','upon','versus','via','with','within','without'
		];

		$starting = [ '“', '‘' ]; // Add in things like upside-down exclamation points and question marks
		$stop     = [ '.', '!', '?' ];
		$words = explode( ' ', $string );
		foreach ( $words as $k => $w ) :

			// Grab the first and last characters to check for punctuation later
			$first = substr( $w, 0, 1 );
			$last = substr( $w, -1, 1 );

			// remove all punctuation (except hyphens and en- and em-dashes) from the string
			$w = preg_replace( '/(?![-])\p{P}/u', '', $w );
			if ( ! in_array( $w, $lower ) || strlen( $w ) > 3 ) {
				$words[ $k ] = ucfirst( $w );
			} else {
				$words[ $k ] = lcfirst( $w );
			};

			// Add back in the punctuation if it was there.
			if ( ctype_punct( $first ) ) { $words[ $k ] = $first . $words[ $k ]; }
			if ( ctype_punct( $last ) )  { $words[ $k ] = $words[ $k ] . $last;  }

		endforeach;
		$words[0] = ucfirst( $words[0] );
		return implode( ' ', $words );
	}

	/**
	 * Converts a string to CamelCase
	 *
	 * @param  string 	$string 	the string to be converted
	 * @return string             the string in CamelCase
	 */
	public static function camel_case( $string ) {
		// converts spaces to camelcase
		$words = explode( ' ', $string );
		foreach ( $words as $k => $w ) :
			$words[ $k ] = ucfirst( $w );
		endforeach;
		$words[0] = lcfirst( $words[0] );

		return implode( '', $words );
	}

	public static function underscore( $string ) {
		// converts spaces to underscores
		return str_replace( ' ', '_', $string );
	}

	public static function hyphenate( $string ) {
		// converts spaces to hyphens
		return str_replace( ' ', '-', $string );
	}

	/**
	 * Adds commas to list (uses Oxford Commas, sorry, it's just proper)
	 *
	 * @todo Debug this... it doesn't quite make sense looking back on it
	 *
	 * @param array  	$list   an array to be pushed into a comma-ified string
	 * @param boolean $suffix a suffix to add
	 */
	public static function add_commas_to_list( $list, $suffix = false ) {
		// We want a string, so let's convert it to one with an Oxford Comma
		$string = '';
		$count  = 1;
		foreach ( $list as $unit => $value ) :
			// Concatenate the string with the units
			$string .= $suffix ? "{$value} {$unit}" : $value;
			if ( $count == count( $list ) ) {
				// All done, so return
				return $string;
			} else if ( ( ( $count + 1 ) == count( $list ) ) && ( 2 == count( $list ) ) ) {
				// There are only two units, so no comma
				$string .= ' and ';
			} else if ( ( $count + 1 ) == count( $list ) ) {
				// Last unit, so add in the "and"
				$string .= ', and ';
			} else {
				// We have more units, so just add in the comma
				$string .= ', ';
			}
			$count++;
		endforeach;

		return $string;

	}

}