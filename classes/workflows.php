<?php

namespace Alphred\Workflows;

class Workflows {

    public function __construct() {
        if ( ! isset( $_SERVER['alfred_workflow_data'] ) ) {
            // should throw an exception
            return false;
        }
        $this->map_file = $_SERVER['alfred_workflow_data'] . '/workflow_map.json';
    }

    public function find() {
        $base = $_SERVER['PWD'];
        $workflows = json_decode( file_get_contents( $this->map_file ), true );
        // make this a better search thingie... or maybe make this work with the db function...
        // finds a workflow path
    }

    public function map() {

        $wfs = scandir( '..', array_diff( '.', '..', '.DS_Store' ) );
        $pb = '/usr/libexec/PlistBuddy';
        $workflows = [];
        foreach ( $wfs as $w ) :
            if ( strpos( $w, 'user.workflow.' ) === 0 ) {
                if ( ! file_exists( "{$w}/info.plist" ) ) continue;
                // I need to alter this to protect from errors
                $cmd = "{$pb} -c 'print :bundleid' '{$w}/info.plist'";
                $bundle = exec( $cmd );
                $cmd = "{$pb} -c 'print :name' '{$w}/info.plist'";
                $name = exec( $cmd );
                $uid = $w;
                $workflows[$bundle] = array(
                    'bundle' => $bundle,
                    'name'   => $name,
                    'dir'    => $w
                );
            }
        endforeach;

        file_put_contents( $this->map_file, json_encode( $workflows ) );
    }

}