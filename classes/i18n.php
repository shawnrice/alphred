<?php

namespace Alphred;

/*
 * For internationalization:
 *
 * gettext() isn't compiled into OS X's PHP installation, so I've created my own hacky version.
 *
 * (1) Create a directory called "i18n" in your workflow folder.
 * (2) For each language you want to use, create a file called ln.json (lowercase) where
 * "ln" is the two-letter language code: i.e. en = English, de = German, etc...
 * (3) In that json, make the original string as the key and the value as the translated test.
 *     i.e., a file called i18n/fr.json would contain:
 *     {
 *       "Hello": "Bonjour",
 *       "Do you speak French?": "Parlez-vous Français?",
 *       "I am a grapefruit": "Je suis un pamplemousse",
 *     }
 *
 * (4) Make sure you escape the string if necessary.
 *
 * Tip: if you'd rather have the json created for you, then just do something like...
 *         file_put_contents( 'i18n/fr.json', json_encode( [
 *             'string'  => 'translated',
 *             'string2' => 'second translation'
 *         ], JSON_PRETTY_PRINT );
 *
 *  Then just execute that PHP, and the json file will be created for you.
 *
**/

class i18n {

	public function __construct() {
		if ( ! file_exists( 'i18n' ) || ! is_dir( 'i18n' ) ) { return false; }
		if ( ! defined( 'ALPHRED_TESTING' ) || ( true !== ALPHRED_TESTING ) ) {
			$locale = exec( 'defaults read .GlobalPreferences AppleLanguages | tr -d [:space:] | cut -c2-3' );
		} else {
			$locale = 'fr';
		}
		if ( file_exists( "i18n/{$locale}.json" ) ) {
			$this->locale = $locale;
		} else {
			return false;
		}

		try {
			$this->dictionary = json_decode( file_get_contents( "i18n/{$locale}.json" ), true );
		} catch ( Exception $e ) {
			file_put_contents( 'php://stderr', "Error: locale '{$locale}.json' file is not valid json." );
			return false;
		}

		return $this;
	}

	public function translate( $string ) {
		if ( ! isset( $this->locale ) ) { return $string; }
		if ( isset( $this->dictionary[ $string ] ) ) {
			return $this->dictionary[ $string ];
		}
		return $string;
	}
}

// In the future, it would be pretty badass if I could give the option to do a background translation
// using Google Translate or something akin to that.

// I'd have to figure out how to cache it though...