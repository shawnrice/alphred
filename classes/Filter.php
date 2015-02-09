<?php
/**
 *
 * This file contains the functions for filtering arrays of text
 *
 * This is almost a direct translation of the filter functionality of
 * Deanishe's Alfred Workflows (https://github.com/deanishe/alfred-workflow).
 * He and his collaborators get credit for this one.
 *
 * Also, if you are folding diacritics, then you can work only with
 * characters that can be transliterated to ASCII.
 */

namespace Alphred;

class Filter {

	/**
	 * Filters items
	 * @param array    	$haystack [description]
	 * @param string   	$needle   [description]
	 * @param integer  	$max      [description]
	 * @param string  	$key      [description]
	 * @param [type]  	$flags    [description]
	 */
	public function Filter( $haystack, $needle, $max = false, $key = false, $flags = MATCH_ALLCHARS, $fold_diacritics = true ) {
		/**
		 * @todo Add in min-score
		 * @todo Add in return score option
		 * @todo Re-think about the way to call this function
		 */

		// Here, we make the assumption that, if the $needle or search string is empty, then the filter was a misfire, so
		// we'll just return all of the results.
		if ( empty( trim( $needle ) ) ) {
			return $haystack;
		}

		// Initialize an empty results array
		$results = [];

		// Go through each item in the "haystack" array
		foreach ( $haystack as $row ) :

			// Start with a score of 0
			$score = 0;

			// Treat each word in "needle" as separate
			$words = explode( ' ', $needle );
			// trim the whitespace off the needles
			array_walk( $words, 'trim' );

			// If a key was specified, use that; otherwise, just use the value of the row
			if ( $key ) {
				$value = $row[$key];
			} else {
				$value = $row;
			}

			// If the value is empty, then don't bother searching. We got whitespace.
			if ( empty( trim( $value ) ) )
				continue;

			// Foreach word, do a search
			foreach( $words as $word ) :

				// If the word is empty, then don't bother searching
				if ( empty( $word ) )
					continue;

				// Perform the search
				$result = self::filter_item( $value, $word, $flags, $fold_diacritics );
				// Check is a score was sent back that was not 0. If it was 0, then just
				// continue because it didn't matter
				if ( ! $result[0] ) {
					continue;
				}
				// It did matter! And so augment the score.
				$score += $result[0];

			endforeach;

			// If the score is greater than 0, then include it in the results
			if ( $score > 0 ) {
				$results[] = [ $score, $row ];
			}

		endforeach;

		// Sort the array by score
		usort( $results, 'self::sort_by_score' );
		// If we have a max result set, then take the top results
		if ( isset( $max ) && ( count( $results ) > $max ) ) {
			$results = array_slice( $results, 0, $max );
		}

		// Right now, we're just returning the values without the scores.
		foreach ($results as $key => $value ) :
			$results[$key] = $value[1];
		endforeach;
		// Return the sorted results
		return $results;
	}

	/**
	 * Callback function to help sort the results array
	 * @param  array $a an array
	 * @param  array $b an array
	 * @return bool
	 */
	private function sort_by_score( $a, $b ) {
		return $a[0] < $b[0];
	}

	/**
	 * Removes all non-capital characters and non-digit characters frmo a string
	 * @param  string $string 	a string to process
	 * @return string         	the processed string
	 */
	private function remove_all_non_caps( $string ) {
		return strtolower( preg_replace( '/[^A-Z0-9]/', '', $string ) );
	}

	/**
	 * Converts and transliterates a string to ascii
	 * @param  string $string a string to transliterate
	 * @return string         the transliterated string
	 */
	private function convert( $string ) {
		// I don't want to mess with encodings, so we'll just auto-detect
		$encoding = mb_detect_encoding( $string );
		// Note: if PHP will throw a notice if the string contains characters that cannot
		// be transliterated. These will most likely be certain symbols and characters
		// from many different Asian languages.
		return iconv( $encoding, 'ASCII//TRANSLIT', $string );
 	}

