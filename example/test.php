<?php

require_once( '../Main.php' );

// $string = "on Upon my arrival, and upon, my, on something else.";
// print Alphred\Text::title_case( $string );

$array = [ 'one' => 'two', 'three' => 'and four' ];
print http_build_query( $array );

exit(0);


