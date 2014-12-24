<?php

// File to build the phar...

$phar = new Phar( 'build/Alphred.phar',
                  FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
                  'Alphred.phar'
);

foreach( array_diff( scandir( 'classes' ), ['.', '..', '.DS_Store' ] ) as $filename ) :
    $phar[ "classes/{$filename}" ] = file_get_contents( 'classes/' . $filename );
endforeach;
foreach( array_diff( scandir( 'scripts' ), ['.', '..', '.DS_Store' ] ) as $filename ) :
    $phar[ "scripts/{$filename}" ] = file_get_contents( 'scripts/' . $filename );
endforeach;
$phar->setStub( $phar->createDefaultStub( 'Alphred.php' ) );
