<?php
/**
 * Performs any actions necessary
 */

// Just to test outside of the workflow environment
$_SERVER['alfred_workflow_name'] = 'Github Repos';
$_SERVER['alfred_workflow_bundleid'] = 'com.spr.gh.repos';
$_SERVER['alfred_workflow_data'] =
	$_SERVER['HOME'] . '/Library/Application Support/Alfred 2/Workflow Data/com.spr.gh.repos';
$_SERVER['alfred_workflow_cache'] =
	$_SERVER['HOME'] . '/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/com.spr.gh.repos';

// Require Alphred
require_once( __DIR__ . '/../build/Alphred.phar' );

$log = new Alphred\Log;

if ( ! isset( $argv[1] ) ) {
	die("You need an argument...\n");
}
$action = trim( $argv[1] );


// For the "set-password" action, we're going to use an AppleScript dialog
// that will present an "input hidden" password text box. This way, we do
// not have to worry that anyone is looking over our shoulders.
if ( 'set-password' == $action ) {
	// Create the dialog box. Note: you need to provide a "default answer" in
	// order for the text box to appear, so we're setting the default answer to
	// nothing at all.
	$dialog = new Alphred\AppleScript\Dialog([
	  'text' => 'Please set your Github password',
	  'title' => 'GH Repos',
	  'default_answer' => '',
	  'hidden_answer' => true
	]);
	// Run the dialog and save the output as in the $password variable. Note,
	// Alphred's AppleScript utility will strip out everything but the text
	// returned.
	$password = $dialog->execute();
	// Now we need some error checking. This is a placeholder, but, since we
	// know that Github won't allow for an empty password, then we're doing
	// to just exit the script with an error message.
	if ( empty( $password ) || 'canceled' == $password ) {
		die("Empty argument");
	}
	// So, there is a password. Let's go ahead and save it using Alphred's
	// keychain utility. This is what is needed, minimally, in order to set
	// the password.
	if ( Alphred\Keychain::save_password( 'github.com', $password ) ) {
		// Send a message to the console that we managed to set the password.
		$log->log( 'Set password for github.', 1, 'console' );
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
	if (empty( $username ) ) {
		die("Empty username");
	}
	// Create a new config object, just like we did with the script filter
	$config = new Alphred\Config( 'json' );
	// Set the username to $username. The backend will take care of escaping the
	// characters for you.
	$config->set( 'username', $username );
	// Print a message that we've set the username (for the notification).
	print "Set username to {$username}\n";
	// Exit with a 0 status so nothing else in the script runs.
	exit(0);
}

if ( filter_var( $action, FILTER_VALIDATE_URL ) ) {
	exec( "open {$action}" );
}