<?php

// A simple file that will run in the background
require_once( __DIR__ . '/../../Main.php' );

if ( file_exists( 'background-test-file.txt' ) ) {
	unlink( 'background-test-file.txt' );
}

if ( Alphred\Globals::is_background() ) {
	file_put_contents( 'background-test-file.txt', 'background' );
} else {
	file_put_contents( 'background-test-file.txt', 'foreground' );
}