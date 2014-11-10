<?php

namespace Alphred\System;


class System {

    public function background() {

    }

    public function fork() {

    }

    public function args() {
        // sanitize cmd args
    }


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



public function zip() {

}

public function unzip() {

}

public function tar() {

}

public function untar() {

}

public function tgz() {

}

public function untgz() {

}

public function extract() {
    // determine what kind of archive it is
    // pass it to the appropriate function
}

public function dmg() {

}


}