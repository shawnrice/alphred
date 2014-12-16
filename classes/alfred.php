<?php

// Right now, some of this code should just be in alphred.php... we'll see.

namespace Alphred;

class Alfred {

    public function __construct( $options = [ 'create_directories' => false ] ) {
        if ( true === $options['create_directories'] ) {
            $this->create_directories();
        }
    }

    public function create_directories() {
        if ( ! $this->data() ) {
            return false;
        }
        if ( ! file_exists( $this->data() ) ) {
            mkdir( $this->data() );
        }
        if ( ! file_exists( $this->cache() ) ) {
            mkdir( $this->cache() );
        }

        return true;
    }

    public function user() {
      return $_SERVER['USER'];
    }

    public function bundle() {
      return $_SERVER['alfred_workflow_bundleid'];
    }

    public function data() {
      return $_SERVER['alfred_workflow_data'];
    }

    public function cache() {
      return $_SERVER['alfred_workflow_cache'];
    }

    public function uid() {
      return $_SERVER['alfred_workflow_uid'];
    }

    public function workflow_name() {
      return $_SERVER['alfred_workflow_name'];
    }

    public function theme_subtext() {
      return $_SERVER['alfred_theme_subtext'];
    }

    public function alfred_version() {
      return $_SERVER['alfred_version'];
    }

    public function alfred_build() {
      return $_SERVER['alfred_version_build'];
    }

    public function dir() {
      return $_SERVER['PWD'];
    }

    public function theme_background() {
      return $_SERVER['alfred_theme_background'];
    }

    public function trigger( $bundle, $trigger, $argument = false ) {
        return $this->call_external_trigger( $bundle, $trigger, $argument );
    }

    public function call_external_trigger( $bundle, $trigger, $argument = false ) {
      $script = "tell application \"Alfred 2\" to run trigger \"$trigger\" in workflow \"$bundle\"";
      if ( $argument !== false ) {
        $script .= "with argument \"$argument\"";
      }
      return exec( "osascript -e '$script'" );
    }

    // should I take these out?
    public function _( $string ) {
        if ( ! isset( $this->i18n ) )
            $this->i18n = new \Alphred\i18n;
        if ( $this->i18n === false )
            return $string;
        return $this->i18n->translate( $string );
    }

    public function t( $string ) {
        return $this->_( $string );
    }

}

class ScriptFilter {
// Should we have the options of "modules" to enable here and have this as the main entry-point
// for the entire usage?

    public function __construct( $options = [] ) {

        if ( isset( $options['config'] ) ) {
            require_once( __DIR__ . '/config.php');
            $this->config = new \Alphred\Config\Config( $options['config'] );
        }

        $this->il18 = false;
        foreach( ['localize', 'localise', 'il8n' ] as $localize ) :
            if ( isset( $options[ $localize ] ) && $options[ $localize ] ) {
                $this->initializei118n();
                break;
            }
        endforeach;

        // We'll just save all the options for later use if necessary
        $this->options = $options;

        $this->results = [];
        $this->xml = new \XMLWriter();

    }

    private function initializei118n() {
        if ( class_exists( '\Alphred\i18n' ) ) {
            $this->il18 = new \Alphred\i18n;
        }
    }

    private function t( $string ) {
        if ( ! isset( $this->i18n ) ) {
            return $string;
        }
        return $this->i18n->translate( $string );
    }

    public function add_result( $result ) {
        if ( ! ( is_object( $result ) && ( get_class( $result ) == 'Alphred\Result' ) ) ) {
            // Double-check that the namespacing doesn't affect the return value of "get_class"
            // raise an exception instead
            return false;
        }
        $this->results[] = $result;
    }

    public function item( $props ) {
        $tmp = new \Alphred\Result( $props );
        $this->add_result( $tmp );
        return $tmp;
    }

    public function get_results() {
        return $this->results;
    }

    public function to_xml() {

        if ( true === $this->options[ 'error_on_empty' ] ) {
            if ( 0 == count( $this->get_results() ) ) {
                $result = new \Alphred\Result( [
                    'title'    => 'Error: No results found.',
                    'icon'     => '/System/Library/CoreServices/CoreTypes.bundle/Contents/Resources/Unsupported.icns',
                    'subtitle' => 'Please search for something else.',
                    'valid'    => false
                ]);
                $this->add_result( $result );
            }
        }

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
                if ( ( 'autocomplete' !== $v ) && ( 'uid' !== $v ) ) {
                    $this->xml->writeAttribute( $v, '' );
                }
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
                if ( strpos( $k, '_' ) !== false && strpos( $k, 'subtitle' ) === 0 ) {
                    $this->xml->startElement( substr( $k, 0, strpos( $k, '_' ) ) );
                    $this->xml->writeAttribute( 'mod', substr( $k, strpos( $k, '_' ) + 1 ) );
                } else if ( strpos( $k, '_' ) !== false ) {
                    // Add in checks for icon filetype
                    $this->xml->startElement( substr( $k, 0, strpos( $k, '_' ) ) );
                    $this->xml->writeAttribute( 'type', substr( $k, strpos( $k, '_' ) + 1 ) );
                } else {
                    $this->xml->startElement( $k );
                }
                $this->xml->text( $this->t( $v ) );
                $this->xml->endElement();
            }
        endforeach;

        $this->xml->endElement();
    }

    // public function read( $key ) {
    //     return $this->config->read( $key );
    // }

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

    // extra meta function that will let you set more than one thing at once
    public function set( $options ) {
        if ( ! is_array( $options ) ) {
            return false;
        }

        foreach ( $options as $option => $value ) :
            $method = "set_{$option}";
            $this->$method( $value );
        endforeach;
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