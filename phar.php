<?php
/**
 * This file builds Alphred as a .phar
 *
 */

// Create a new phar named Alphred.phar
$phar = new Phar( 'build/Alphred.phar',
                  FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
                  'Alphred.phar'
);

// Cycle through these directories and include everything
foreach( [ 'classes', 'scripts', 'commands' ] as $directory ) :
	foreach( array_diff( scandir( $directory ), ['.', '..', '.DS_Store' ] ) as $filename ) :
	    $phar[ "{$directory}/{$filename}" ] = file_get_contents( "{$directory}/{$filename}" );
	endforeach;
endforeach;

// Set "classes/Alphred.php" as the default
$phar->setStub( $phar->createDefaultStub( 'classes/Alphred.php' ) );