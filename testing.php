<?php

namespace Alphred;

require_once( 'Main.php' );

// // require_once('build/alphred.phar');

// // For testing purposes, we set these.
// $_SERVER['alfred_workflow_data'] = $_SERVER['HOME'] . '/Library/Application Support/Alfred 2/Workflow Data/com.alphred';
// $_SERVER['alfred_workflow_bundleid'] = 'com.alphred';

// $date = new ;
// $as = new AppleScript\Dialog([
//     'text' => 'Hello!',
//     'title' => 'What?',
//     'icon' => 'note',
//     'buttons' => [ 'One', 'Two', 'Three' ],
//     'default_button' => 'One',
//     'cancel' => 'Two',
//     'timeout' => 1
// ]);
// echo $as->execute();

// AppleScript\Notification::notify( ['text'=> 'Testing!', 'title' => 'what?', 'sound' => 'Purr']);
// print_r( AppleScript\ChooseFile::execute([
//     'text' => 'Choose a damn file!',
//     'invisibles' => true,
//     'multiple' => true,
//     'package_contents' => true,
//     'type' => [ 'txt', 'doc', 'docx' ],
//     'location' => "/Users/Sven/Desktop"
// ]));
// print_r( AppleScript\Choose::from_list(
//     [ 'One', 'Two', 'Three' ],
//     [
//         'multiple' => true,
//         'default' => 'Two',
//         'ok'    => 'Thats what I want',
//         'empty' => 'true'
//     ]
// ));

print_r( AppleScript::get_front() );

// print_r( AppleScript\ChooseFileName::execute([
//     'text' => 'Choose a damn file!',
//     'location' => "/Users/Sven/Desktop"
// ]));
// print_r( AppleScript\Choose::folder([
//     'text' => 'Choose a damn file!',
//     'invisibles' => true,
//     'multiple' => true,
//     'package_contents' => true,
//     'location' => "/Users/Sven/Desktop"
// ]));
// exit();