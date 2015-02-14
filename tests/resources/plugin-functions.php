<?php

function my_test_get_password() {
	return 'testpassword';
}

function testing_on_load_plugin() {
	file_put_contents( 'onload-test-file.txt', 'a string' );
}