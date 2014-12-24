<?php

namespace Alphred;

class Exception extends \Exception {

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




		// file_put_contents( 'php://stderr', "[{$date}] " .
		// 	"[{$this->file},{$this->line}] [WARNING] Log level '{$level}' " .
		// "is not valid. Falling back to 'INFO' (1)" . PHP_EOL );