 	/**
 	 * [filter_item description]
 	 * @param  string $value           the value string (haystack)
 	 * @param  string $query           the query string (needle)
 	 * @param  [type] $match_on        [description]
 	 * @param  bool   $fold_diacritics whether or not to transliterate to ascii
 	 * @return array                   an array that is score and then the rule matched
 	 */
 	private function filter_item( $value, $query, $match_on, $fold_diacritics ) {
 		$query = strtolower( $query );

 		if ( $fold_diacritics ) {
 			$value = self::convert( $value );
 		}

    // Pre-filter anything that doesn't contain all of the characters
		$arr = array_unique( str_split( $query ) );
		$arr2 = array_unique( str_split( strtolower( $value ) ) );
		if ( count( array_diff( $arr, $arr2 ) ) > 0 ) {
			return [ 0, 'None' ];
		}

    // Item starts with $query
    if ( ( $match_on & MATCH_STARTSWITH ) && ( 0 === strpos( strtolower( $value ), $query ) ) ) {
    	$score = 100.0 - ( strlen( $value ) / strlen( $query ) );
      return [ $score, MATCH_STARTSWITH ];
    }

    // $query matches capitalised letters in item, e.g. of = OmniFocus
    if ( $match_on & MATCH_CAPITALS ) {
    		$initials = self::remove_all_non_caps( $value );
        if ( false !== strpos( $initials, $query ) ) {
            $score = 100.0 - ( strlen( $initials ) / strlen( $query ) );
            return [ $score, MATCH_CAPITALS ];
        }
    }

    // split the item into "atoms", i.e. words separated by spaces or other non-word characters
    if ( $match_on & MATCH_ATOM || $match_on & MATCH_INITIALS_CONTAIN || $match_on & MATCH_INITIALS_STARTSWITH ) {
        // Split into atoms, note: if you are not transliterating, then this will split on accented characters too
        $atoms = preg_split('/[^a-zA-Z0-9]/', strtolower($value), -1, PREG_SPLIT_NO_EMPTY);

        // initials of the atoms
        $initials = $atoms;
        array_walk( $initials, function ( &$value, $key ) { $value = substr( $value, 0, 1 ); } );
        $initials = implode( '', $initials );
		}

    if ( $match_on & MATCH_ATOM ) {
        // is `$query` one of the atoms in item? Similar to substring, but $scores more highly, as it's
        // a word within the item
        if ( in_array( $query, $atoms ) ) {
            $score = 100.0 - ( strlen( $value ) / strlen( $query ) );
            return [ $score, MATCH_ATOM ];
        }
		}
    # `$query` matches start (or all) of the initials of the
    # atoms, e.g. ``himym`` matches "How I Met Your Mother"
    # *and* "how i met your mother" (the ``capitals`` rule only
    # matches the former)
    if ( ( $match_on & MATCH_INITIALS_STARTSWITH ) && ( 0 === strpos( $initials, $query ) ) ) {
        $score = 100.0 - ( strlen( $initials ) / strlen( $query ) );
        return [ $score, MATCH_INITIALS_STARTSWITH ];
		} else if ($match_on & MATCH_INITIALS_CONTAIN && (false !== strpos( $initials, $query ) ) ) {
    # `query` is a substring of initials, e.g. ``doh`` matches
    # "The Dukes of Hazzard"
        $score = 95.0 - (strlen($initials) / strlen($query));
        return [$score, MATCH_INITIALS_CONTAIN];
		}

    # `query` is a substring of item
    if ( ( $match_on & MATCH_SUBSTRING ) && ( false !== strpos( strtolower( $value ), $query ) ) ) {
        $score = 90.0 - ( strlen( $value ) / strlen( $query ) );
        return [ $score, MATCH_SUBSTRING ];
    }

    # finally, assign a $score based on how close together the
    # characters in `query` are in item.
    /**
     * @todo Rework the scoring on this part
     */
    if ( $match_on & MATCH_ALLCHARS ) {
    	foreach( str_split( $query ) as $character ) :
    		$position[] = strpos( $value, $character );
  		endforeach;
  		$divisor = ( ( 1 + reset($position) ) * ( ( abs( end($position) - reset($position) ) ) ) );
  		// protect from divide by 0 warnings
  		if ( $divisor == 0 ) {
  			$divisor = 1;
  		}
      $score = 100.0 / $divisor;
      $score = $score * ( strlen($query) / strlen($value) );
      return [$score, MATCH_ALLCHARS ];
    }

    // Nothing matched, so return a score of 0
    return [0, 'None'];
	}

}

