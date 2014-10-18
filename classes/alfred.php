<?php

// Right now, some of this code should just be in alphred.php... we'll see.

namespace Alphred\Alfred;

class Alfred {

    public function trigger() {
        // call an external trigger
    }

    public function callExternalTrigger( $bundle, $trigger, $argument = FALSE ) {
      $script = "tell application \"Alfred 2\" to run trigger \"$trigger\" in workflow \"$bundle\"";
      if ( $argument !== FALSE ) {
        $script .= "with argument \"$argument\"";
      }
      exec( "osascript -e '$script'" );
    }


}

class Workflow {
// Should we have the options of "modules" to enable here and have this as the main entry-point
// for the entire usage?

    public function add_result( $result ) {
        if ( ! ( is_object( $result ) && ( get_class( $result ) == 'Result' ) ) {
            // Double-check that the namespacing doesn't affect the return value of "get_class"
            // raise an exception instead
            return false;
        }
        $this->results[] = $result;
    }



}

class Result {

    public function __construct( $title ) {
        $string_methods = [ 'title',
                            'subtitle',
                            'subtitle_shift',
                            'subtitle_fn',
                            'subtitle_ctrl',
                            'subtitle_alt',
                            'subtitle_cmd',
                            'uid',
                            'arg',
                            'text_copy',
                            'text_large_type',
                            'autocomplete'
                            ];
        $bool_methods = [ 'valid' ];

        if ( is_string( $title ) ) {
            $this->set_title( $title );
        } else if ( is_array( $title ) ) {
            foreach ( $title as $key => $value ) {
                $this->set_$key( $value );
            }
        }


    }

    // Let's just make a common function for all the "set" methods
    public function __call( $method, $arguments ) {
        if ( strpos( $method, "set_" ) === 0 ) {
            if ( count( $arguments ) == 1 ) {
                $m = str_replace('set_', '', $method);
                if ( is_bool( $arguments[0] ) ) {
                    if ( in_array( $m, $this->bool_methods ) ) {
                        $this->$m = $arguments[0];
                        return true;
                    }
                } else if ( is_string( $arguments[0] ) ) {
                    if ( in_array( $m, $this->string_methods ) ) {
                        $this->$m = $arguments[0];
                        return true;
                    }
                }
            }
        }
        // We should raise an exception here instead.
        return false;
    }
}