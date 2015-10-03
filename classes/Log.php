<?php
/**
 * Contains Log class for Alphred, providing basic logging functionality
 *
 * PHP version 5
 *
 * @package    Alphred
 * @copyright  Shawn Patrick Rice 2014
 * @license    http://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @author     Shawn Patrick Rice <rice@shawnrice.org>
 * @link       http://www.github.com/shawnrice/alphred
 * @link       http://shawnrice.github.io/alphred
 * @since      File available since Release 1.0.0
 *
 */

namespace Alphred;

/**
 * Simple static logging functionality that writes to files or STDERR
 *
 * @package   Alphred
 * @since     Class available since 1.0.0
 *
 */
class Log {

	/**
	 * An array that contans the valid log levels
	 * @since 1.0.0
	 * @var array
	 */
	static $log_levels = [
								0 => 'DEBUG',
							  1 => 'INFO',
							  2 => 'WARNING',
							  3 => 'ERROR',
							  4 => 'CRITICAL',
		];

		/**
		 * Log a message to a file
		 *
		 * @since 1.0.0
		 *
		 * @param  string  					$message  the message to log
		 * @param  integer|string 	$level    the log level
		 * @param  string  					$filename the filename of the log without an extension
		 * @param  boolean|integer 	$trace    how far back to trace
		 */
		public static function file( $message, $level = 1, $filename = 'workflow', $trace = false ) {

			// Check if the log level is loggable based on the threshold.
			// The threshold is defined as the constant ALPHED_LOG_LEVEL, and defaults to level 2 (WARNING).
			// Change this either in the workflow.ini file or by defining the constant ALPHRED_LOG_LEVEL
			// before you include Alphred.phar.
			if ( ! self::is_loggable( $level ) ) {
				return false;
			}

			// Get the full path to the log file, and create the data directory if it doesn't exist
			$log_file = self::get_log_filename( $filename );

			// Check and rotate the log if necessary
			self::check_log( $log_file );

			// Get the trace
			$trace = self::trace( $trace );
			// Get the formatted date
			$date = self::date_file();
			// Normalize the log level
			$level = self::normalize_log_level( $level );

			// Construct the log entry
			$message = "[{$date}][{$trace}][{$level}] {$message}\n";

			// Write to the log file
			file_put_contents( $log_file, $message, FILE_APPEND | LOCK_EX );
		}

		/**
		 * Log a message to the console (STDERR)
		 *
		 * @since 1.0.0
		 *
		 * @param  string  					$message the message to log
		 * @param  string|integer 	$level   the log level
		 * @param  boolean|integer 	$trace   how far back to trace
		 */
		public static function console( $message, $level = 1, $trace = false ) {

			// Check if the log level is loggable based on the threshold.
			// The threshold is defined as the constant ALPHED_LOG_LEVEL, and defaults to level 2 (WARNING).
			// Change this either in the workflow.ini file or by defining the constant ALPHRED_LOG_LEVEL
			// before you include Alphred.phar.
			if ( ! self::is_loggable( $level ) ) {
				return false;
			}

			// Get the trace
			$trace = self::trace( $trace );
			// Get the formatted date
			$date  = self::date_console();
			// Normalize the log level
			$level = self::normalize_log_level( $level );
			file_put_contents( 'php://stderr', "[{$date}][{$trace}][{$level}] {$message}\n" );
		}

		/**
		 * Log a message to both a file and the console
		 *
		 * @since 1.0.0
		 *
		 * @param  string  					$message  the message to log
		 * @param  integer|string 	$level    the log level
		 * @param  string  					$filename the filename of the log without an extension
		 * @param  boolean|integer 	$trace    how far back to trace
		 */
		public static function log( $message, $level = 1, $filename = 'workflow', $trace = false ) {
			// Check if the log level is loggable based on the threshold.
			// The threshold is defined as the constant ALPHED_LOG_LEVEL, and defaults to level 2 (WARNING).
			// Change this either in the workflow.ini file or by defining the constant ALPHRED_LOG_LEVEL
			// before you include Alphred.phar.
			if ( ! self::is_loggable( $level ) ) {
				return false;
			}
			// Log message to console
			self::console( $message, $level, $trace );
			// Log message to file
			self::file( $message, $level, $filename, $trace );
		}

		/**
		 * Gets the full filepath to the log file
		 *
		 * @since 1.0.0
		 *
		 * @param  string $filename a filename for a log file
		 * @return string           the full filepath for a log file
		 */
		private static function get_log_filename( $filename ) {
			// Attempt to get the workflow's data directory. If it isn't set (i.e. running outside of a workflow env),
			// then just use the current directory.
			if ( ! $dir = \Alphred\Globals::get( 'alfred_workflow_data' ) ) {
				$dir = '.';
			} else {
				self::create_log_directory();
			}
			return "{$dir}/{$filename}.log";
		}

		/**
		 * Creates the workflow's data directory if it does not exist.
		 *
		 * @since 1.0.0
		 */
		private static function create_log_directory() {
			$directory = \Alphred\Globals::get( 'alfred_workflow_data' );
			if ( $directory ) {
				if ( ! file_exists( $directory ) ) {
					mkdir( $directory, 0775, true );
				}
			}
		}

