<?php

// Example that connects to Github
// https://developer.github.com/v3/

// Note: for testing purposes, these scripts link to the individual Alphred files. You should
// use the `phar` instead. If using the cli-server, you should unpack the scripts using
// `php Alphred.phar create-server-scripts` and use those.

// Make this available via the CLI-server
require_once( __DIR__ . '/server-scripts/server.php' );

// For quicker development purposes, I'm setting some of the variables here so that it
// can run via the command line. Don't do this in a real workflow.
if ( ! isset( $_SERVER['alfred_workflow_data'] ) ) {
	require_once( __DIR__ . '/test_vars.php' );
}

// Require Alphred
require_once( __DIR__ . '/Alphred.phar' );

$query = ''; // Initialize an empty query

// This handles things from the command line for testing
unset( $argv[0] ); // unset the path
if ( isset( $argv[1] ) ) {
	$query = trim( implode(' ', $argv ) ); // Make argv $query for now.
}

$Alphred = new Alphred([ 'error_on_empty' => true ]);

// Read the username from the config file
$username = $Alphred->config_read( 'username' );

// If the username has not been set, then we'll just show one option for the script filter
// that will lead to the action to set the username.
if ( ! $username ) {
	$Alphred->add_result([
			'title'    => 'Please set your username',
			'subtitle' => "Set username to `{$query}`",
			'arg'      => "set-username {$query}",
			'valid'    => true
	]);
	// Print out the XML
	$Alphred->to_xml();
	// Exit the script with a status of 0 (which means sucessfully completed -- no errors)
	exit(0);
}

// We're going to try to read the password from the Keychain.
$password = $Alphred->get_password( 'github.com' );
if ( ! $password || empty( $password ) ) {
	// The password has not been set, so we'll provide only one option to set the password
	$Alphred->add_result([
			'title' => 'Press enter to set your password',
			'arg'   => 'set-password',
			'valid' => true
	]);
	// Print out the XML
	$Alphred->to_xml();
	// Exit the script with a status of 0 (which means sucessfully completed -- no errors)
	exit(0);
}

// At this point, we now have the username and password set, so the workflow should be configured.
// So we'll go ahead and start to construct a call to Github.

// It isn't stated, but data caching is turned on by default with a set max-life of 600 seconds,
// which is ten minutes. The options are long, so we'll go ahead and set them one by one.

// Github advises us to explicitly add the header below
$options['headers'] = [ 'Accept: application/vnd.github.v3+json' ];
// Github also demands that we set a user-agent
$options['user_agent'] = 'alfred';
// Github gives us a default of 30 repos in the response, but we can push it to 100. Let's get 100.
$options['params'] = [ 'per_page' => 100 ];
// Lastly, we're using basic authorization with Github rather than any Oauth or Access Tokens, so
// we'll go ahead and add in the basic authorization with the username and password below.
$options['auth'] = [ $username, $password ];
// The request variables have been set, so let's execute it. If we wanted to adjust the caching options,
// then we'd pass another argument.
$repos = $Alphred->get( "https://api.github.com/users/{$username}/repos", $options );
// We know that we're getting JSON data, so we'll also decode it into an easily accessible array.
$repos = json_decode( $repos, true );

$Alphred->console( "Queried: `https://api.github.com/users/{$username}/repos`", 1 );
/*
 We could have just pushed all of that to this long, long call:

 $repos = json_decode( $Alphred->request_get( "https://api.github.com/users/{$username}/repos", [
	'params' => [ 'per_page' => 100 ],
	'auth' => [ $username, $password ],
	'user_agent' => 'alfred',
	'headers' => [ 'Accept: application/vnd.github.v3+json' ]
 ]), true );

*/

// Okay, now, if there is a query, then we'll use that to filter out the repos
if ( ! empty( $query ) ) {
	// So, Alphred's filter will filter out all things that don't match the query, and it will also
	// reorganize the array so that the highest match is at the top. Granted, Alfred will override
	// the sort order if a uid is present.
	$matches = $Alphred->filter( $repos, $query, 'name' );
} else {
	// There was no query, so the answer is the full set
	$matches = $repos;
}

// Let's get the inverse of the background of the theme so that we can assign the right icon
$color = ( 'light' == $Alphred->theme_background() ) ? 'dark' : 'light';

if ( 0 == count( $matches ) ) {
	$Alphred->add_result([
	  'title' => 'No Repositories found',
	  'icon'  => '/System/Library/CoreServices/CoreTypes.bundle/Contents/Resources/Unsupported.icns'
	]);
	$Alphred->to_xml();
	exit(0);
}

// Let's go ahead and add each to the script filter results
foreach ( $matches as $match ) :
	// Let's use one of the text filters to tell us how long ago something was updated.
	$updated = "Last updated " . $Alphred->fuzzy_time_diff( strtotime( $match['updated_at'] ) ) . ".";
	// Alphred lets us add results by adding an Alphred\Result object. While we can create these and
	// modify them over the course of the script, we'll just create the Result object in the `add_result`
	// method call.

	// Figure out the icon with the color from above
	$icon = __DIR__ . "/icons/mark-github-{$color}.png";
	$Alphred->add_result([
			// I want Alfred to show the name of the repo as the title
			'title'          => $match['name'],
			// We'll add in the appropriate icon
			'icon'           => $icon,
			// The description will the the subtitle
			'subtitle'       => $match['description'],
			// See the stargazers when you press shift
			'subtitle_shift' => $match['stargazers_count'] . ' stars.',
			// See the forks when you press function
			'subtitle_fn'    => $match['forks_count'] . ' forks.',
			// See when this was last updated when you press command
			'subtitle_cmd'   => $updated,
			// See the open issues when you press control
			'subtitle_ctrl'  => $match['open_issues'] . ' open issues.',
			// Right now, this just shows the icon. What should we put here?
			'subtitle_alt'   => $icon,
			// Add in a uid so that Alfred can do its sorting magic
			'uid'            => $match['name'],
			// Set the argument to the URL, so that we can open it with the action
			'arg'            => $match['html_url'],
			// Setting valid to `true` means that we can action it
			'valid'          => true
	]);
endforeach;

// Send out the script filter XML
$Alphred->to_xml();