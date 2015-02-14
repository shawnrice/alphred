<?php
// Just a router file to help with the testing...

$array = [
	'item1' => 'this is some testing framework stuff',
	'item2' => 'yet another string',
	'item3' => 'totally another string',
	'item4' => 'Lorem Ipsum Stuff',
	'item5' => 'ABCs of Alphabet City',
	'item6' => 'All Bucolic Cattle Do Eat Fate'
];

$array['agent'] = $_SERVER['HTTP_USER_AGENT'];
$array['method'] = $_SERVER['REQUEST_METHOD'];

if ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
	$array['user'] = $_SERVER['PHP_AUTH_USER'];
}
if ( isset( $_SERVER['PHP_AUTH_PW'] ) ) {
	$array['user'] = $_SERVER['PHP_AUTH_PW'];
}




print_r( $_SERVER );
print_r( $_GET );
print_r( $_POST );
print json_encode( $array, JSON_PRETTY_PRINT );