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

    public function __construct( $options = [] ) {
        if ( isset( $options['config'] ) ) {
            require_once( __DIR__ . '/config.php');
            $this->config = new \Alphred\Config\Config( $options['config'] );
        }
        $this->xml = new \XMLWriter();

    }

    public function add_result( $result ) {
        if ( ! ( is_object( $result ) && ( get_class( $result ) == 'Alphred\Alfred\Result' ) ) ) {
            // Double-check that the namespacing doesn't affect the return value of "get_class"
            // raise an exception instead
            return false;
        }
        $this->results[] = $result;
    }

    public function item( $props ) {
        $tmp = new Result( $props );
        $this->add_result( $tmp );
        return $tmp;
    }

    public function get_results() {
        return $this->results;
    }

    public function to_xml() {
        $this->xml->openMemory();
        $this->xml->setIndent(4);
        $this->xml->startDocument('1.0', 'UTF-8' );
        $this->xml->startElement( 'items' );

        foreach ( $this->results as $result ) { $this->write_item( $result ); }
        $this->xml->endDocument();
        echo $this->xml->outputMemory();
    }

    private function write_item( $item ) {
        $item = $item->data;
        $attributes = [ 'uid', 'arg', 'autocomplete' ];
        $bool = [ 'valid' ];
        $this->xml->startElement( 'item' );

        foreach ( $attributes as $v ) :
            if ( ! isset( $item[$v] ) ) {
                $this->xml->writeAttribute( $v, '');
            } else {
                $this->xml->writeAttribute($v, $item[$v]);
            }
        endforeach;

        if ( isset( $item['valid'] ) && in_array( strtolower($item['valid']), ['yes', 'no', true, false]) ) {
            $valid = $item['valid'] ? 'yes' : 'no';
            $this->xml->writeAttribute('valid', $valid );
        } else {
            $this->xml->writeAttribute('valid', 'no' );
        }

        foreach ( $item as $k => $v ) :
            if ( ! in_array( $k, array_merge( $attributes, $bool ) ) ) {
                if ( strpos( $k, '_' ) !== FALSE && strpos( $k, 'subtitle' ) === 0 ) {
                    $this->xml->startElement( substr( $k, 0, strpos( $k, '_' ) ) );
                    $this->xml->writeAttribute( 'mod', substr( $k, strpos( $k, '_' ) + 1 ) );
                } else if ( strpos( $k, '_' ) !== FALSE ) {
                    // Add in checks for icon filetype
                    $this->xml->startElement( substr( $k, 0, strpos( $k, '_' ) ) );
                    $this->xml->writeAttribute( 'type', substr( $k, strpos( $k, '_' ) + 1 ) );
                } else {
                    $this->xml->startElement( $k );
                }
                $this->xml->text( $v );
                $this->xml->endElement();
            }
        endforeach;

        $this->xml->endElement();
    }

    public function set( $key, $value ) {
        return $this->config->set( $key, $value );
    }

    public function read( $key ) {
        return $this->config->read( $key );
    }

    public function remove( $key ) {
        return $this->config->remove( $key );
    }

    public function config_set( $key, $value ) {
        return $this->set( $key, $value );
    }

    public function config_read( $key ) {
        return $this->read( $key );
    }

    public function data() {
        return $_SERVER['alfred_workflow_data'];
    }

    public function cache() {
        return $_SERVER['alfred_workflow_cache'];
    }

}


class Result {

    public function __construct( $title ) {
        $this->string_methods = [
            'title',
            'icon',
            'icon_filetype',
            'icon_fileicon',
            'subtitle',
            'subtitle_shift',
            'subtitle_fn',
            'subtitle_ctrl',
            'subtitle_alt',
            'subtitle_cmd',
            'uid',
            'arg',
            'text_copy',
            'text_largetype',
            'autocomplete'
        ];
        $this->bool_methods = [ 'valid' ];

        $this->data = [];

        if ( is_string( $title ) ) {
            $this->set_title( $title );
        } else if ( is_array( $title ) ) {
            foreach ( $title as $key => $value ) :
                $fn = "set_{$key}";
                $this->$fn( $value );
            endforeach;
        }

    }

    // Let's just make a common function for all the "set" methods
    public function __call( $method, $arguments ) {
        if ( strpos( $method, "set_" ) === 0 ) {
            if ( count( $arguments ) == 1 ) {
                $m = str_replace('set_', '', $method);
                if ( is_bool( $arguments[0] ) ) {
                    if ( in_array( $m, $this->bool_methods ) ) {
                        $this->data[$m] = $arguments[0];
                        return true;
                    }
                } else if ( is_string( $arguments[0] ) ) {
                    if ( in_array( $m, $this->string_methods ) ) {
                        $this->data[$m] = $arguments[0];
                        return true;
                    }
                }
            }
        }
        // We should raise an exception here instead.
        return false;
    }
}