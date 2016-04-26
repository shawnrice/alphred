<?php
/**
 * Contains Date class for Alphred
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
 * Provides text filters for date objects
 *
 * This class should be cleaned up quite a bit, and it needs to be made pluggable
 * so that it can be used by languages other than English. But, _I think_ right now
 * it is good enough to be released because it falls into "special sauce" rather
 * than necessary functionality.
 *
 * @todo Abstract the time dictionaries so that they can be translated
 * @todo Add in a less precise version of "seconds to human time"
 * @todo Make these work with dates before Jan 1, 1970
 *
 */
class Date {

	private static $legend_english = [
		'millenium' => ['multiple' => 'millenia',  'value' => 31536000000 ],
		'century'   => ['multiple' => 'centuries', 'value' => 3153600000  ],
		'decade'    => ['multiple' => 'decades',   'value' => 315360000   ],
		'year'      => ['multiple' => 'years',     'value' => 31536000    ],
		'month'     => ['multiple' => 'months',    'value' => 2592000     ],
		'week'      => ['multiple' => 'weeks',     'value' => 604800      ],
		'day'       => ['multiple' => 'days',      'value' => 86400       ],
		'hour'      => ['multiple' => 'hours',     'value' => 3600        ],
		'minute'    => ['multiple' => 'minutes',   'value' => 60          ],
		'second'    => ['multiple' => 'seconds',   'value' => 1           ]
	];

	/**
	 * Returns a slightly modified array of the difference between two dates
	 *
	 * @since 1.0.0
	 * @todo Fix for values before Jan 1, 1970
	 *
	 * @param  int $date1 a date in seconds
	 * @param  int $date2 a date in seconds
	 * @return array  an array that represents the difference in granular units
	 */
	private function diff_a_date( $date1, $date2 ) {

		$date1 = new \DateTime( date( 'D, d M Y H:i:s', $date1 ) );
		$date2 = new \DateTime( date( 'D, d M Y H:i:s', $date2 ) );
		$diff  = $date1->diff( $date2 );

		$millenia = floor( $diff->y / 1000 );
		$diff->y = $diff->y % 1000;
		$centuries = floor( $diff->y / 100 );
		$diff->y = $diff->y % 100;
		$decades = floor( $diff->y / 10 );
		$diff->y = $diff->y % 10;

		return [
			'units' => [
				'decades' => $decades,
				'years'   => $diff->y,
				'months'  => $diff->m,
				// Calculate weeks
				'weeks'   => floor( $diff->d / 7 ),
				// Calculate leftover days
				'days'    => $diff->d % 7,
				'hours'   => $diff->h,
				'minutes' => $diff->i,
				'seconds' => $diff->s,
			],
			// Is the date in the past or the future?
			'tense' => ( ( 0 === $diff->invert ) ? 'past' : 'future' ),
		];

	}

