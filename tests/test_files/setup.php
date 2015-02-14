<?php

			// We need to set up some environmental variables to spoof Alfred's environment
			$_SERVER['alfred_workflow_name'] = 'Github Repos';
			$_SERVER['alfred_workflow_bundleid'] = 'com.spr.gh.repos';
			$_SERVER['alfred_workflow_data'] =
				$_SERVER['HOME'] . '/Library/Application Support/Alfred 2/Workflow Data/com.spr.gh.repos';
			$_SERVER['alfred_workflow_cache'] =
				$_SERVER['HOME'] . '/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/com.spr.gh.repos';
			$_SERVER['alfred_version'] = 2.6;

			// We include the main file rather than the phar because phpunit doesn't track the files well in
			// a phar environment.
			require_once( __DIR__ . '/../../Main.php' );