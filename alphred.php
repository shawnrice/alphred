<?php
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
 *
 * If you are putting a variable to the gettext, like so:
 * _($text);
 * you are better of making another function like this:
 * <?php
 * function __($text){
 * if(empty($text)) return "";
 * else gettext($text);
 * }
 * ?>
 *
 *
 * <?php
 * // Set language to German
 * putenv('LC_ALL=de_DE');
 * setlocale(LC_ALL, 'de_DE');
 *
 * // Specify location of translation tables
 * bindtextdomain("myPHPApp", "./locale");
 *
 * // Choose domain
 * textdomain("myPHPApp");
 *
 * // Translation is looking for in ./locale/de_DE/LC_MESSAGES/myPHPApp.mo now
 *
 * // Print a test message
 * echo gettext("Welcome to My PHP Application");
 *
 * // Or use the alias _() for gettext()
 * echo _("Have a nice day");
 * ?>
 *
 * gettext is not installed....
 *
 *
 * For internationalization:
 *
 * gettext() isn't compiled into OS X's PHP installation, so I've created my own hacky version.
 *
 * (1) Create a directory called "i18n" in your workflow folder.
 * (2) For each language you want to use, create a file called ln.php (lowercase) where
 * "ln" is the two-letter language code: i.e. en = English, de = German, etc...
 * (3) In that folder, create just one function called i18nln($string) where ln, again, is
 * the two-letter langauge code. So, English would be i18nen($string), and German would be
 * i18nde($string).
 * (4) In that function, create an associative array in which your original string is the key,
 * and the translation is the value. So, for i18nfr($string), there might be this:
 *
 * $t =  array('Hello', 'Bonjour',
 *            'Do you speak French?', 'Parlez-vous Français?',
 *            'I am a grapefruit', 'Je suis un pamplemousse',
 *       );
 *
 * Then, simply add in the line:
 *
 * return stripslashes($t[addslashes("$string")]);
 * (5) Make sure you escape the string if necessary.
**/

class Alphred {

  private $results;

  public function __construct( $log = FALSE, $locale = FALSE ) {

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

    // Okay... This causes errors, but we need to make sure that it is fixed.
    // if ($locale) {
    //   $l = exec( 'defaults read .GlobalPreferences AppleLanguages |'
    //   . ' tr -d [:space:] | cut -c2-3' );
    //   if ( file_exists($this->dir . '/i18n/' . $l . '.php')) {
    //     $this->l = $l;

    //     include($this->dir . '/i18n/' . $l . '.php');

    //     $function = "i18n$l";
    //     if ( function_exists( $function ) ) {
    //       $this->t = function( $function );
    //     } else {
    //       $this->l = FALSE;
    //     }
    //   }
    //   else
    //     $this->l = FALSE;
    //   unset( $l );
    // }
  } /* End __contruct() */

  public function user() {
    return $this->user;
  }

  public function bundle() {
    return $this->bundle;
  }

  public function data() {
    return $this->data;
  }

  public function cache() {
    return $this->cache;
  }

  public function dir() {
    return $this->dir;
  }




  public function t( $string ) {
    if ( ! $this->l ) {
      return $string;
    } else {

    }
  }

  public function log( $msg ) {
    if ( ! file_exists( $this->log ) )
      file_put_contents( $this->log , $msg . "\n" );
    else
      file_put_contents( $this->log , $msg . "\n" , FILE_APPEND | LOCK_EX );
  }

  // Functions for encrypting and decryting strings
  public function setSalt( $salt ) {
    $this->salt = $salt;
  }

  public function getSalt() {
    return $this->salt;
  }

  public function encryptString( $string, $salt = FALSE ) {
    if ( $salt == FALSE )
      $salt = $this->salt;
    $string  = $salt . $string . $salt;
    $cmd = 'out=$(echo "' . $string . '" | openssl base64 -e); echo "${out}"';
    return exec( $cmd );
  }

