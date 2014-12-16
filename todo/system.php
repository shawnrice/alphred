<?php

namespace Alphred;


class System {

//       public function background() {

//       }

//       public function fork( $program, $cmd ) {

//         $script = <<< EOB
// #!/bin/bash
// nohup {$program} {$cmd} 2>&1 > /dev/null &
// echo $?

// EOB;
//         // $file = "/tmp/fork-" . md5( $script ) . '.sh';
//         // file_put_contents( $file, $script );
//         // $pid = exec( "bash $file" );

//         echo $script;

//       }

//       public function args() {
//           // sanitize cmd args
//       }


    function version( $verbose = false ) {
      if ( ! $verbose ) {
        return substr( exec( 'sw_vers -productVersion' ), strpos( $version, '.', 3 ) );
      } else {
        $version = exec( 'sw_vers -productVersion' );
        $versions = array( 'Yosemite' => '10.10', 'Mavericks' => '10.9',
          'Mountain Lion' => '10.8', 'Lion' => '10.7', 'Snow Leopard' => '10.6' );
        foreach ( $versions as $k => $v ) :
          if ( strpos( $version, $v ) ) {
            return $k;
          }
        endforeach;
      }
      return false;
    }

//////////// Zip Functions using ZipArchive

  private function recurse_directory_zip( $directory ) {
    echo "Adding directory: ${directory}" . PHP_EOL;
    if ( ! is_dir( $directory ) ) { return false; }
    if ( ! isset( $this->archive ) ) { return false; } // well, throw an exception
    if ( empty( $directory ) ) { return false; }

    foreach ( glob( "{$directory}/*" ) as $file ) :
      if ( is_file( $file ) ) {
        if ( $file == '.DS_Store' ) { continue; }
        $this->archive->addFile( "{$file}", str_replace( $this->temp_path, '', $file ) );
      } else if ( is_dir( $file ) ) {
        $this->recurse_directory_zip( $file );
      }
    endforeach;

  }

  public function zip( $zipfile, $files = [] ) {

    if ( count( $files ) == 0 ) { return false; }
    if ( file_exists( $zipfile ) ) { unlink( $zipfile ); }

    $this->archive = new \ZipArchive;
    $zip = $this->archive->open( $zipfile , \ZipArchive::OVERWRITE);

    foreach ( $files as $file ) :
      if ( ! file_exists( $file ) ) {
        // throw some sort of exception
        continue;
      }
      $file = realpath( $file );
      if ( is_file( $file ) ) {
        if ( $file == '.DS_Store' ) { continue; }
        $this->archive->addFile( $file, basename( $file ) );
      } else if ( is_dir( $file ) ) {
        $this->temp_path = str_replace( basename( $file ), '', $file );
        $this->recurse_directory_zip( $file );
      }
    endforeach;
    $this->archive->close();
    unset( $this->archive );
    unset( $zip );

  }

  public function unzip( $zipfile, $destination ) {

    if ( ! file_exists( $zipfile ) ) { return false; } // or throw an exception
    if ( ! file_exists( $destination ) || ! is_dir( $destination ) ) {
      // actually, throw an exception
      return false;
    }

    $zip = new \ZipArchive;
    if ( $zip->open( $zipfile ) === true ) {
        $zip->extractTo( $destination );
        $zip->close();
    } else {
        // throw an exception
    }
  }


//////////// END Zip Functions using ZipArchive


  public function tar( $filename, $files = [] ) {
    // We're using the PharData class here.
    // http://php.net/manual/en/class.phardata.php

    if ( file_exists( "{$filename}.tar" ) ) { unlink( "{$filename}.tar" ); }
    if ( file_exists( "{$filename}.tar.gz" ) ) { unlink( "{$filename}.tar.gz" ); }

    $archive = new \PharData( "{$filename}.tar" );

    try {
      foreach ( $files as $file ) :
        if ( ! file_exists( $file ) ) {
          // throw an exception
          continue;
        }

        if ( is_file( $file ) ) {
          $archive->addFile( $file );
        } else if ( is_dir( $file ) ) {
          $archive->buildFromDirectory( $file );
        }
      endforeach;
    } catch ( Exception $e ) {
        echo "Exception : " . $e;
    }
    $archive->compress( \Phar::GZ );
    unlink( "{$filename}.tar" );
  }


  public function phar_zip( $filename, $files = [], $skip_dots = true ) {
    // We're using the PharData class here.
    // http://php.net/manual/en/class.phardata.php

    if ( file_exists( "{$filename}.zip" ) ) { unlink( "{$filename}.zip" ); }

    if ( $skip_dots ) {
      $archive = new \PharData( "{$filename}.zip" );
    } else {
      $archive = new \PharData( "{$filename}.zip" );
    }


    try {
      foreach ( $files as $file ) :
        if ( ! file_exists( $file ) ) {
          // throw an exception
          continue;
        }

        if ( is_file( $file ) ) {
          $archive->addFile( $file );
        } else if ( is_dir( $file ) ) {
          $archive->buildFromDirectory( $file );
        }
      endforeach;
    } catch ( Exception $e ) {
        echo "Exception : " . $e;
    }
  }

  public function untar() {

  }

  public function tgz() {
    $file = "test.txt";
    $gzfile = "test.gz";
    $fp = gzopen ($gzfile, 'w9'); // w9 == highest compression
    gzwrite ($fp, file_get_contents($file));
    gzclose($fp);
  }

  public function untgz() {

  }

  public function extract( $archive, $destination ) {
    if ( ! file_exists( $archive ) ) {
      return false;
      // actually, throw an exception
    }
    if ( ! file_exists( $destination ) || ! is_dir( $destination ) ) {
      return false;
      // actually, throw an exception
    }
    $archive = new \PharData( $archive );
    $archive->extractTo( $destination );
  }

  public function mount_dmg( $dmg ) {
    // These should throw exceptions...
    if ( ! file_exists( $dmg ) ) { return false; }
    if ( 'dmg' != strtolower( pathinfo( $dmg, PATHINFO_EXTENSION) ) ) { return false; }
    return exec( "hdiutil attach -nobrowse -quiet {$dmg}" );
  }

  public function unmount_dmg( $volume ) {
    if ( file_exists( "/Volumes/{$volume}" ) && is_dir( "/Volumes/{$volume}" ) )
      return exec( "hdiutil detach -quiet /Volumes/{$volume}" );
    return false;
  }


}