<?php

// This file exists just so that I can use this for testing via the command line rather than
// having to open Alfred each time. It's just faster to see the development that way for me.
// In a real workflow, this would not and should not exist.

$_SERVER['alfred_workflow_name'] = 'Github Repos';
$_SERVER['alfred_workflow_bundleid'] = 'com.spr.gh.repos';
$_SERVER['alfred_workflow_data'] =
	$_SERVER['HOME'] . '/Library/Application Support/Alfred 2/Workflow Data/com.spr.gh.repos';
$_SERVER['alfred_workflow_cache'] =
	$_SERVER['HOME'] . '/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/com.spr.gh.repos';