  public function decryptString( $string, $salt = FALSE ) {
    if ( $salt == FALSE )
      $salt = $this->salt;
    $cmd   = 'out=$(echo "' . $string . '" | openssl base64 -d); echo "${out}"';
    return str_replace( $salt, '', exec( $cmd ) );
  }

  public function writeProtected( $key, $value ) {
    $value = $this->encryptString( $value );
    $this->writeSetting( $key, $value );
  }

  public function readProtected( $key ) {
    return $this->decryptString( $this->readSetting( $key ) );
  }

  public function writeSetting( $key, $value ) {
    if ( ! ( isset( $key ) && is_array( $key ) ) )
      return FALSE;
    if ( ! ( isset( $value ) && is_array( $value ) ) )
      return FALSE;

    $settings = json_decode( file_get_contents( $this->settings ), TRUE );
    $settings[ $key ] = $value;
    file_put_contents( $this->settings, json_encode( $settings ) );
  }

  public function readSetting( $key ) {
    if ( ! ( isset( $key ) && is_array( $key ) ) )
      return FALSE;
    $settings = json_decode( file_get_contents( $this->settings ), TRUE );
    if ( isset( $settings[ $key ] ) )
      return $settings[ $key ];
    else
      return FALSE;
  }

  public function validate_settings( $array ) {
    if ( ! file_exists( $this->settings ) )
      return FALSE;

    return TRUE;
  }

  public function call( $url, $settings = array() ) {
    // Function to simplify a cURL request
  }

  public function auth_call( $url, $user, $pass, $settings = array() ) {
    // Function to simplify an auth cURL request
  }

  public function post( $url, $settings = array()) {
    // Function to simplify a post request
  }

  public function auth_post($url, $user, $pass, $settings = array() ) {
    // Function to simplify an auth post request
  }

  public function dl( $url ) {
    // Function to download a URL easily.
    return file_get_contents($url);
  }

  public function cache_result($result, $kind) {

  }

  public function get_cache_result($result, $kind, $age) {

  }

  public function clear_caches($kind = FALSE) {

  }

  public function resetData() {
    // function wipes all data and destroys directories
    deleteDir( $this->data  );
  }

  public function resetCache() {
    // function wipes all data and destroys directories
    deleteDir( $this->cache );
  }

  public function results() {

  }

  /**
   * Add a result for a script filter.
   *
   * @param  array | object  $result  An array or an object that contains a result.
   */
  public function addResult( $result ) {
    if ( is_object( $result ) && is_a( $result, 'AlphredResponse' ) ) {
      array_push( $this->results, (array) $result );
    } else if ( is_array( $result ) ) {
      array_push( $this->results, $result );
    } else {
      return FALSE;
    }
  }

