<?php
/**
 * Contains i18n class for Alphred, providing basic translation functionality
 *
 * PHP version 5
 *
 * @package    Alphred
 * @copyright  Shawn Patrick Rice 2014
 * @license    http://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @author     Shawn Patrick Rice <rice@shawnrice.org>
 * @link       http://www.github.com/shawnrice/alphred
 * @link       http://shawnrice.github.io/alphred
 * @since      File available since Release 1.0.0
 *
 */

namespace Alphred;

/**
 * Translation library for Alphred
 *
 * Right now, this is sort of "proof-of-concept" and works well enough for static strings, but
 * it does need to be improved _vastly_.
 *
 * Right now, the best part about this library is that it will not break anything. So, if you
 * try to implement it and fuck it up, it'll just do nothing rather than break things.
 *
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

	/**
	 * Constructs the i18n object to use for translation, setting language
	 *
	 * Right now $engine is unused, but it's there to open for expansion for
	 * other translation methods that can be popped in or out. `Json` is just
	 * what is described above.
	 *
	 * @param string $engine translation utility; json is the only option
	 */
	public function __construct( $engine = 'json' ) {
		if ( ! file_exists( 'i18n' ) || ! is_dir( 'i18n' ) ) { return false; }
		// This is internal, for testing. If the "ALPHRED_TESTING" flag is set,
		// then we'll pretend that our language is French rather than the system
		// default (for me: English). Consider this testing code.
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

		// Try to load the translation "dictionary"
		try {
			$this->dictionary = json_decode( file_get_contents( "i18n/{$locale}.json" ), true );
		} catch ( Exception $e ) {
			// Well, the translation dictionary is not good JSON. So let's just log an error to the console
			// and pretend this never happened.
			file_put_contents( 'php://stderr', "Error: locale '{$locale}.json' file is not valid json." );
			return false;
		}

		return $this;
	}

	/**
	 * Translates a string from a dictionary
	 *
	 * @todo use "engines" instead of a single method
	 *
	 * @param  string $string a string to translate
	 * @return string         a, possibly, translated string
	 */
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

// For now, we'll leave this as is. In future releases, we'll create more engines that should work better
// than this.