		/**
		* Checks to see if the log needs to be rotated
		*
		* @since 1.0.0
		*/
		private static function check_log( $filename ) {
			// ALPHRED_LOG_SIZE is define in bytes. It defaults to 1048576 and is set in
			// `Alphred.php`. If you want to change the max size, then either define the
			// max size in the INI file or define the constant ALPHRED_LOG_SIZE before
			// you include `Alphred.phar`.
			if ( filesize( $filename ) > ALPHRED_LOG_SIZE ) {
				// The log is too big, so rotate it.
				self::rotate_log( $filename );
			}
		}


		/**
		* Rotates the log
		*
		* @since 1.0.0
		*/
		private static function rotate_log( $log_file ) {

			// Set the backup log filename
			$old = substr( $log_file, 0, -4 ) . '.1.log';

			// Check if an old filelog exists
			if ( file_exists( $old ) ) {
				// It exists, so delete it
				unlink( $old );
			}

			// Rename the current log to the old log
			rename( $log_file, $old );

			// Create an empty log file
			file_put_contents( $log_file, '' );
		}

		/**
		 * Normalizes the log level, returning 'INFO' or 1 if invalid
		 *
		 * @since 1.0.0
		 *
		 * @param  integer|string $level the level represented as either a string or an integer
		 * @return string        	the name of the log level
		 */
		private static function normalize_log_level( $level ) {
			// Check if the log level is numeric
			if ( is_numeric( $level ) ) {
				// It is numeric, so check if it is valid
				if ( isset( self::$log_levels[ $level ] ) ) {
					// It is valid, so return the name of the level
					return self::$log_levels[ $level ];
				} else {
					// The level is numeric but not valid, so log a warning to the console, and
					// return log level 1.
					self::console( "Log level {$level} is not valid. Setting to log level 1." );
					return self::$log_levels[1]; // This is an assumption note an error here
				}
			}
			// It is not numeric, so check if it is in the log levels array
			if ( in_array( $level, self::$log_levels ) ) {
				// It is in the array, so return the value passed
				return $level;
			}
			// The log level is a string but is not valid, so log an error to the console
			// and return log level 1.
			self::console( "Log level {$level} is not valid. Setting to log level 1." );
			return self::$log_levels[1]; // This is an assumption, note an error here
		}

		/**
		 * Fetches information from a stack trace
		 *
		 * @since 1.0.0
		 *
		 * @param  boolean|integer $depth How far to do the trace, default is the last
		 * @return string          the file and line number of the trace
		 */
		private static function trace( $depth = false ) {

			// Get the relevant information from the backtrace
			$trace = debug_backtrace();
			// Check if the dpeth is defined, and if the depth is within the trace
			if ( $depth && isset( $trace[ $depth ] ) ) {
				// The depth is defined, see if it is negative
				if ( $depth < 0 ) {
					// It's negative, so translate that to a positive number that we can use.
					$depth = count( $trace ) + $depth - 1;
				}
				// Get the explicit trace
				$trace = $trace[ $depth ];
			} else {
				// Just get the last trace.
				$trace = end( $trace );
			}

			// Set the filename
			$file  = basename( $trace['file'] );
			// Set the line number
			$line  = $trace['line'];

			return "{$file}:{$line}";
		}


		/**
		 * Checks if a log level is within a display threshold
		 *
		 * @since 1.0.0
		 *
		 * @param  mixed  $level  Either a string or a
		 * @return boolean        Whether or not a value is above the logging threshold
		 */
		private static function is_loggable( $level ) {
			// First, check is the level is numeric
			if ( ! is_numeric( $level ) ) {
				// It is not numeric, so let's translate it to a number
				$level = array_search( $level, self::$log_levels ); // This needs error checking
			}
			// Return a boolean of whether or not the level is less than or equal to the logging threshold
			return $level >= self::get_threshold();
		}

		/**
		 * Gets the threshold for log messages
		 *
		 * @todo Implement exception for bad log level
		 * @since 1.0.0
		 *
		 * @return integer  an integer matching a log level
		 */
		private static function get_threshold() {
			// Check is the threshold is defined as a number
			if ( is_numeric( ALPHRED_LOG_LEVEL ) ) {
				// It is, so just return that number
				return ALPHRED_LOG_LEVEL;
			} else if ( in_array( ALPHRED_LOG_LEVEL, self::$log_levels ) ) {
				// The threshold is not defined as a number, but it is a string defined
				// in the log_levels, so return the number
				return array_search( ALPHRED_LOG_LEVEL, self::$log_levels );
			} else {
				// The threshold is not a number, and it is not a string that is in the
				// log_levels, so throw an exception and return 0.
				throw new Exception( "Alphred Log Level is not a valid level" );
				return 0;
			}
		}

		/**
		 * Gets the time formatted for a console display log
		 *
		 * @since 1.0.0
		 *
		 * @return string the time as HH:MM:SS
		 */
		private static function date_console() {
			return date( 'H:i:s', time() );
		}

		/**
		 * Gets a datestamp formatted for a file log
		 *
		 * @since 1.0.0
		 *
		 * @return string Formatted as YYYY-MM-DD HH:MM:SS
		 */
		private static function date_file() {
 			return date( 'Y-m-d H:i:s' );
		}
}