  public function alfred_xml() {
    print_r( $this->results );
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
  public function cdata( $key, $value, $xml, $mod = FALSE ) {
    $xml->startElement( $key );
    if ( $mod )
      $xml->writeAttribute( $mod[0], $mod[1] );
    $xml->writeCData( $value );
    $xml->endElement();
  }

  /**
   * [writeItem description]
   *
   * @param   [type]   $xml            [description]
   * @param   string   $uid            [description]
   * @param   string   $arg            [description]
   * @param   string   $valid          [description]
   * @param   string   $autocomplete   [description]
   * @param   boolean  $type           [description]
   * @param   string   $title          [description]
   * @param   string   $subtitle       [description]
   * @param   string   $subtitleShift  [description]
   * @param   string   $subtitleFn     [description]
   * @param   string   $subtitleCtrl   [description]
   * @param   string   $subtitleAlt    [description]
   * @param   string   $subtitleCmd    [description]
   * @param   string   $textCopy       [description]
   * @param   string   $textLargeType  [description]
   *
   * @return  [type]                   [description]
   */
  public function writeItem( $xml, $uid = '', $arg = '', $valid = 'no',
    $autocomplete = '', $type = FALSE, $title = '', $subtitle = '',
    $subtitleShift = '', $subtitleFn = '', $subtitleCtrl = '',
    $subtitleAlt = '', $subtitleCmd = '', $textCopy = '',
    $textLargeType = '' ) {

    // Write an Item
    $xml->startElement( 'item' );
    // Construct Item Line
    if ( isset( $uid ) && ( ! empty( $uid ) ) )
      $xml->writeAttribute( 'uid', $uid );
    else
      $xml->writeAttribute( 'uid', '' );
    if ( isset( $arg ) && ( ! empty( $arg ) ) )
      $xml->writeAttribute( 'arg', $arg );
    else
      $xml->writeAttribute( 'arg', '' );
    if ( isset( $valid ) && ( ! empty( $valid ) ) )
      $xml->writeAttribute( 'valid', $valid );
    else
      $xml->writeAttribute( 'valid', '' );
    if ( isset( $autocomplete ) && ( ! empty( $autocomplete ) ) )
      $xml->writeAttribute( 'autocomplete', $autocomplete );
    else
      $xml->writeAttribute( 'autocomplete', '' );
    if ( isset( $type ) && ( ! empty( $type ) ) )
      $xml->writeAttribute( 'type', $type );
    // End Item Line

    // Write Properties
    if ( isset( $title ) && ( ! empty( $title ) ) )
      $this->cdata( 'title', $title, $xml );
    if ( isset( $subtitle ) && ( ! empty( $subtitle ) ) )
      $this->cdata( 'subtitle', $subtitle, $xml );
    if ( isset( $subtitleShift ) && ( ! empty( $subtitleShift ) ) )
      $this->cdata( 'subtitle', $subtitleShift, $xml, array( 'mod', 'shift' ) );
    if ( isset( $subtitleFn ) && ( ! empty( $subtitleFn ) ) )
      $this->cdata( 'subtitle', $subtitleFn, $xml, array( 'mod', 'fn' ) );
    if ( isset( $subtitleCtrl ) && ( ! empty( $subtitleCtrl ) ) )
      $this->cdata( 'subtitle', $subtitleCtrl, $xml, array( 'mod', 'ctrl' ) );
    if ( isset( $subtitleAlt ) && ( ! empty( $subtitleAlt ) ) )
      $this->cdata( 'subtitle', $subtitleAlt, $xml, array( 'mod', 'alt' ) );
    if ( isset( $subtitleCmd ) && ( ! empty( $subtitleCmd ) ) )
      $this->cdata( 'subtitle', $subtitleCmd, $xml, array( 'mod', 'cmd' ) );
    if ( isset( $textCopy ) && ( ! empty( $textCopy ) ) )
      $this->cdata( 'text', $textCopy, $xml, array( 'type', 'copy' ) );
    if ( isset( $textLargeType ) && ( ! empty( $textLargeType ) ) )
      $this->cdata( 'text', $textLargeType, $xml, array( 'type', 'largetype' ) );
    // End Properties

    $xml->endElement(); // End Item

  }


} /* End Class Alphred */

class AlphredResponse
{

  private $uid;
  private $arg;
  private $valid;
  private $autocomplete;
  private $title;
  private $subtitle;
  private $subtitleShift;
  private $subtitleFn;
  private $subtitleCtrl;
  private $subtitleAlt;
  private $subtitleCmd;
  private $icon;
  private $type;
  private $textCopy;
  private $textLargeType;

  public function __contruct() {
    $this->uid           = '';
    $this->arg           = '';
    $this->type          = '';
    $this->valid         = 'yes';
    $this->autocomplete  = '';
    $this->title         = '';
    $this->subtitle      = '';
    $this->subtitleShift = '';
    $this->subtitleFn    = '';
    $this->subtitleCtrl  = '';
    $this->subtitleAlt   = '';
    $this->subtitleCmd   = '';
    $this->icon          = '';
    $this->textCopy      = '';
    $this->textLargeType = '';
  }

