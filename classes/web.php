<?php

namespace Alphred;

class http {

 public function post(  $url,
                        $params = [],
                        $cache  = [ 'bin' => '', 'ttl' => 600 ],
                        $credentials = false ) {
    if ( $cache ) {
      if ( $file = $this->cache( md5( $path . implode(' ', $params ) ), $cache[ 'bin' ], $cache[ 'ttl' ] ) ) {
        return file_get_contents( $file );
      }
    }

    $this->request( $path );
    curl_setopt( $this->c, CURLOPT_POST, 1 );
    if ( count( $vars ) > 0 ) {
        curl_setopt( $this->c, CURLOPT_POSTFIELDS, $params );
    }
    if ( $credentials && is_array( $credentials ) ) {
      if ( ! isset( $credentials['user'] ) || ! isset( $credentials['pass'] ) ) {
        // I need to throw an exeception
        return false;
      }
      curl_setopt( $this->c, CURLOPT_USERPWD, "[{$credentials['user']}]:[{$credentials['pass']}]" );
    }
    $result = curl_exec( $this->c );
    // This needs error handlings
    // curl_error(ch)
    curl_close( $this->c );
    return $result;
  }

  public function get( $url, $params = [], $cache = true ) {
    // Function to simplify a get request
    if ( count( $vars ) > 0 ) {
      $fields = '?';
      foreach( $vars as $k => $v ) :
        $fields .= "{$k}={$v}&";
      endforeach;
      $fields = substr( $fields, 0, -1 );
      $url = $url . $fields;
    }

    $this->request( $url );
    $result = curl_exec( $this->c );
    // This needs error handlings
    // curl_error(ch)
    curl_close( $this->c );
    return $result;

  }

  private function request( $url ) {
      $this->c = curl_init();
      curl_setopt_array( $this->c, [
          CURLOPT_URL => filter_var( $url, FILTER_SANITIZE_URL ),
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_FAILONERROR => 1,
          CURLOPT_FOLLOWLOCATION => 1,
      ]);
  }

  public function simple_download( $url, $destination = '', $mkdir = false ) {
    // Function to download a URL easily.
    $url = filter_var( $url, FILTER_SANITIZE_URL );
    if ( empty( $destination ) )
      return file_get_contents( $url );
    else {
      if ( file_exists( $destination ) && is_dir( $destination ) ) {
        $destination = $destination . '/' . basename( parse_url( $url, PHP_URL_PATH ) );
      }
      file_put_contents( $destination, file_get_contents( $url ) );
    }
    return $destination;
  }

  public function get_favicon( $url, $destination = '', $cache = true, $ttl = 604800 ) {
    $url = parse_url( $url );
    $domain = $url['host'];
    if ( $cache && $file = $this->cache( "{$domain}.png", 'favicons', $ttl ) ) {
      return $file;
    }
    $favicon = file_get_contents( "https://www.google.com/s2/favicons?domain={$domain}" );
    if ( empty( $destination ) ) {
      $destination = Globals::get('alfred_workflow_cache') . "/favicons";
    }
    if ( ! file_exists( $destination ) && substr( $destination, -4 ) !== '.png' ) {
      mkdir( $destination, 0755, true );
    }
    if ( file_exists( $destination ) && is_dir( $desintation ) ) {
      $destination .= "/{$domain}.png";
    }

    file_put_contents( $destination, $favicon );
    return $destination;
  }

  public function cache( $file, $bin = '', $ttl = 600 ) {
    $file = Globals::get('alfred_workflow_cache') . "/{$bin}/{$file}";
    if ( ! file_exists( $file ) ) return false;
    if ( time() - filemtime( $file ) > $ttl && $ttl !== 0 ) return false;
    return $file;
  }

  public function clear_cache( $bin = '' ) {
    $clear = Globals::get('alfred_workflow_cache');
    $clear .= ( $bin ) ? $bin : '';
    if ( file_exists( $clear ) && is_dir( $clear ) ) {
      // remove all the files from the bin. If the bin isn't set, then remove
      // all from the root cache. If it is, also remove the bin folder.
    }
  }




}