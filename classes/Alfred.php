<?php
/**
 *
 * @package Alphred
 *
 *
 */


// Right now, some of this code should just be in alphred.php... we'll see.

namespace Alphred;

/**
 *
 * What is the purpose of this class? Is there any real good purpose?
 *
 */
class Alfred {

	public function __construct( $options = [ 'create_directories' => false ] ) {
		if ( true === $options['create_directories'] ) {
			$this->create_directories();
		}
	}

	private function create_directories() {
		if ( ! self::data() ) {
			return false;
		}
		if ( ! file_exists( self::data() ) ) {
			mkdir( self::data(), 0775, true );
		}
		if ( ! file_exists( self::cache() ) ) {
			mkdir( self::cache(), 0775, true );
		}

		return true;
	}

	public function user() {
		return Globals::get( 'USER' );
	}

	public function bundle() {
		return Globals::get( 'alfred_workflow_bundleid' );
	}

	public function data() {
		return Globals::get( 'alfred_workflow_data' );
	}

	public function cache() {
		return Globals::get( 'alfred_workflow_cache' );
	}

	public function uid() {
		return Globals::get( 'alfred_workflow_uid' );
	}

	public function workflow_name() {
		return Globals::get( 'alfred_workflow_name' );
	}

	public function theme_subtext() {
		return Globals::get( 'alfred_theme_subtext' );
	}

	public function alfred_version() {
		return Globals::get( 'alfred_version' );
	}

	public function alfred_build() {
		return Globals::get( 'alfred_version_build' );
	}

	public function dir() {
		return Globals::get( 'PWD' );
	}

	public function theme_background() {
		return Globals::get( 'alfred_theme_background' );
	}

  public function light_or_dark() {
    // Regex pattern to parse the Alfred background variable
    $pattern = "/rgba\(([0-9]{1,3}),([0-9]{1,3}),([0-9]{1,3}),([0-9.]{4,})\)/";
    // Do the regex, matching everything in the $matches variable
    preg_match_all( $pattern, Globals::get( 'alfred_theme_background' ), $matches );
    // Pull the values into an $rgb array
    $rgb = array( 'r' => $matches[1][0], 'g' => $matches[2][0], 'b' => $matches[3][0] );

    // This calculates the luminance. Values are between 0 and 1.
    $luminance = ( 0.299 * $rgb[ 'r' ] + 0.587 * $rgb[ 'g' ] + 0.114 * $rgb[ 'b' ] ) / 255;

    if ( 0.5 < $luminance ) {
        return 'light';
    }
    return 'dark';
  }

}





