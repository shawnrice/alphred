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

  public function __construct( $log = false, $locale = false ) {

    // Include the classes
    foreach ( scandir( __DIR__ . '/classes' ) as $file ) :
      if ( pathinfo( __DIR__ . "/{$file}" PATHINFO_EXTENSION ) == 'php' )
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
    //       $this->l = false;
    //     }
    //   }
    //   else
    //     $this->l = false;
    //   unset( $l );
    // }
  } /* End __contruct() */

  public function user() {
    if ( ! isset($this->user) ) { $this->user = $_SERVER['USER']; }
    return $this->user;
  }

  public function bundle() {
$_SERVER['alfred_workflow_bundleid'];
    return $this->bundle;
  }

  public function data() {
    $_SERVER['alfred_workflow_data']
    return $this->data;
  }

  public function cache() {
    $_SERVER['alfred_workflow_cache']
    return $this->cache;
  }

public function uid() {
  $_SERVER['alfred_workflow_uid'];
}

public function workflow_name() {
  $_SERVER['alfred_workflow_name'];
}

public function theme_subtext() {
  $_SERVER['alfred_theme_subtext'];
}

public function alfred_version() {
  $_SERVER['alfred_version'];
}

public function alfred_build() {
  $_SERVER['alfred_version_build'];
}



  public function dir() {
    if ( ! isset($this->dir) ) { $this->dir = $_SERVER['PWD']; }
    return $this->dir;
  }

  public function theme_background() {
    return $_SERVER['alfred_theme_background'];
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



  public function writeSetting( $key, $value ) {
    if ( ! ( isset( $key ) && is_array( $key ) ) )
      return false;
    if ( ! ( isset( $value ) && is_array( $value ) ) )
      return false;

    $settings = json_decode( file_get_contents( $this->settings ), true );
    $settings[ $key ] = $value;
    file_put_contents( $this->settings, json_encode( $settings ) );
  }

  public function readSetting( $key ) {
    if ( ! ( isset( $key ) && is_array( $key ) ) )
      return false;
    $settings = json_decode( file_get_contents( $this->settings ), true );
    if ( isset( $settings[ $key ] ) )
      return $settings[ $key ];
    else
      return false;
  }

  public function validate_settings( $array ) {
    if ( ! file_exists( $this->settings ) )
      return false;

    return true;
  }



  public function cache_result($result, $kind) {

  }

  public function get_cache_result($result, $kind, $age) {

  }

  public function clear_caches($kind = false) {

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
      return false;
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
  public function cdata( $key, $value, $xml, $mod = false ) {
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
    $autocomplete = '', $type = false, $title = '', $subtitle = '',
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





/**
 * Checks to see if the settings.json file exists and is complete
 * @param  string $settings_file path to settings file
 * @return mixed                 either an error code ('user' or 'password') or true on a complete settings file
 */
function checkSettings( $settings_file ) {

  if ( file_exists( $settings_file ) ) {
    // The settings file exists; now check for completeness

    $settings = json_decode( file_get_contents( $settings_file ) , true );

    if ( ! isset( $settings['username'] ) || empty( $settings['username'] ) ) {
      // The user setting isn't there, so throw the user error
      return 'user';
    }
    if ( ! isset( $settings['password'] ) || empty( $settings['password'] ) ) {
      // The password setting isn't there, so throw the password error
      return 'password';
    }
    // There are no problems with the settings, so return 'true'
    return true;
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



/*******************************************************************************
 * End Alfred Bundler
 ******************************************************************************/

