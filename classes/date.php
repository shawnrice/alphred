<?php

namespace Alphred;

class Date {

public function __construct() {
  $this->avoidDateErrors();
}

public function avoidDateErrors() {
  // Set date/time to avoid warnings/errors.
  if ( ! ini_get('date.timezone') ) {
    $timezone = exec( 'tz=`ls -l /etc/localtime` && echo ${tz#*/zoneinfo/}' );
    ini_set( 'date.timezone', $timezone );
  }
}

public function convertSecondsToHumanTime( $time ) {

  if ( $time < 0 )
    $time = $time * -1;

  if ( $time < 60 ) {
    if ( $time > 1 )
      return "$time seconds";
    else
      return "$time second";
  }

  // Error checking needs to go here.
  $seconds = $time % 60;
  $time    = ( $time - $seconds ) / 60;
  $minutes = $time % 60;
  $time    = ( $time - $minutes ) / 60;
  $hours   = $time % 24;
  $time    = ( $time - $hours ) / 24;
  $days    = $time % 30;
  $time    = ( $time - $days ) / 30;
  // $weeks   = $time % 30; // This is just an approximation
  // $time    = ( $time - $weeks ) / 30;
  $months  = $time % 12;
  $time    = ( $time - $months ) / 12;
  $years   = $time % 10;
  $time    = ( $time - $years ) / 10;
  $decades = $time % 10;
  $time    = ( $time - $decades ) / 10;
  $centuries = $time;
  // $months
  // $years

  return [ 'seconds' => $seconds,
           'minutes' => $minutes,
           'hours'   => $hours,
           'days'    => $days,
           'weeks'   => $weeks,
           'months'  => $months,
           'years'   => $years,
           'decades' => $decades,
           'centuries' => $centuries
         ];

}

public function ago( $time, $ago = true ) {

  $this->avoidDateErrors();

  $now = time();

  $past = true;
  if ( ! ( ( $now - $time ) > 0 ) ) {
    $past = false;
  }



}

}