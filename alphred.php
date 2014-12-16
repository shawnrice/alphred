<?php
// http://www.alfredforum.com/topic/4716-some-new-alfred-script-environment-variables-coming-in-alfred-24/#entry28819

 // "alfred_preferences" = "/Users/Crayons/Dropbox/Alfred/Alfred.alfredpreferences";
 //    "alfred_preferences_localhash" = adbd4f66bc3ae8493832af61a41ee609b20d8705;
 //    "alfred_theme" = "alfred.theme.yosemite";
 //    "alfred_theme_background" = "rgba(255,255,255,0.98)";
 //    "alfred_theme_subtext" = 3;
 //    "alfred_version" = "2.4";
 //    "alfred_version_build" = 277;
 //    "alfred_workflow_bundleid" = "com.alfredapp.david.googlesuggest";
 //    "alfred_workflow_cache" = "/Users/Crayons/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/com.alfredapp.david.googlesuggest";
 //    "alfred_workflow_data" = "/Users/Crayons/Library/Application Support/Alfred 2/Workflow Data/com.alfredapp.david.googlesuggest";
 //    "alfred_workflow_name" = "Google Suggest";
 //    "alfred_workflow_uid" = "user.workflow.B0AC54EC-601C-479A-9428-01F9FD732959";

// so this should be just like the bundler in that this is the entry point
// and the rest is the backend that isn't quite necessary to expose but can be
// exposed
/**
 *
 * (1) Provides basic workflow necessities
 * (2) Provides basic string encryption for passwords
 * (3) Provides basic settings files with validation and reset options
 * (4) provides simplified auth/anon get/post cURL requests
 * (5) provides basic logging functionality
 * (6) provides basic caching mechanisms
 * (7) internationalization features??
 *
*/

class Alphred {

  private $results;

  public function __construct( $log = false, $locale = false ) {

    // Include the classes
    foreach ( scandir( __DIR__ . '/classes' ) as $file ) :
      if ( pathinfo( __DIR__ . "/{$file}", PATHINFO_EXTENSION ) == 'php' )
        require_once( __DIR__ . "/{$file}" );
    endforeach;


    $this->dir = exec('pwd'); // Only valid way that I think I can do
    $this->bundle = exec( "defaults read '{$this->dir}/info.plist' 'bundleid'" );
    $this->cache  = "{$_SERVER['HOME']}/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/{$this->bundle}";
    $this->data   = "{$_SERVER['HOME']}/Library/Application Support/Alfred 2/Workflow Data/{$this->bundle}";
    $this->salt  = 'ereNDrpSgW';

    $this->logging = $log;

    $this->log   = $this->cache . '/logs/' . mktime() . '-log.txt';
    $this->settings = $this->data . '/settings.json';

    // Make the relevant directories
    // See createDir() below
    createDir( $this->cache );
    createDir( $this->data  );
    createDir( $this->cache . '/logs' );
    $this->results = array();


  } /* End __contruct() */






  public function resetData() {
    // function wipes all data and destroys directories
    deleteDir( $this->data  );
  }

  public function resetCache() {
    // function wipes all data and destroys directories
    deleteDir( $this->cache );
  }


  /**
   * [cdata description]
   *
   * @param   [type]   $key    [description]
   * @param   [type]   $value  [description]
   * @param   [type]   $xml    [description]
   * @param   boolean  $mod    [description]
   *
   * @return  [type]           [description]
   */
  public function cdata( $key, $value, $xml, $mod = false ) {
    $xml->startElement( $key );
    if ( $mod )
      $xml->writeAttribute( $mod[0], $mod[1] );
    $xml->writeCData( $value );
    $xml->endElement();
  }


/**
 * Encrypts a string with a salted ssl base64 encoding
 * @param  string $string a string, here, we use a password string
 * @return string         an encoded password string
 */
function encryptString( $string ) {
  $salt  = 'ereNDrpSgW';
  $string  = $salt . $string . $salt;
  $cmd = 'out=$(echo "' . $string . '" | openssl base64 -e); echo "${out}"';
  return exec( $cmd );
}

/**
 * Decrypts a salted ssl base64 encoding string
 * @param  string $string encrypted password string
 * @return string         decrypted password string
 */
function decryptString( $string ) {
  $salt  = 'ereNDrpSgW';
  $cmd   = 'out=$(echo "' . $string . '" | openssl base64 -d); echo "${out}"';
  $decoded  = exec( $cmd );
  $decoded  = str_replace( $salt, '', $decoded );
  return $decoded;
}



function readPlistValue( $key, $plist ) {
  return exec( "/usr/libexec/PlistBuddy -c \"Print :$key\" '$plist' 2> /dev/null" );
}

function checkConnection() {
  ini_set( 'default_socket_timeout', 1);

  // First test
  exec( "ping -c 1 -t 1 www.google.com", $pingResponse, $pingError);
  if ( $pingError == 14 )
    return false;

  // Second Test
    $connection = @fsockopen("www.google.com", 80, $errno, $errstr, 1);

    if ( $connection ) {
        $status = true;
        fclose( $connection );
    } else {
        $status = false;
    }
    return $status;
}

function getFiles( $dir ) {
  return array_diff( scandir( $dir ), array( '..', '.', '.DS_Store' ) );
}

function createDir( $dir, $permissions = 0755, $recursive = false ) {
  if ( ! file_exists( $dir ) ) {
    if ( ! mkdir( $dir, $permissions, $recursive ) ) {
      return true;
    } else {
      return false;
    }
  }
  return true;
}

function sortArray( &$array ) {
  uasort( $array, 'sortAssociativeArray' );
}

function sortAssociativeArray( $a, $b ) {
  return $a > $b;
}

function getDefaultsValue( $domain, $key ) {
  exec( "defaults read $domain $key", $out );
  if ( empty( $out[0] ) ) {
    return false;
  } else {
    return $out[0];
  }
}

// Recursive function to delete all files in a directory
function deleteDir( $dir ) {
  $files = array_diff( scandir( $dir ), array( '..', '.' ) );
  foreach ( $files as $f ) :
    if ( is_dir( $f ) ) {
      deleteDir( $f );
      rmdir( $f );
    } else {
      unlink( $f );
    }
  endforeach;
}