  public function setUid           ( $value ) {
    $this->uid = $value;
  }
  public function setArg           ( $value ) {
    $this->uid = $value;
  }
  public function setType          ( $value ) {
    $this->type = $value;
  }
  public function setValid         ( $value ) {
    $this->valid = $value;
  }
  public function setAutocomplete  ( $value ) {
    $this->autocomplete = $value;
  }
  public function setTitle         ( $value ) {
    $this->title = $value;
  }
  public function setSubtitle      ( $value ) {
    $this->subtitle = $value;
  }
  public function setSubtitleShift ( $value ) {
    $this->SubtitleShift = $value;
  }
  public function setSubtitleFn    ( $value ) {
    $this->SubtitleFn = $value;
  }
  public function setSubtitleCtrl  ( $value ) {
    $this->SubtitleCtrl = $value;
  }
  public function setSubtitleAlt   ( $value ) {
    $this->SubtitleAlt = $value;
  }
  public function setSubtitleCmd   ( $value ) {
    $this->SubtitleCmd = $value;
  }
  public function setIcon          ( $value ) {
    $this->icon = $value;
  }
  public function setTextCopy      ( $value ) {
    $this->textCopy = $value;
  }
  public function setTextLargeType ( $value ) {
    $this->textLargeType = $value;
  }

