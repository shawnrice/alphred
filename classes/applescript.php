<?php

namespace Alphred\AppleScript;

// So this file is an absolute mess.
// I might need to rethink if I will / should reorganize all the classes
// into something a bit better....

class AppleScript {

    // uses applescript to get the information about a lot of system stuff

    public function front() {

    }

    public function activate() {

    }

    public function tab() {

    }

}

class Notification {

    // The notification will always have the script editor icon on it.
    // Use CocoaDialog for better notifications.
    public function notify( $options ) {
        if ( is_string( $options ) ) {
            exec( "osascript -e 'display notification \"{$options}\"'" );
            return true;
        }
        if ( ! isset( $options['text'] ) ) {
            // throw exception
            return false;
        }

        $sounds = [
                'Basso',
                'Bottle',
                'Funk',
                'Hero',
                'Ping',
                'Purr',
                'Submarine',
                'Blow',
                'Frog',
                'Glass',
                'Morse',
                'Pop',
                'Sosumi',
                'Tink'
        ];

        $script = "osascript -e 'display notification \"{$options['text']}\"";
        foreach ( $options as $field => $option ) :
            switch ( $field ) :
                case 'title' :
                    $script .= " with title \"{$option}\"";
                    break;
                case 'subtitle' :
                    $script .= " subtitle \"{$option}\"";
                    break;
                case 'sound' :
                    if ( in_array( $option, $sounds ) ) {
                        $script .= " sound name \"{$option}\"";
                    }
                    break;
                default:
                    break;
            endswitch;
        endforeach;
        $script .= "'";
        exec( $script );
    }
}

class Dialog {

    public function __construct( $values = [] ) {
        if ( count( $values ) > 0 ) {
            foreach ( $values as $k => $v ) :
                if ( method_exists( $this, "set_{$k}" ) ) {
                    $method = "set_{$k}";
                    $this->$method( $v );
                }
            endforeach;
        }

    }

    private function create_dialog() {
        $this->script = "display dialog \"{$this->text}\"";
        if ( isset( $this->buttons_ ) )       $this->script .= $this->buttons_;
        if ( isset( $this->default_answer ) ) $this->script .= $this->default_answer;
        if ( isset( $this->title ) )          $this->script .= $this->title;
        if ( isset( $this->icon ) )           $this->script .= $this->icon;
        if ( isset( $this->hidden_answer ) )  $this->script .= $this->icon;
        if ( isset( $this->cancel ) )         $this->script .= $this->cancel;
        if ( isset( $this->timeout ) )        $this->script .= $this->timeout;
    }

    public function execute() {
        $this->create_dialog();
        $result = exec( "osascript -e '{$this->script}' 2>&1" );
        if ( false !== strpos( $result, ', gave up:false' ) ) {
            $result = str_replace( ', gave up:false', '', $result );
        }
        if ( false !== strpos( $result, 'gave up:true' ) ) {
            return 'timeout';
        }
        if ( false !== strpos( $result, 'execution error: User canceled. (-128)' ) ) {
            return 'canceled';
        }
        if ( strpos( $result, 'text returned:' ) !== false ) {
            return substr( $result, strpos( $result, 'text returned:' ) + 14 );
        }
        return str_replace( 'button returned:', '', $result );
    }

    public function set_icon( $icon ) {
        $default_icons = [ 'stop', 'note', 'caution' ];
        if ( in_array( strtolower( $icon ), $default_icons ) ) {
            $this->icon = ' with icon ' . array_search( $icon, $default_icons );
            return true;
        }
        if ( ! file_exists( realpath( $icon ) ) ) return false;
        $icon = str_replace('/', ':', realpath( $icon ) );
        $this->icon = ' with icon file "' . substr( $icon, 1, strlen( $icon ) - 1 ) . '"';
    }

    public function set_text( $text ) {
        $this->text = addslashes( $text ); // is the addslashes necessary?
    }

    public function set_buttons( $buttons, $default = '' ) {
        if ( empty( $buttons ) ) return false;
        $this->buttons = $buttons; // to use later if setting the default.
        if ( is_array( $buttons ) && ( count( $buttons) > 0 ) ) {
            $this->buttons_ = "buttons {";
            foreach( $buttons as $b ) :
                $this->buttons_ .= "\"{$b}\",";
            endforeach;
            $this->buttons_ = substr( $this->buttons_, 0, -1 ) . "}";
        } else if ( is_string( $buttons ) ) {
            $this->buttons_ = " buttons {\"{$buttons}\"}";
        }

        if ( ! empty( $default ) ) {
            $this->set_default_button( $default );
        }
    }

    public function set_default_button( $button ) {
        if ( $default = ( array_search( $button, $this->buttons ) + 1 ) ) {
            $this->buttons_ .= " default button {$default}";
            return true;
        }
        return false;
    }

