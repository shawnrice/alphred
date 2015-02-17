<?php
// Just a router file to help with the testing...

$array = [];
$array['items'][] = 'this is some testing framework stuff';
$array['items'][] = 'yet another string';
$array['items'][] = 'totally another string';
$array['items'][] = 'Lorem Ipsum Stuff';
$array['items'][] = 'ABCs of Alphabet City';
$array['items'][] = 'All Bucolic Cattle Do Eat Fate';

$array['agent'] = $_SERVER['HTTP_USER_AGENT'];
$array['method'] = $_SERVER['REQUEST_METHOD'];

if ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
	$array['user'] = $_SERVER['PHP_AUTH_USER'];
}
if ( isset( $_SERVER['PHP_AUTH_PW'] ) ) {
	$array['user'] = $_SERVER['PHP_AUTH_PW'];
}
if ( count( $_GET ) > 0 ) {
	$array = array_merge_recursive( $array, $_GET );
}
if ( count( $_POST ) > 0 ) {
	$array = array_merge_recursive( $array, $_POST );
}

print json_encode( $array, JSON_PRETTY_PRINT );