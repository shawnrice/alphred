<?php

namespace Alphred;

class Globals {

    public function get( $name ) {
        $variables = [
            'alfred_theme_background',
            'alfred_theme_subtext',
            'alfred_version',
            'alfred_version_build',
            'alfred_workflow_bundleid',
            'alfred_workflow_cache',
            'alfred_workflow_data',
            'alfred_workflow_name',
            'alfred_workflow_uid',
            'PWD',
            'USER'
        ];

        if ( in_array( $name, $variables ) ) {
            return $_SERVER[$name];
        } else {
            return false;
        }
    }

}