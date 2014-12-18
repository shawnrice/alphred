<?php

// This file is to be included in any scripts that you use with the server
// so that things can be routed correctly.

if ( 'cli-server' === php_sapi_name() ) {
    $argv[1]                             = $_POST['query'];
    $_SERVER['alfred_workflow_bundleid'] = $_POST['alfred_workflow_bundleid'];
    $_SERVER['alfred_workflow_data']     = $_POST['alfred_workflow_data'];
    $_SERVER['alfred_workflow_cache']    = $_POST['alfred_workflow_cache'];
}

