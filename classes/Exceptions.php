<?php

namespace Alphred;

/**
 * This is the namespaced "Exception" class (i.e.: Alphred\Exception)
 *
 * Alphred's Exception interface adds in some standard logging functionality
 * whenever any exception is thrown.
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

/**
 * Thrown when trying to pass bad keys to a script filter
 */
class InvalidScriptFilterArgument extends Exception {}

/**
 * Keychain error
 */
class InvalidKeychainAccount   extends Exception {}

/**
 * Exception thrown when trying to set a password in the keychain without the `update` flag
 */
class PasswordExists   				 extends Exception {}

/**
 * Thrown when trying to get a password that has not been set
 */
class PasswordNotFound 				 extends Exception {}

/**
 * Thrown with a bad error code.
 *
 * If you use the library through the wrapper, then you should never see this; but if you
 * extend it, then you might.
 */
class InvalidSecurityAction		 extends Exception {}
/**
 * Thrown when `security` doesn't know what to do
 *
 * If you use the library through the wrapper, then you should never see this; but if you
 * extend it, then you might.
 */
class UnknownSecurityException extends Exception {}

/**
 *
 */
class TooManyArguments  	     extends Exception {}
/**
 * Thrown when trying to set an XML property that should not exist
 */
class InvalidXMLProperty			 extends Exception {}

/**
 * Thrown when sending something that should be a bool but isn't
 */
class ShouldBeBool			 			 extends Exception {}

/**
 * Thrown when trying to access a file that does not exist
 */
class FileDoesNotExist				 extends Exception {}

/**
 * Thrown when trying to load a plugin that has not been defined
 *
 * Usually, you can correct this by `including` or `requiring` the plugin code before either
 * including the library or instantiating the Alphred wrapper. Otherwise, check for syntax
 * or spelling errors.
 */
class PluginFunctionNotFound   extends Exception {}

/**
 * Thrown when trying to get a config key that has not been set
 *
 * This exists so that there is the difference between `not set` or `undefined` and `false`
 */
class ConfigKeyNotSet 				 extends Exception {}