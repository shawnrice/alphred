<?php

require_once( 'system.php' );

$a = new Alphred\System;
$a->fork( '/usr/bin/bash', "'{$_SERVER['PWD']}' 'once' 'twice' 'thrice'" );

// $a = new Alphred\System;
// $a->tar( 'alphred', glob( '..' ) );

// if ( ! file_exists( "/Users/Sven/Desktop/Alphred" ) ) {
//     mkdir( "/Users/Sven/Desktop/Alphred" );
// }

// $a->extract( 'alphred.tar.gz', "/Users/Sven/Desktop/Alphred" );

// $a->tar( 'archive', glob( '..' ) );

// $tests = [ '.test', 'test2' ];

// foreach ( $tests as $string ) :
// preg_match( "/^[^.]*/", $string, $matches);
// print_r( $matches );
// endforeach;