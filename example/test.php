<?php

print "Starting a shell script\n";
$script = './dselay.php';

if ( file_exists( $script ) ) {
	if ( is_array( $arg ) ) {
		$arg = implode( ' ', $arg );
	}
	if ( false !== strpos( $arg, '"' ) ) {
		$arg = str_replace( '"', '\"', $arg )
	}
	exec( "/usr/bin/nohup php '{$script}' >/dev/null 2>&1 &", $output, $return );
} else {
	print "Script does not exist";
}



function background( $script, $args = false ) {
	// Make sure that the script
	if ( ! file_exists( $script ) ) {
		throw new Exception( "Script `{$script}` does not exist." );
	}
	if ( $args ) {
		if ( is_array( $args ) ) {
			// Turn $args into a string if we were passed an array
			$args = implode( ' ', $args );
		} else {
			// Quote args if it is a string
			$args = "'{$args}'";
		}
		$args = str_replace( '"', '\"', $args );
	}
	exec( "/usr/bin/nohup php '{$script}' {$args}  >/dev/null 2>&1 &", $output, $return );
}