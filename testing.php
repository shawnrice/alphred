<?php


require_once('classes/alfred.php');
require_once('classes/config.php');
require_once('classes/text.php');
require_once('classes/server.php');
require_once('classes/applescript.php');

// require_once('build/alphred.phar');

// For testing purposes, we set these.
$_SERVER['alfred_workflow_data'] = $_SERVER['HOME'] . '/Library/Application Support/Alfred 2/Workflow Data/com.alphred';
$_SERVER['alfred_workflow_bundleid'] = 'com.alphred';

$a = new \Alphred\AppleScript\Dialog( [
    'text' => 'This is it. Really. It is. That
  is all there is to_xml
  it   ',
    'title' => 'This is a title',
    'buttons' => ['testing', 'ok', 'cancel'],
    'default_button' => 'ok',
    ]);
$a->set_icon( 'stop' );
echo $a->execute();

$text = new \Alphred\Text;
$w = new \Alphred\Workflow(['config' => 'db']);

$w->set( 'username', 'And sha-wn but: :considering: patrick rice' );
print_r( $text->titleCase( $w->config_read( 'username' ) ) );
$w->remove( 'username' );
echo "AND NOW: " . $w->config_read( 'username' );


$result = $w->item([
    'title' => 'This is a title',
    'subtitle' => 'Subtitle',
    'subtitle_alt' => 'Shifty!',
    'valid' => true,
    'args' => 'test',
    'text_copy' => 'text copy',
    'icon_filetype' => 'pdf',
    'icon_fileicon' => 'pdf',
]);

$result->set_subtitle_cmd( 'testing' );

$w->to_xml();