	/**
	 * Converts a time diff into a human readable approximation
	 *
	 * @since 1.0.0
	 * @todo Fix for values before Jan 1, 1970
	 * @todo make available for non-English languages
	 *
	 * @param int     $seconds a date represented in seconds since the unix epoch
	 * @param string  $dictionary what language to use (currently unsupported and ignored)
	 * @return string       a fuzzy time
	 */
	public function fuzzy_ago( $seconds, $dictionary = 'english' ) {

		if ( $seconds < 0 ) {
			return false;
		}

		// Do a quick diff
		$diff = self::diff_a_date( $seconds, time() );
		// Get the tense
		$tense = $diff['tense'];
		// Get the units
		$times = $diff['units'];
		// We want it a bit more granular...

		// Table of how many are in the next
		$post_units = [
			'seconds'   => 60, // 60 seconds in a minute
			'minutes'   => 60, // 60 minutes in an hour
			'hours'     => 24, // 24 hours in a day
			'days'      => 7,  // 7 days in a week
			'weeks'     => 4,  // 4 weeks in a month
			'months'    => 12, // 12 months in a year
			'years'     => 10, // 10 years in a decade
			'decades'   => 10, // 10 decades in a century
			'centuries' => 10, // 10 centuries in a millenia
		];

		// Plural => singular translation table
		$singular = [
			'seconds'   => 'second',
			'minutes'   => 'minute',
			'hours'     => 'hour',
			'days'      => 'day',
			'weeks'     => 'week',
			'months'    => 'month',
			'years'     => 'year',
			'decades'   => 'decade',
			'centuries' => 'century',
		];

		// It's weird to say "last minute," so we'll say "a minute ago," etc...
		$special = [
			'seconds' => ['past' => 'just now', 		 'future' => 'in a second'],
			'minutes' => ['past' => 'a minute ago', 'future' => 'in a minute'],
			'hours'   => ['past' => 'an hour ago',  'future' => 'in an hour'  ],
			'days'    => ['past' => 'yesterday',  	 'future' => 'tomorrow'    ]
		];

		// Set preliminary tense prefix and suffix strings
		if ( 'past' === $tense ) {
			$tense_prefix = '';
			$tense_suffix = ' ago';
		} else {
			$tense_prefix = 'in ';
			$tense_suffix = '';
		}

		// We're going to define two thresholds to use. These will indicate whether or not we
		// should use the next unit up to define the time.
		$threshold1 = 0.6;
		$threshold2 = 0.8;

		// Cycle through the array to try to find the right values
		foreach ( $times as $unit => $value ) :
			if ( ( 0 === $value ) && ( ! isset( $main_unit ) ) ) {
				$previous_unit = $unit;
			} else if ( isset( $main_unit ) ) {
				$next_unit  = $unit;
				$next_value = $value;
				break;
			}
			if ( ( 0 !== $value ) && ( ! isset( $main_unit ) ) ) {
				$main_unit  = $unit;
				$main_value = $value;
			}
		endforeach;

		// Add on the remainder of the "next unit" so that we can get it in base 10
		$main_value += ( $post_units[ $next_unit ] ) ? ( $next_value / $post_units[ $next_unit ] ) : 0;

		// So, we've defined two thresholds that will have us "round up" to the next
		// unit (i.e. day -> week and week->month). Check, first, if they're close enough
		// that we should use the greater unit.
		if ( $main_value / $post_units[ $main_unit ] > $threshold2 ) {
			// The first threshold ($threshold2) rounds to "almost a {next unit}"
			// So, "almost a week" instead of "5 days ago"
			if ( 'hours' === $singular[ $previous_unit ] ) {
				$string = "almost an {$singular[ $previous_unit ]}";
			} else {
				$string = "almost a {$singular[ $previous_unit ]}";
			}
		} else if ( $main_value / $post_units[ $main_unit ] > $threshold1 ) {
			// The second threshold rounds to "last {next unit}"
			// So, "last week" or "next week" instead of "4 days ago"

			if ( isset( $special[ $previous_unit ] ) ) {
				$string = $special[ $previous_unit ][ $tense ];
				$tense_prefix = '';
				$tense_suffix = '';
			} else {
				$string = "{$singular[ $previous_unit ]}";
				if ( $tense_prefix ) {
					$tense_prefix = 'next ';
				} else {
					$tense_prefix = 'last ';
					$tense_suffix = '';
				}
			}
		} else {
			// If it's close enough to 1, then we'll use a singular
			if ( 1 === $main_value || 1 === round( $main_value ) ) {
				if ( isset( $special[ $main_unit ] ) ) {
					$string = $special[ $main_unit ][ $tense ];
					$tense_prefix = '';
					$tense_suffix = '';
				} else {
					$string = "a {$singular[ $main_unit ]}";
				}
			} else if ( 2 === $main_value || 2 === round( $main_value ) ) {
				// If it's close enough to 2, then we'll use 'a couple'
				$string = "a couple {$main_unit}";
			} else {
				// We'll default to 'a few' because other cases should have already been taken care of.
				$string = "a few {$main_unit}";
			}
		}

		// We're going to return a string that has a prefix, the main string, and a suffix.
		// The prefix and suffix may be empty, but that depends on whether or not it's in the future
		// or the past and a few other things.
		return "{$tense_prefix}{$string}{$tense_suffix}";

	}

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
		$legend = self::$legend_english;

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
		if ( 'array' === $type ) {
			return $data;
		}

		// We want a string, so let's convert it to one with an Oxford Comma
		// because Oxford Commas are important. If you don't agree, then look here:
		// http://stephentall.org/2011/09/19/oxford-comma/
		// If you still don't agree, "fuck off," says the grammarian.
		// This. is. not. optional.
		return Text::add_commas_to_list( $data, true );
	}

	/**
	 * Explains how long ago something happened...
	 *
	 * This also works with the future.
	 *
	 * @since 1.0.0
	 * @todo Make this work with values before 1 Jan, 1970
	 *
	 * @param  integer  $seconds  a number of seconds
	 * @param  boolean 	$words   	whether or not to return numerals or the word-equivalent
	 * @return string             a string indicating a time in words
	 */
	public function ago( $seconds, $words = false ) {
		$tense = 'past';
		$seconds = ( time() - $seconds ); // this needs to be converted with the date function
		if ( $seconds < 0 ) {
			$tense = 'future';
			$seconds = abs( $seconds ); // We need a positive number
		}

		$string = Date::seconds_to_human_time( $seconds, $words, 'string' );
		if ( 'past' === $tense ) {
			return "{$string} ago";
		} else {
			return "in {$string}";
		}
	}

	/**
	 * Converts a number to words
	 *
	 * @todo Add in an option for a shorter version...
	 * @todo Add in translation options so that we don't support _only_ English
	 *
	 * @param  int $number a number
	 * @return string      the number, but, as words
	 */
	public function convert_number_to_words( $number, $dictionary = 'english' ) {
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
			1000000000000000000 => 'quintillion',
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
