<?php

namespace Alphred;

// Much of (almost all of for now) this was taken from my code in the Alfred Bundler.
// If you're using this library and the bundler, then just use one or the other
// version of this class

/**
 *
 * Simple logging functionality that writes to files or STDERR
 *
 * Usage: just create a single object and reuse it solely. Initialize the
 * object with a full path to the log file (no log extension)
 *
 * @package   Alphred
 * @since     Class available since Taurus 1
 *
 */
class Log {

  /**
   * Log file
   *
   * Full path to log file with no extension; set by user at instantiation
   *
   * @var  string
   * @since 1.0.0
   */
  public $log;

  /**
   * An array of log levels (int => string )
   *
   * 0 => 'DEBUG'
   * 1 => 'INFO'
   * 2 => 'WARNING'
   * 3 => 'ERROR'
   * 4 => 'CRITICAL'
   *
   * @var  array
   * @since 1.0.0
   */
  protected $logLevels;

  /**
   * Stacktrace information; reset with each message
   *
   * @var  array
   * @since 1.0.0
   */
  private $trace;

  /**
   * File from stacktrace; reset with each message
   *
   * @var  string
   * @since 1.0.0
   */
  private $file;

  /**
   * Line from stacktrace; reset with each message
   *
   * @var  int
   * @since 1.0.0
   */
  private $line;

  /**
   * Log level; reset with each message
   *
   * @var  mixed
   * @since 1.0.0
   */
  private $level;

  /**
   * Default destination to send a log message to
   *
   * @var  string   options: file, console, both
   */
  private $defaultDestination;

  /**
   * Sets variables and ini settings to ensure there are no errors
   *
   * @param  string  $log                   filename to use as a log
   * @param  string  $destination = 'file'  default destination for messages
   * @since 1.0.0
   */
  public function __construct( $log, $destination = 'file' ) {

    if ( !  Globals::get('alfred_bundleid') ) {
      // we should throw an exception here
      return false;
    }

    $this->log = Globals::get('alfred_workflow_data') . '/' . Globals::get('alfred_bundleid') . '.log';
    $this->initializeLog();

    if ( ! in_array( $destination, [ 'file', 'console', 'both' ] ) ) {
      $this->defaultDestination = 'file';
    } else {
      $this->defaultDestination = $destination;
    }

    // These are the appropriate log levels
    $this->logLevels = array( 0 => 'DEBUG',
                              1 => 'INFO',
                              2 => 'WARNING',
                              3 => 'ERROR',
                              4 => 'CRITICAL',
    );

    // Set date/time to avoid warnings/errors.
    Date::avoid_date_errors();

    // This is needed because, Macs don't read EOLs well.
    if ( ! ini_get( 'auto_detect_line_endings' ) ) {
      ini_set( 'auto_detect_line_endings', true );
    }

  }

  /**
   * Logs a message to either a file or STDERR
   *
   * After initializing the log object, this should be the only
   * method with which you interact.
   *
   *
   * <code>
   * $log = new Log( '/full/path/to/mylog' );
   * $log->log( 'Write this to a file', 'INFO' );
   * $log->log( 'Warning message to console', 2, 'console' );
   * $log->log( 'This message will go to both the console and the log', 3, 'both');
   * </code>
   *
   *
   * @param   string  $message      message to log
   * @param   mixed   $level        either int or string of log level
   * @param   string  $destination  where the message should appear:
   *                                valid options: 'file', 'console', 'both'
   * @since 1.0.0
   */
  public function log( $message, $level = 'INFO', $destination = '', $trace = 0 ) {

    // Set the destination to the default if not implied
    if ( empty( $destination ) )
      $destination = $this->defaultDestination;

    // Get the relevant information from the backtrace
    $this->trace = debug_backtrace();
    $this->trace = $this->trace[ $trace ];
    $this->file  = basename( $this->trace[ 'file' ] );
    $this->line  = $this->trace[ 'line' ];

    // check / normalize the arguments
    $this->level = $this->normalizeLogLevel( $level );
    $destination = strtolower( $destination );

    if ( $destination == 'file' || $destination == 'both' )
      $this->logFile( $message );
    if ( $destination == 'console' || $destination == 'both' )
      $this->logConsole( $message );

  }

  /**
   * Creates log directory and file if necessary
   * @since 1.0.0
   */
  private function initializeLog() {
    if ( ! file_exists( $this->log ) ) {
      if ( ! is_dir( realpath( dirname( $this->log ) ) ) )
        mkdir( dirname( $this->log ), 0775, true );
      file_put_contents( $this->log, '' );
    }
  }


  /**
   * Checks to see if the log needs to be rotated
   * @since 1.0.0
   */
  private function checkLog() {
    if ( filesize( $this->log ) > 1048576 )
      $this->rotateLog();
  }


  /**
   * Rotates the log
   * @since 1.0.0
   */
  private function rotateLog() {
      $old = substr( $this->log, -4 );
      if ( file_exists( $old . '1.log' ) )
        unlink( $old . '1.log' );

      rename( $this->log, $old . '1.log' );
      file_put_contents( $this->log, '' );
  }

  /**
   * Ensures that the log level is valid
   *
   * @param   mixed  $level   either an int or a string denoting log level
   *
   * @return  string          log level as string
   * @since 1.0.0
   */
  public function normalizeLogLevel( $level ) {

    $date = date( 'H:i:s', time() );

    // If the level is okay, then just return it
    if ( isset( $this->logLevels[ $level ] )
         || in_array( $level, $this->logLevels ) ) {
      return $level;
    }

    // the level is invalid; log a message to the console
    file_put_contents( 'php://stderr', "[{$date}] " .
      "[{$this->file},{$this->line}] [WARNING] Log level '{$level}' " .
      "is not valid. Falling back to 'INFO' (1)" . PHP_EOL );

    // set level to info
    return 'INFO';
  }

  /**
   * Writes a message to the console (STDERR)
   *
   * @param   string  $message  message to log
   * @since 1.0.0
   */
  public function logConsole( $message ) {
    $date = date( 'H:i:s', time() );
    file_put_contents( 'php://stderr', "[{$date}] " .
      "[{$this->file}:{$this->line}] [{$this->level}] {$message}" . PHP_EOL );
  }

  /**
   * Writes message to log file
   *
   * @param   string  $message  message to log
   * @since 1.0.0
   */
  public function logFile( $message ) {
    $date = date( "Y-m-d H:i:s" );
    $message = "[{$date}] [{$this->file}:{$this->line}] " .
               "[{$this->level}] ". $message . PHP_EOL;
    file_put_contents( $this->log, $message, FILE_APPEND | LOCK_EX );
  }


}