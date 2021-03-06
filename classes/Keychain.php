<?php
/**
 * Keychain classes for Alphred
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
 * Enables easy access to parts of the Keychain for secure password storage / retrieval
 *
 * Uses the `security` command in order to add / retrieve / delete passwords. Note: we use
 * only the "generic" password functions and not the "internet" password functions.
 *
 * @see https://developer.apple.com/library/mac/documentation/Darwin/Reference/ManPages/man1/security.1.html security(1)
 *
 */
class Keychain {

	/**
	 * Throws an exception if you try to instantiate it
	 *
	 * @throws UseOnlyAsStatic if you try to institate a Globals object
	 */
	public function __construct() {
		throw new UseOnlyAsStatic( 'The Keychain class is to be used statically only.', 2 );
	}

	/**
	 * Saves a password to the keychain
	 *
	 * @throws PasswordExists			(indirectly )when trying to add a password that already exists
	 *         										without specifying 'update'
	 *
	 * @param  string  	$account  		the name of the account
	 * @param  string  	$password 		the new password
	 * @param  boolean 	$update     	whether or not to update an old password (defaults to `true`)
	 * @param  string 	$service 			optional: defaults to the bundleid of the workflow (if set)
	 *
	 * @return boolean            		whether or not it was successful (usually true)
	 */
	public static function save_password( $account, $password, $update = true, $service = null ) {
		if ( $update ) {
			$update = ' -U';
		} else {
			$update = '';
		}
		return self::call_security( 'add-generic-password', $service, $account, "{$update} -w '{$password}'" );
	}


	/**
	 * Retrieves a password from the keychain
	 *
	 * @throws InvalidKeychainAccount 	on an empty account
	 *
	 * @param  string $account 			the name of an account
	 * @param  string $service 			optional: defaults to the bundleid of the workflow (if set)
	 *
	 * @return string          			the password
	 */
	public static function find_password( $account, $service = null ) {
		// Make sure that the account is something other than whitespace
		if ( empty( trim( $account ) ) ) {
			throw new InvalidKeychainAccount( 'You must specify an account to get a password', 3 );
		}

		return self::call_security( 'find-generic-password', $service, $account, '-w' );
	}


	/**
	 * Deletes a password from the keychain
	 *
	 * @param  string $account 			the name of the account
	 * @param  string $service 			optional: defaults to the bundleid of the workflow (if set)
	 * @return boolean          		success of command
	 */
	public static function delete_password( $account, $service = null ) {
		if ( empty( trim( $account ) ) ) {
			throw new InvalidKeychainAccount(
			    'The action you just attempted will delete the entire keychain; please specify the account', 3
			);
		}
		return self::call_security( 'delete-generic-password', $service, $account, '' );
	}


	/**
	 * Interfaces directly with the `security` command
	 *
	 * @throws PasswordExists			when trying to add a password that already exists without specifying 'update'
	 * @throws PasswordNotFound			when trying to find a password that does not exist
	 * @throws UnknownSecurityException when something weird happens
	 *
	 * @param  string 	$action  	one of 'add-', 'delete-', or 'find-generic-password'
	 * @param  string 	$service 	the "owner" of the action; usually the bundle id
	 * @param  string 	$account 	the "account" of the password
	 * @param  string 	$args    	extra arguments for the security command
	 * @return string|boolean       either a found password or true
	 */
	private static function call_security( $action, $service, $account, $args ) {
		if ( ! in_array( $action, [ 'add-generic-password', 'delete-generic-password', 'find-generic-password' ] ) ) {
			throw new InvalidSecurityAction( "{$action} is not valid.", 4 );

			// So, if, for some reason, the thing is caught, we can't really go on. So we'll exit anyway.
			return false;
		}
		$service = self::set_service( $service );

		// Note: $args needs to be escaped in the function that calls this one
		$command = "security {$action} -s '{$service}' -a '{$account}'  {$args}";
		exec( $command, $output, $return_code );
		if ( 45 == $return_code ) {
			// raise exception because password already exists
			throw new PasswordExists( 'Password Already Exists, did you mean to update it?', 2 );
		} else if ( 44 == $return_code ) {
			// raise exception because password does not exist
			throw new PasswordNotFound( "Password for '{$account}' does not exist", 3 );
		} else if ( 0 == $return_code ) {
			// Do nothing here. For now.
			// @todo Do something here.
		} else {
			throw new UnknownSecurityException(
				'An unanticipated error has happened when trying to call the security command', 4
			);
		}

		if ( 'find-generic-password' === $action ) {
			/**
			 * @todo Test that this is exactly what we need to return
			 */
			return $output[0];
		}
		return true;
	}

	/**
	 * Sets the service appropriately, usually to the bundle id of the workflow
	 */
	private static function set_service( $service ) {

		// The service has not been set, so let's set it to the bundle id of the workflow
		if ( is_null( $service ) ) {
			if ( Globals::bundle() ) {
				$service = Globals::bundle();
			}
		}
		return $service;
	}


}