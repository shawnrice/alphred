<?php

namespace Alphred;

class Notification {

/**
 * The available, built-in sounds that you can use
 * @var array
 */
private static $sounds = [
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




	/**
	 * Sends a system notification
	 *
	 * The notification will always have the script editor icon on it.
	 * Use CocoaDialog for better notifications.
	 *
	 * @param  string|array $options [description]
	 * @return boolean          [description]
	 */
	public function notify( $options ) {
		if ( is_string( $options ) ) {
			exec( "osascript -e 'display notification \"{$options}\"'" );
			return true;
		}
		if ( ! isset( $options['text'] ) ) {
			// throw exception
			return false;
		}

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
				if ( in_array( $option, self::$sounds ) ) {
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