<?php

namespace Alphred;

/**
 *
 * @todo Abstract the time dictionaries so that they can be translated
 *
 *
 */
class Date {

	/**
	 * Converts seconds to a human readable string or an array
	 *
	 * @param  integer  $seconds  a number of seconds
	 * @param  boolean  $words    whether or not the numbers should be numerals or words
	 * @param  string   $type     either 'string' or 'array'
	 * @return string|array 			a string or an array, depending on $type
	 */
	public function seconds_to_human_time( $seconds, $words = false, $type = 'string' ) {
		$data = [];
		$legend = [
		'millenium' => [ 'multiple' => 'millenia',  'value' => 31536000000 ],
		'century'   => [ 'multiple' => 'centuries', 'value' => 3153600000  ],
		'decade'    => [ 'multiple' => 'decades',   'value' => 315360000   ],
		'year'      => [ 'multiple' => 'years',     'value' => 31536000    ],
		'month'     => [ 'multiple' => 'months',    'value' => 2592000     ],
		'week'      => [ 'multiple' => 'weeks',     'value' => 604800      ],
		'day'       => [ 'multiple' => 'days',      'value' => 86400       ],
		'hour'      => [ 'multiple' => 'hours',     'value' => 3600        ],
		'minute'    => [ 'multiple' => 'minutes',   'value' => 60          ],
		'second'    => [ 'multiple' => 'seconds',   'value' => 1           ]
		];

		// Start with the greatest values and whittle down until we're left with seconds
		foreach ( $legend as $singular => $values ) :
			// If the seconds is greater than the unit, then do some math
			if ( $seconds >= $values['value'] ) {
				// How many units are in those seconds?
				$value = floor( $seconds / $values['value'] );
				if ( $words ) {
					// We want words, not numbers, so convert to words
					$value = Date::convert_number_to_words( $value );
				}
				// Did we get single or multiple?
				if ( $seconds / $values['value'] >= 2 ) {
					// Use plural units
					$data[ $values['multiple'] ] = $value;
				} else {
					// Use singlur units
					$data[ $singular ] = $value;
				}
				// Remove what we just converted and continue
				$seconds = $seconds % $values['value'];
			}
		endforeach;

		// If we want this as an array, then return that
		if ( 'array' == $type ) {
			return $data;
		}

		// We want a string, so let's convert it to one with an Oxford Comma
		// because Oxford Commas are important. If you don't agree, then look here:
		// http://stephentall.org/2011/09/19/oxford-comma/
		// Otherwise, "fuck off," says the grammarian.
		// This is not optional.
		return Text::add_commas_to_list( $data, true );
	}

	/**
	 * Explains how long ago something happened...
	 *
	 * @since 1.0.0
	 *
	 * @param  integer  $seconds  a number of seconds
	 * @param  boolean 	$words   	whether or not to return numerals or the word-equivalent
	 * @return string             a string indicating a time in words
	 */
	public function ago( $seconds, $words = false ) {
		// Only goes back to the Unix Epoch (1-Jan 1970)
		$seconds = ( $seconds - time() ) * - 1; // this needs to be converted with the date function
		return Date::seconds_to_human_time( $seconds, $words, 'string' ) . ' ago';
	}

	/**
	 * Converts a number to words
	 *
	 * @todo Add in an option for a shorter version...
	 *
	 * @param  [type] $number [description]
	 * @return [type]         [description]
	 */
	public function convert_number_to_words( $number ) {
		// This is a complex function, but I'm not sure if it can be simplified.
		// adapted from http://www.karlrixon.co.uk/writing/convert-numbers-to-words-with-php/
		$hyphen      = '-';
		$conjunction = ' and ';
		$separator   = ', ';
		$negative    = 'negative ';
		$decimal     = ' point ';
		// This is our map of numerals to letters
		$dictionary  = [
			0                   => 'zero',
			1                   => 'one',
			2                   => 'two',
			3                   => 'three',
			4                   => 'four',
			5                   => 'five',
			6                   => 'six',
			7                   => 'seven',
			8                   => 'eight',
			9                   => 'nine',
			10                  => 'ten',
			11                  => 'eleven',
			12                  => 'twelve',
			13                  => 'thirteen',
			14                  => 'fourteen',
			15                  => 'fifteen',
			16                  => 'sixteen',
			17                  => 'seventeen',
			18                  => 'eighteen',
			19                  => 'nineteen',
			20                  => 'twenty',
			30                  => 'thirty',
			40                  => 'fourty',
			50                  => 'fifty',
			60                  => 'sixty',
			70                  => 'seventy',
			80                  => 'eighty',
			90                  => 'ninety',
			100                 => 'hundred',
			1000                => 'thousand',
			1000000             => 'million',
			1000000000          => 'billion',
			1000000000000       => 'trillion',
			1000000000000000    => 'quadrillion',
			1000000000000000000 => 'quintillion'
		];

		if ( ! is_numeric( $number ) ) {
			// You didn't feed this a number
			return false;
		}

		if ( ( $number >= 0 && (int) $number < 0 ) || (int) $number < 0 - PHP_INT_MAX ) {
			// overflow
			trigger_error(
				'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ( $number < 0 ) {
			// The number is negative, so re-run the function with the positive value but prepend the negative sign
			return $negative . Date::convert_number_to_words( abs( $number ) );
		}

		$string = $fraction = null;

		if ( false !== strpos( $number, '.' ) ) {
			list( $number, $fraction ) = explode( '.', $number );
		}

		// We're going to run through what we have now
		switch ( true ) {
			case $number < 21:
				$string = $dictionary[ $number ];
				break;
			case $number < 100:
				$tens   = ( (int) ( $number / 10 ) ) * 10;
				$units  = $number % 10;
				$string = $dictionary[ $tens ];
				if ( $units ) {
					$string .= $hyphen . $dictionary[ $units ];
				}
				break;
			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[ $hundreds ] . ' ' . $dictionary[100];
				if ( $remainder ) {
					// We have some leftover number, so let's run the function again on what's left
					$string .= $conjunction . Date::convert_number_to_words( $remainder );
				}
				break;
			default:
				$baseUnit = pow( 1000, floor( log( $number, 1000 ) ) );
				$numBaseUnits = (int) ( $number / $baseUnit );
				$remainder = $number % $baseUnit;
				$string = Date::convert_number_to_words( $numBaseUnits ) . ' ' . $dictionary[ $baseUnit ];
				if ( $remainder ) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					// We have some leftover number, so let's run the function again on what's left
					$string .= Date::convert_number_to_words( $remainder );
				}
				break;
		}

		if ( null !== $fraction && is_numeric( $fraction ) ) {
			$string .= $decimal;
			$words = [];
			foreach ( str_split( (string) $fraction ) as $number ) {
				$words[] = $dictionary[ $number ];
			}
			$string .= implode( ' ', $words );
		}

		return $string;
	}

}