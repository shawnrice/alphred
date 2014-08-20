<?php

namespace Alphred\Date;

class Date {

function avoidDateErrors() {
  // Set date/time to avoid warnings/errors.
  if ( ! ini_get('date.timezone') ) {
    $tz = exec( 'tz=`ls -l /etc/localtime` && echo ${tz#*/zoneinfo/}' );
    ini_set( 'date.timezone', $tz );
  }
}

function convertSecondsToHumanTime( $time ) {

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
  $days    = $time % 7;
  $time    = ( $time - $days ) / 7;
  $weeks   = $time; // Expand from here.

}

function ago( $time, $ago = TRUE ) {

  avoidDateErrors();

  $now = mktime();

  $past = TRUE;
  if ( ! ( ( $now - $time ) > 0 ) ) {
    $past = FALSE;
  }



}

}