<?php
/**
 * Performs any actions necessary
 */

// For quicker development purposes, I'm setting some of the variables here so that it
// can run via the command line. Don't do this in a real workflow.
require_once( __DIR__ . '/test_vars.php' );

// Require Alphred
require_once( __DIR__ . '/Alphred.phar' );;

// Instantiate an Alphred object, but since this isn't run as a script filter, we'll just
// turn off the filter. It will run just fine if we left it on, but we're turning it off.
$Alphred = new Alphred( [ 'no_filter' => true ] );

if ( ! isset( $argv[1] ) ) {
	$Alphred->console( 'Cannot run `' . basename( __FILE__ ) . '` without an argument.', 4 );
	exit( 1 );
}
$action = trim( $argv[1] );


// For the "set-password" action, we're going to use an AppleScript dialog
// that will present an "input hidden" password text box. This way, we do
// not have to worry that anyone is looking over our shoulders.
if ( 'set-password' === $action ) {
	// Run the dialog and save the output as in the $password variable. Note,
	// Alphred's AppleScript utility will strip out everything but the text
	// returned.
	$password = $Alphred->get_password_dialog();
	// Now we need some error checking. This is a placeholder, but, since we
	// know that Github won't allow for an empty password, then we're doing
	// to just exit the script with an error message.
	if ( empty( $password ) || 'canceled' === $password ) {
		die( 'Empty argument' );
	}
	// So, there is a password. Let's go ahead and save it using Alphred's
	// keychain utility. This is what is needed, minimally, in order to set
	// the password.
	if ( $Alphred->save_password( 'github.com', $password ) ) {
		// Send a message to the console that we managed to set the password.
		$Alphred->console( 'Set password for github.', 1 );
	}
}

// Next, let's check to see if the action is to set the username. Since we're
// pushing the value of the username along with the action, we'll parse the
// $action variable to see if it's there. Based on how we've set up our script
// filter, we know that the "set-username" action will appear as
// 		"set-username <username>"
// So, we're doing to go ahead and check to see if "set-username" exists in the
// action string at position 0, or the start of the string. Notice the three
// equals signs here. If you try to use two, then strpos will give you false positives
// because 0 will evaluate to false. Use === not ==.
if ( 0 === strpos( $action, 'set-username ' ) ) {
	// So, we're setting the username. So, let's just strip the name of the action
	// out and consider the rest the username, and we'll also trim the whitespace
	// from each side.
	$username = trim( str_replace( 'set-username', '', $action ) );
	// Error checking: Make sure that there is something in the username variable now.
	if ( empty( $username ) ) {
		die( 'Empty username' );
	}
	// Create a new config object, just like we did with the script filter
	$Alphred->config_set( 'username', $username );
	// $config = new Alphred\Config( 'json' );
	// Set the username to $username. The backend will take care of escaping the
	// characters for you.
	// $config->set( 'username', $username );
	// Print a message that we've set the username (for the notification).
	print "Set username to {$username}\n";
	// Exit with a 0 status so nothing else in the script runs.
	exit( 0 );
}

if ( filter_var( $action, FILTER_VALIDATE_URL ) ) {
	exec( "open {$action}" );
}