  public function sendResult() {
    $out  = "<item uid='$this->uid' arg='$this->arg' valid='$this->valid' ";
    $out .= "autocomplete='$this->autocomplete'";
    if ( ! empty( $this->type ) )
      $out .= " type='$this->type'>";
    else
      $out .= ">";
    $out .= "<title>$this->title</title>";
    $out .= "<icon type='fileicon'>$this->icon</icon>";
    $out .= "<subtitle>$this->subtitle</subtitle>";
    if ( ! empty( $this->subtitleShift ) )
      $out .= "<subtitle mod='shift'>$this->subtitleShift</subtitle>";
    if ( ! empty( $this->subtitleFn ) )
      $out .= "<subtitle mod='fn'>$this->subtitleFn</subtitle>";
    if ( ! empty( $this->subtitleCtrl ) )
      $out .= "<subtitle mod='ctrl'>$this->subtitleCtrl</subtitle>";
    if ( ! empty( $this->subtitleAlt ) )
      $out .= "<subtitle mod='alt'>$this->subtitleAlt</subtitle>";
    if ( ! empty( $this->subtitleCmd ) )
      $out .= "<subtitle mod='cmd'>$this->subtitleCmd</subtitle>";
    if ( ! empty( $this->textCopy ) )
      $out .= "<text type='copy'>$this->textCopy</text>";
    if ( ! empty( $this->textLargeType ) )
      $out .= "<text type='largetype'>$this->textLargeType</text>";
    $out .= "</item>";

    return $out;
  }



} /* End Class AlphredResponse */

/*******************************************************************************
 *******************************************************************************
 *******************************************************************************
 *******************************************************************************
 *******************************************************************************
 * Helper Functions
 *******************************************************************************
 *******************************************************************************
 *******************************************************************************
 *******************************************************************************
 ******************************************************************************/

function avoidDateErrors() {
  // Set date/time to avoid warnings/errors.
  if ( ! ini_get('date.timezone') ) {
    $tz = exec( 'tz=`ls -l /etc/localtime` && echo ${tz#*/zoneinfo/}' );
    ini_set( 'date.timezone', $tz );
  }
}

function getOSXVersion( $verbose = FALSE ) {
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
  return FALSE;
}

/**
 * Checks to see if the settings.json file exists and is complete
 * @param  string $settings_file path to settings file
 * @return mixed                 either an error code ('user' or 'password') or TRUE on a complete settings file
 */
function checkSettings( $settings_file ) {

  if ( file_exists( $settings_file ) ) {
    // The settings file exists; now check for completeness

    $settings = json_decode( file_get_contents( $settings_file ) , TRUE );

    if ( ! isset( $settings['username'] ) || empty( $settings['username'] ) ) {
      // The user setting isn't there, so throw the user error
      return 'user';
    }
    if ( ! isset( $settings['password'] ) || empty( $settings['password'] ) ) {
      // The password setting isn't there, so throw the password error
      return 'password';
    }
    // There are no problems with the settings, so return 'TRUE'
    return TRUE;
  } else {
    // The settings file doesn't exist, so just throw the 'user' error
    return 'user';
  }
}

/**
 * Calls the 'gists username' keyword in Alfred to bring up the prompt to set the username
 */
function call_set_username() {
  $cmd = "/usr/bin/osascript -e 'tell application \"Alfred 2\" to search \"gists username \"'";
  passthru( $cmd );
}

/**
 * Calls the 'gists password' keyword in Alfred to bring up the prompt to set the password
 * @return [type] [description]
 */
function call_set_password() {
  $cmd = "/usr/bin/osascript -e 'tell application \"Alfred 2\" to search \"gists password \"'";
  passthru( $cmd );
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

function callExternalTrigger( $bundle, $trigger, $argument = FALSE ) {
  $script = "tell application \"Alfred 2\" to run trigger \"$trigger\" in workflow \"$bundle\"";
  if ( $argument !== FALSE ) {
    $script .= "with argument \"$argument\"";
  }
  exec( "osascript -e '$script'" );
}

function readPlistValue( $key, $plist ) {
  return exec( "/usr/libexec/PlistBuddy -c \"Print :$key\" '$plist' 2> /dev/null" );
}

function checkConnection() {
  ini_set( 'default_socket_timeout', 1);

  // First test
  exec( "ping -c 1 -t 1 www.google.com", $pingResponse, $pingError);
  if ( $pingError == 14 )
    return FALSE;

  // Second Test
    $connection = @fsockopen("www.google.com", 80, $errno, $errstr, 1);

    if ( $connection ) {
        $status = TRUE;
        fclose( $connection );
    } else {
        $status = FALSE;
    }
    return $status;
}

function getFiles( $dir ) {
  return array_diff( scandir( $dir ), array( '..', '.', '.DS_Store' ) );
}

function createDir( $dir, $permissions = 0755, $recursive = FALSE ) {
  if ( ! file_exists( $dir ) ) {
    if ( ! mkdir( $dir, $permissions, $recursive ) ) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  return TRUE;
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
    return FALSE;
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

function convertSecondsToHumanTime( $time ) {

  if ( $time < 0 )
    $time = $time * -1;

  if ( $time < 60 ) {
    if ( $time > 1 )
      return "$time seconds";
    else
      return "$time second";
  }

  // Error checking needs to go here.
  $seconds = $time % 60;
  $time    = ( $time - $seconds ) / 60;
  $minutes = $time % 60;
  $time    = ( $time - $minutes ) / 60;
  $hours   = $time % 24;
  $time    = ( $time - $hours ) / 24;
  $days    = $time % 7;
  $time    = ( $time - $days ) / 7;
  $weeks   = $time; // Expand from here.

}

function howLongAgo( $time, $ago = TRUE ) {

  avoidDateErrors();

  $now = mktime();

  $past = TRUE;
  if ( ! ( ( $now - $time ) > 0 ) ) {
    $past = FALSE;
  }



}


/*******************************************************************************
 * Just including the Alfred Bundler PHP Wrapper for easy use.
 ******************************************************************************/

if ( ! function_exists( '__load' ) ) {

  /***
    Main PHP interface for the Alfred Dependency Bundler. This file should be
    the only one from the bundler that is distributed with your workflow.

    See documentation on how to use: http://shawnrice.github.io/alfred-bundler/

    License: GPLv3
  ***/

  /**
   *  This is the only function the workflow author needs to invoke.
   *  If the asset to be loaded is a PHP library, then you just need to call the function,
   *  and the files will be required automatically.
   *
   *  If you are loading a "utility" application, then the function will return the full
   *  path to the function so that you can invoke it.
   *
   *  If you are passing your own json, then include it as a file path.
   *
   **/
  function __load( $name , $version = 'default' , $type = 'php' , $json = '' ) {
    // Define the global bundler version.
    $bundler_version       = "aries";

    // Let's just make sure that the utility exists before we try to use it.
    $__data = exec('echo $HOME') . "/Library/Application Support/Alfred 2/Workflow Data/alfred.bundler-$bundler_version";
    if ( ! file_exists( "$__data" ) ) {
      __installBundler();
    }

    // This file will be there because it either was or we just installed it.
    require_once( "$__data/bundler.php" );

    // Check for bundler minor update
    $cmd = "sh '$__data/meta/update.sh' > /dev/null 2>&1";
    exec( $cmd );

    if ( file_exists( 'info.plist' ) ) {
      // Grab the bundle ID from the plist file.
      $bundle = exec( "/usr/libexec/PlistBuddy -c 'print :bundleid' 'info.plist'" );
    } else if ( file_exists( '../info.plist' ) ) {
      $bundle = exec( "/usr/libexec/PlistBuddy -c 'print :bundleid' '../info.plist'" );
    } else {
      $bundle = '';
    }

    if ( $type == 'php' ) {
      $assets = __loadAsset( $name , $version , $bundle , strtolower($type) , $json );
      foreach ($assets as $asset ) {
        require_once( $asset );
      }
      return TRUE;
    } else if ( $type == 'utility' ) {
      $asset = __loadAsset( $name , $version , $bundle , strtolower($type) , $json );
      return str_replace(' ' , '\ ' , $asset[0]);
    } else {
      return __loadAsset( $name , $version , $bundle , strtolower($type) , $json );
    }

    // We shouldn't get here.
    return FALSE;

  } // End __load()
}

if ( ! function_exists( '__installBundler' ) ) {
  /**
   * Installs the Alfred Bundler utility.
   **/
  function __installBundler() {
    // Install the Alfred Bundler

    global $bundler_version, $__data;

    $installer = "https://raw.githubusercontent.com/shawnrice/alfred-bundler/$bundler_version/meta/installer.sh";
    $__cache   = $_SERVER[ 'HOME' ] .
       "/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/alfred.bundler-$bundler_version";

    // Make the directories
    if ( ! file_exists( $__cache ) ) {
      mkdir( $__cache );
    }
    if ( ! file_exists( "$__cache/installer" ) ) {
      mkdir( "$__cache/installer" );
    }
    // Download the installer
    // I'm throwing in the second bash command to delay the execution of the next
    // exec() command. I'm not sure if that's necessary.
    exec( "curl -sL '$installer' > '$__cache/installer/installer.sh'" );
    // Run the installer
    exec( "sh '$__cache/installer/installer.sh'" );

  } // End __installBundler()
}

/*******************************************************************************
 * End Alfred Bundler
 ******************************************************************************/

// This could be used later to create icns files
// mkdir MyIcon.iconset
// sips -z 16 16     Icon1024.png --out MyIcon.iconset/icon_16x16.png
// sips -z 32 32     Icon1024.png --out MyIcon.iconset/icon_16x16@2x.png
// sips -z 32 32     Icon1024.png --out MyIcon.iconset/icon_32x32.png
// sips -z 64 64     Icon1024.png --out MyIcon.iconset/icon_32x32@2x.png
// sips -z 128 128   Icon1024.png --out MyIcon.iconset/icon_128x128.png
// sips -z 256 256   Icon1024.png --out MyIcon.iconset/icon_128x128@2x.png
// sips -z 256 256   Icon1024.png --out MyIcon.iconset/icon_256x256.png
// sips -z 512 512   Icon1024.png --out MyIcon.iconset/icon_256x256@2x.png
// sips -z 512 512   Icon1024.png --out MyIcon.iconset/icon_512x512.png
// cp Icon1024.png MyIcon.iconset/icon_512x512@2x.png
// iconutil -c icns MyIcon.iconset
// rm -R MyIcon.iconset