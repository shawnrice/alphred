<?php

require_once( '../Main.php' );

$values = [
	1429341045,
	1423341345,
	1423311045,
	1423331045,
	1423141045,
	1423221045,
	1423333345,
	1423312145,
	1
];

	// These don't really count for leap years, but, whatever
	// $units = [
	// 		'second' 		=> 1,
	// 		'minute' 		=> 60,
	// 		'hour' 			=> 3600,
	// 		'day' 			=> 86400,
	// 		'week'	 		=> 604800,
	// 		'month' 		=> 2592000,
	// 		'year' 			=> 31536000,
	// 		'decade' 		=> 315360000,
	// 		'century' 	=> 3153600000,
	// 		'millenium' => 31536000000,
	// ];

$base = 360;
for ( $i=1; $i < 100000; $i *= 1.25 ) {
	$number = 1 + ( $i / 10 );
	print( Alphred\Date::fuzzy_ago( time() - ( $base * $number ) ) );
}

foreach( $values as $value ) {
	print Alphred\Date::fuzzy_ago( $value );
}

exit(0);


