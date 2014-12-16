<?php

namespace Alphred;

class Date {

    public function __construct() {
      $this->avoid_date_errors();
    }

    public function avoid_date_errors() {
      // Set date/time to avoid warnings/errors.
      if ( ! ini_get( 'date.timezone' ) ) {
        $timezone = exec( 'tz=`ls -l /etc/localtime` && echo ${tz#*/zoneinfo/}' );
        ini_set( 'date.timezone', $timezone );
      }
    }


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

      foreach ( $legend as $singular => $values ) :
        if ( $seconds >= $values['value'] ) {
          $value = floor( $seconds / $values['value']);
          if ( $words )
            $value =  $this->convert_number_to_words( $value );

          if ( $seconds / $values['value'] >= 2 ) {
            $data[ $values['multiple'] ] = $value;
          } else {
            if ( $words )
              $data[ $singular ] = $value;
          }
          $seconds = $seconds % $values['value'];
        }
      endforeach;
      if ( $type == 'array' )
        return $data;

      $string = '';
      $count  = 1;
      foreach( $data as $unit => $value ) :
        $string .= "{$value} {$unit}";
        if ( $count == count( $data ) )
          return $string;
        else if ( $count + 1 == count( $data ) )
          $string .= ", and ";
        else
          $string .= ", ";
        $count++;
      endforeach;

    }

    public function ago( $seconds, $words = false ) {
      // Only goes back to the Unix Epoch (1-Jan 1970)
      $seconds = ( $seconds - time() ) * - 1; // this needs to be converted with the date function
      return $this->seconds_to_human_time( $seconds, $words, 'string' ) . ' ago';
    }

    public function convert_number_to_words( $number ) {
        // from http://www.karlrixon.co.uk/writing/convert-numbers-to-words-with-php/
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
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
        );

        if (!is_numeric($number)) {
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
            return $negative . $this->convert_number_to_words( abs( $number ) );
        }

        $string = $fraction = null;

        if ( strpos( $number, '.' ) !== false ) {
            list( $number, $fraction ) = explode( '.', $number );
        }

        switch ( true ) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ( (int) ($number / 10) ) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ( $units ) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convert_number_to_words( $remainder );
                }
                break;
            default:
                $baseUnit = pow( 1000, floor( log( $number, 1000 ) ) );
                $numBaseUnits = (int) ( $number / $baseUnit );
                $remainder = $number % $baseUnit;
                $string = $this->convert_number_to_words( $numBaseUnits ) . ' ' . $dictionary[$baseUnit];
                if ( $remainder ) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convert_number_to_words( $remainder );
                }
                break;
        }

        if ( null !== $fraction && is_numeric( $fraction ) ) {
            $string .= $decimal;
            $words = array();
            foreach ( str_split( (string) $fraction ) as $number ) {
                $words[] = $dictionary[$number];
            }
            $string .= implode( ' ', $words );
        }

        return $string;
    }

}