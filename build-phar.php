<?php
/**
 * This file builds Alphred as a .phar
 */

// Create a new phar named Alphred.phar
$phar = new Phar( 'build/Alphred.phar',
                  FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
                  'Alphred.phar'
);

// Start buffering. Mandatory to modify stub.
$phar->startBuffering();

// We use 'Main.php' as the, well, main entry point for the library. It can understand if it is used as a
// command line tool or included as a library.
$defaultStub = $phar->createDefaultStub( 'Main.php' );


$phar[ "Main.php" ] = file_get_contents( __DIR__ . "/Main.php" );

// Cycle through these directories and include everything
foreach( [ 'classes', 'scripts', 'commands' ] as $directory ) :
	foreach( array_diff( scandir( $directory ), ['.', '..', '.DS_Store' ] ) as $filename ) :
	    $phar[ "{$directory}/{$filename}" ] = file_get_contents( __DIR__ . "/{$directory}/{$filename}" );
	endforeach;
endforeach;

// Add in other files
$other_files = [
	// 'apigen.neon',
	'build-phar.php',
	// 'build.xml',
	'Changelog.md',
	'code-standards.xml',
	'phpdoc.dist.xml',
	// 'phpunit.xml.dist', // Taking this out for now
	'README.md',
];


// Include the invidual files
foreach ( $other_files as $file ) :
	$phar[ $file ] = file_get_contents( __DIR__ . '/' . $file );
endforeach;

// Create a custom stub to add the shebang
// $stub = "#!/usr/bin/php \n" . $defaultStub;
$stub = $defaultStub;

// Add the stub
$phar->setStub( $stub );

$phar->stopBuffering();

// I should use php's chmod() function, but I'm going to cheat here to make it executable
exec( 'chmod +x build/Alphred.phar', $return, $code );
if ( 0 == $code ) {
	print "Built Phar\n";
} else {
	print "Problem occured building phar.\n";
}
exit( $code );
