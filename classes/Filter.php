<?php
/**
 *
 * This file contains the functions for filtering arrays of text
 *
 *
 * Right now, it's not written as a class file. Change that.
 */

namespace Alphred;


$array = [];
$array[] = "OmniFocus";
$array[] = "The Duke of York";
$array[] = "Configuration";
$array[] = "Offers of wealth and happiness and Fear";
$array[] = "abcde";


class Filter {

	# These replicate Alfred Workflow
	# ####
	# Match filter flags
	#: Match items that start with ``query``
	const MATCH_STARTSWITH = 1;
	#: Match items whose capital letters start with ``query``
	const MATCH_CAPITALS = 2;
	#: Match items with a component "word" that matches ``query``
	const MATCH_ATOM = 4;
	#: Match items whose initials (based on atoms) start with ``query``
	const MATCH_INITIALS_STARTSWITH = 8;
	#: Match items whose initials (based on atoms) contain ``query``
	const MATCH_INITIALS_CONTAIN = 16;

	const MATCH_INITIALS = 24;
	#: Combination of :const:`MATCH_INITIALS_STARTSWITH` and
	#: :const:`MATCH_INITIALS_CONTAIN`
	const MATCH_INITIALS = 32;
	#: Match items if ``query`` is a substring
	const MATCH_SUBSTRING = 64;
	#: Match items if all characters in ``query`` appear in the item in order
	const MATCH_ALLCHARS = 127;
	#: Combination of all other ``MATCH_*`` constants

	public function Filter( $haystack, $needle, $flags = self::MATCH_ALLCHARS ) {

		if ($flags & self::MATCH_STARTSWITH) {
			print "Match Starts with\n";
		}
		if ($flags & self::MATCH_SUBSTRING) {
			print "Match Substring\n";
		}
		if ($flags & self::MATCH_ATOM) {
			print "Match Atom\n";
		}
		if ($flags & self::MATCH_INITIALS_STARTSWITH) {
			print "Match Initials Starts with\n";
		}

		if ($flags & self::MATCH_ALLCHARS) {
			print "Match All Chars\n";
		}

		foreach ( $haystack as $row ) :
			// $lev = levenshtein( $row, $string, 9999, 9999, 9 );
			// print "Score {$lev} for: {$row}\n";
			if ( self::match_initials( $row, $needle ) )
				print $row . "\n";
		endforeach;
	}


	/**
	 * Prepares the string by removing diacritics, etc
	 *
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	function prepare_string( $string ) {

	}


	function remove_all_non_caps( $string ) {
		return strtolower( preg_replace( '/[^A-Z]/', '', $string ) );
	}

	function match_initials( $haystack, $needle ) {
		$haystack = self::remove_all_non_caps( $haystack );
		if ( false !== strpos( $haystack, $needle ) )
			return true;
		return false;
	}


}

Filter::Filter( $array, "of", 6 );