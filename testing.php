<?php

namespace Alphred;

require_once('classes/Alphred.php');

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
// print_r( AppleScript\ChooseFromList::execute(
//     [ 'One', 'Two', 'Three' ],
//     [ 'multiple' => true ]
// ));
// print_r( AppleScript\ChooseFileName::execute([
//     'text' => 'Choose a damn file!',
//     'location' => "/Users/Sven/Desktop"
// ]));
print_r( AppleScript\Choose::folder([
    'text' => 'Choose a damn file!',
    'invisibles' => true,
    'multiple' => true,
    'package_contents' => true,
    'location' => "/Users/Sven/Desktop"
]));
// exit();

// print_r( $date->ago( 315234220, true ) );
// echo PHP_EOL;

// $text = new Text;
// $w = new ScriptFilter(['config' => 'db']);

// $w->set( 'username', 'And sha-wn but: :considering: patrick rice' );
// print_r( $text->titleCase( $w->config_read( 'username' ) ) );
// $w->remove( 'username' );
// echo "AND NOW: " . $w->config_read( 'username' );


// $result = $w->item([
//     'title' => 'This is a title',
//     'subtitle' => 'Subtitle',
//     'subtitle_alt' => 'Shifty!',
//     'valid' => true,
//     'args' => 'test',
//     'text_copy' => 'text copy',
//     'icon_filetype' => 'pdf',
//     'icon_fileicon' => 'pdf',
// ]);

// $result->set_subtitle_cmd( 'testing' );

// $w->to_xml();