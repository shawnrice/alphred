<?php

// Example that connects to Github

// Just to test outside of the workflow environment
$_SERVER['alfred_workflow_name'] = 'Github Repos';
$_SERVER['alfred_workflow_bundleid'] = 'com.spr.gh.repos';
$_SERVER['alfred_workflow_data'] =
	$_SERVER['HOME'] . '/Library/Application Support/Alfred 2/Workflow Data/com.spr.gh.repos';
$_SERVER['alfred_workflow_cache'] =
	$_SERVER['HOME'] . '/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/com.spr.gh.repos';


// Require Alphred
require_once( __DIR__ . '/../build/Alphred.phar' );

$query = ''; // Initialize an empty query

// This handles things from the command line for testing
unset( $argv[0] ); // unset the path
if ( isset( $argv[1] ) ) {
	$query = trim( implode(' ', $argv ) ); // Make argv $query for now.
}

// print "Query: '{$query}'\n";

$filter = new Alphred\ScriptFilter;

// We want to use the built-in configuration utility, and we'll go ahead and use the standard
// JSON format. So, create a new "config" utility.
$config = new Alphred\Config( 'json' );
// Read the username from the config file
$username = $config->read('username');

// If the username has not been set, then we'll just show one option for the script filter
// that will lead to the action to set the username.
if ( empty( $username ) ) {
	$filter->add_result( new Alphred\Result([
	    'title' => 'Please set your username',
	    'arg'   => "set-username {$query}",
	    'valid' => true
	]));
	// Print out the XML
	$filter->to_xml();
	// Exit the script with a status of 0 (which means sucessfully completed -- no errors)
	exit(0);
}

// We're going to try to read the password from the Keychain. Since the Alphred's keychain
// interface will throw an exception if the password is not found, we'll wrap this in a "try/catch"
// block.
//
// Note, we're catching only the "Alphred\PasswordNotFound" exception.
try {
	$password = Alphred\Keychain::find_password( 'github.com' );
} catch (Alphred\PasswordNotFound $e) {
		// The password has not been set, so we'll provide only one option to set the password
		$filter->add_result( new Alphred\Result([
	    'title' => 'Press enter to set your password',
	    'arg'   => 'set-password',
	    'valid' => true
	]));
	// Print out the XML
	$filter->to_xml();
	// Exit the script with a status of 0 (which means sucessfully completed -- no errors)
	exit(0);
}

// At this point, we now have the username and password set, so the workflow should be configured.
// So we'll go ahead and start to construct a call to Github.
// It isn't stated, but data caching is turned on by default with a set max-life of 600 seconds.
// $request = new Alphred\Request( "https://api.github.com/users/{$username}/repos" );
$request = new Alphred\Request( "https://api.github.com/users/{$username}/repos" );

// Github advises us to explicitly add the header below
$request->set_headers([ 'Accept: application/vnd.github.v3+json' ]);

// Github also demands that we set a user-agent
$request->add_user_agent( 'something goes here' );

// Github gives us a default of 30 repos in the response, but we can push it to 100
$request->add_parameter( 'per_page', 100 );

// Lastly, we're using basic authorization with Github rather than any Oauth or Access Tokens, so
// we'll go ahead and add in the basic authorization with the username and password below.
$request->set_auth( $username, $password );


// The request has been setup, so let's execute it. We know that we're getting JSON data, so we'll
// also decode it into an easily accessible array.
$repos =  json_decode( $request->execute(), true );

// Okay, now, if there is a query, then we'll use that to filter out the repos
if ( ! empty( $query ) ) {
	// So, Alpred's filter will filter out all things that don't match the query, and it will also
	// reorganize the array so that the highest match is at the top. Granted, Alfred will override
	// the sort order if a uid is present.
	$matches = Alphred\Filter::Filter($repos, $query, 30, 'name', MATCH_ALL );
} else {
	// There was no query, so the answer is the full set
	$matches = $repos;
}

// Let's go ahead and add each to the script filter results
foreach ( $matches as $match ) :
	// Let's use one of the text filters to tell us how long ago something was updated.
	$updated = "Last updated " . Alphred\Date::ago( strtotime( $match['updated_at'] ) ) . ".";
	// Alphred lets us add results by adding an Alphred\Result object. While we can create these and
	// modify them over the course of the script, we'll just create the Result object in the `add_result`
	// method call.
	$icon = 'icons/octoface-light.png';
	$filter->add_result( new Alphred\Result([
	    // I want Alfred to show the name of the repo as the ttle
	    'title' 	 			 => $match['name'],
	    // We'll add in the appropriate icon
	    'icon' 					 => $icon,
	    // The description will the the subtitle
	    'subtitle' 			 => $match['description'],
	    // See the stargazers when you press shift
	    'subtitle_shift' => $match['stargazers_count'] . ' stars.',
	    // See the forks when you press function
	    'subtitle_fn' 	 => $match['forks_count'] . ' forks.',
	    // See when this was last updated when you press command
	    'subtitle_cmd' 	 => $updated,
	    // See the open issues when you press control
	    'subtitle_ctrl'  => $match['open_issues'] . ' open issues.',
	    // Right now, this just shows the icon. What should we put here?
	    'subtitle_alt'   => $icon,
	    // Add in a uid so that Alfred can do its sorting magic
	    'uid' 		 			 => $match['name'],
	    'arg'      			 => $match['html_url'],
	    'valid'    			 => true
	]));
endforeach;

// Send out the script filter XML
$filter->to_xml();