    public function set_title( $title ) {
        $this->title = " with title \"{$title}\"";
    }

    public function set_default_answer( $text ) {
        $this->default_answer = " default answer \"{$text}\"";
    }

    public function set_timeout( $seconds ) {
        $this->timeout = " giving up after {$seconds}";
    }

    public function set_cancel( $cancel ) {
        $this->cancel = " cancel button \"{$cancel}\"";
    }

    public function set_hidden_answer( $hidden = false ) {
        if ( $hidden ) {
            $this->hidden_answer = " hidden answer true";
        }
    }

}


class Choose {

    private function create( $start, $options, $selections ) {
        if ( ! isset( $options ) || ! is_array( $options ) ) {
            return false;
        }
        $script = "osascript -e '{$start}";
        foreach( $options as $key => $value ) :
            $quotes = true;
            if ( isset( $selections[ $value ] ) ) {
                if ( is_array( $selections[ $value ] ) ) {
                    $selections[ $value ] = '{"' . implode( '", "', $selections[ $value ] ) . '"}';
                    $quotes = false;
                }
                if ( is_bool( $selections[ $value ] ) ) {
                    if ( $selections[ $value ] ) {
                        $selections[ $value ] = 'true';
                    } else {
                        $selections[ $value ] = 'false';
                    }
                }
                if ( $quotes ) {
                    $script .= " {$key} \"{$selections[ $value ]}\"";
                } else {
                    $script .= " {$key} {$selections[ $value ]}";
                }
            }
        endforeach;
        return $script .= "' 2>&1";
    }

    private function to_posix_path( $path ) {
        $path = str_replace( ':', '/', $path );
        $path = "/" . $path;
        return $path;
    }


    public function from_list( $list, $options = false ) {
        if ( ! is_array( $list ) ) {
            return false;
        }
        $list  = '{"' . implode( '", "', $list ) . '"}';
        $start = "choose from list {$list}";
        $default_options = [
            'with title'                  => 'title',
            'with prompt'                 => 'text',
            'default items'               => 'default',
            'OK button name'              => 'ok',
            'cancel button name'          => 'cancel',
            'multiple selections allowed' => 'multiple',
            'empty selection allowed'     => 'empty'
        ];
        $script = self::create( $start, $default_options, $options );

        if ( $script ) {
            return self::process( exec( $script ) );
        } else {
            return false;
        }
    }

    public function file( $options = false ) {
        $start = 'choose file';
        $default_options = [
            'with prompt'                 => 'text',
            'of type'                     => 'type',
            'default location'            => 'location',
            'invisibles'                  => 'invisibles',
            'multiple selections allowed' => 'multiple',
            'showing package contents'    => 'package_contents'
        ];
        $script = self::create( $start, $default_options, $options );

        if ( $script ) {
            return self::process( exec( $script ), 'alias ', true );
        } else {
            return false;
        }
    }

    public function filename( $options = false ) {
        $start = 'choose file name';
        $default_options = [
            'with prompt'      => 'text',
            'default name'     => 'default',
            'default location' => 'location'
        ];
        $script = self::create( $start, $default_options, $options );

        if ( $script ) {
            return self::process( exec( $script ), 'file ', true );
        } else {
            return false;
        }
    }

    public function folder( $options = false ) {
        $start = 'choose folder';
        $default_options = [
            'with prompt'                 => 'text',
            'default location'            => 'location',
            'invisibles'                  => 'invisibles',
            'multiple selections allowed' => 'multiple',
            'showing package contents'    => 'package_contents'
        ];
        $script = self::create( $start, $default_options, $options );

        if ( $script ) {
            return self::process( exec( $script ), 'alias ', true );
        } else {
            return false;
        }
    }

    private function process( $result, $strip = false, $path = false ) {
        // Make sure the user didn't cancel the selection
        if ( false !== strpos( $result, 'execution error: User canceled. (-128)' ) ) {
            return 'canceled';
        }
        if ( $strip ) {
            $result = str_replace( $strip, '', $result );
        }
        if ( false !== strpos( $result, ',' ) ) {
            $result = explode( ',', $result );
            // Just trim everything
            array_walk( $result, function( &$value, $key ) {
                $value = trim( $value );
            });
        } else {
            $result = [ $result ];
        }
        if ( $path ) {
            array_walk( $result, function( &$value, $key ) {
                $value = self::to_posix_path( $value );
            });
        }
        return $result;
    }

}


// Not written / not sure I'm going to write:
// ===========
// class ChooseApplication {
//     // this needs to be written
// }
// Note: "choose remote application" will not be included nor will "choose file name" as those
// use cases can be taken care of by the above and better php scripting.