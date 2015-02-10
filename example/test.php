<?php

require_once( '../Main.php' );

$url = '/usr/bin/somethingelse';
print filter_var( $url, FILTER_VALIDATE_URL );

exit(0);


