<?php

$phar = new Phar( 'build/alphred.phar',
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 'alphred.phar' );
foreach( array_diff( scandir( 'classes' ), ['.', '..', '.DS_Store' ] ) as $filename ) :
    $phar[$filename] = file_get_contents( 'classes/' . $filename );
endforeach;
$phar->setStub( $phar->createDefaultStub( 'alphred.php' ) );

// copy($srcRoot . "/config.ini", $buildRoot . "/config.ini");
