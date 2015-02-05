<?php

namespace Alphred;

/**
 * This is the namespaced "Exception" class (i.e.: Alphred\Exception).
 */
class Exception extends \Exception {

	/**
	 * Writes a message to STDERR depending on log level
	 *
	 * @param string  				$message message thrown
	 * @param integer 				$code    error code
	 * @param string|boolean  $file
	 */
	public function __construct( $message, $code = 3, $file = false ) {
		// We are going to just include a log with a stacktrace on every exception.
		\Alphred\Log::console( $message, $code );

		// Do we need to record this to a file?
		if ( $file ) {
			if ( \Alphred\Globals::get('alfred_workflow_data' ) ) {
				\Alphred\Log::file( $message, $code );
			}
		}
	}

}

/**
 * Thrown when trying to instantiate a class that is written to be used as static only.
 */
class UseOnlyAsStatic					 extends Exception {}

/**
 * This is thrown when trying to use functions that require variables set by Alfred
 */
class RunningOutsideOfAlfred   extends Exception {}


class InvalidKeychainAccount   extends Exception {}
class PasswordExists   				 extends Exception {}
class PasswordNotFound 				 extends Exception {}
class InvalidSecurityAction		 extends Exception {}
class UnknownSecurityException extends Exception {}


class TooManyArguments  	     extends Exception {}
class InvalidXMLProperty			 extends Exception {}
class ShouldBeBool			 			 extends Exception {}
