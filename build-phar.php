<?php
/**
 * This file builds Alphred as a .phar
 */

// Create a new phar named Alphred.phar
$phar = new Phar( 'build/Alphred.phar',
                  FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
                  'Alphred.phar'
);

$phar[ "Main.php" ] = file_get_contents( __DIR__ . "/Main.php" );

// Cycle through these directories and include everything
foreach( [ 'classes', 'scripts', 'commands' ] as $directory ) :
	foreach( array_diff( scandir( $directory ), ['.', '..', '.DS_Store' ] ) as $filename ) :
	    $phar[ "{$directory}/{$filename}" ] = file_get_contents( __DIR__ . "/{$directory}/{$filename}" );
	endforeach;
endforeach;

// Add in other files
$other_files = [
	'apigen.neon',
	'build-phar.php',
	'build.xml',
	'code-standards.xml',
	'phpdoc.dist.xml',
	'phpunit.xml.dist',
	'README.md',
];

foreach ( $other_files as $file ) :
	$phar[ $file ] = file_get_contents( __DIR__ . '/' . $file );
endforeach;

// Set "classes/Alphred.php" as the default
$phar->setStub( $phar->createDefaultStub( 'Main.php' ) );