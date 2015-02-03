<?php

namespace Alphred;

class Exception extends \Exception {

	/**
	 * Writes a message to STDERR depending on log level
	 *
	 * @todo Find a better way to control the internal logging
	 *
	 * @param string  $message message thrown
	 * @param integer $code    error code
	 */
	public function __construct( $message, $code = 0 ) {

		if ( $code >= ALPHRED_LOG_LEVEL ) {
			$log_levels = array(
				0 => 'DEBUG',
			  1 => 'INFO',
			  2 => 'WARNING',
			  3 => 'ERROR',
			  4 => 'CRITICAL',
			);
			// Get the relevant information from the backtrace
			$trace = debug_backtrace();
			$trace = end( $trace );
			$file  = basename( $trace['file'] );
			$line  = $trace['line'];
			$date = date( 'H:i:s', time() );
			file_put_contents( 'php://stderr',
			  "[{$file},{$line}] [{$date}] [{$log_levels[ $code ]}] {$message}" . PHP_EOL
			);
		}
	}

}

/**
 * Thrown when trying to instantiate a class that is written to be used as static only.
 */
class UseOnlyAsStatic					extends Exception {}


class InvalidKeychainAccount   extends Exception {}
class PasswordExists   				 extends Exception {}
class PasswordNotFound 				 extends Exception {}
class InvalidSecurityAction		 extends Exception {}
class UnknownSecurityException extends Exception {}




