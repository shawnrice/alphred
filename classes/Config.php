<?php
/**
 * Contains Config class for Alphred
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
 * A simple class to manage configuration for workflows
 *
 * Currently, there are three handlers that are available: `json`, `sqlite`, and
 * `ini`. These correspond to their obvious data storage types. To use, do something
 * simple like:
 * ````php
 * $config = new Alphred\Config( 'ini' );
 * $config->set( 'username', 'shawn patrick rice' );
 * ````
 * To get it later, just use:
 * ````php
 * $username = $config->read( 'username' );
 * ````
 *
 * You can store arrays and more complex data with the `json` and `ini` handlers.
 * Currently, the SQLite3 handler is a bit primitive.
 *
 */
class Config {

	/**
	 * A list of valid handlers and their file extensions
	 *
	 * Current options are `json`, `sqlite`, and `ini`.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $handlers = [
		// as file_extension => handler_name
		'json' 		=> 'json',
		'sqlite3' => 'sqlite',
		'ini' 		=> 'ini',
	];

	/**
	 * Constructs the Config object
	 *
	 * @since 1.0.0
	 * @todo Make this pluggable (to load custom handlers)
	 *
	 * @param string $handler  the name of the handler
	 * @param string $filename the basename of the config file
	 */
	public function __construct( $handler, $filename = 'config' ) {

		// Do a quick check to make sure that we're running in a workflow environment
		// because we need access to certain variables for this to work
		if ( ! Globals::bundle() ) {
			throw new RunningOutsideOfAlfred( "Cannot use Alphred's Config outside of the workflow environment", 4 );
		}
		// Make sure that the data directory has been created
		self::create_data_directory();

		if ( ! in_array( $handler, $this->handlers ) ) {
			/**
			 * @todo Redo the exception
			 */
			throw new Exception( "Unknown config handler: {$handler}", 4 );
		}

		// Set the handler
		$this->handler = $handler;
		// Construct the filename
		$this->filename = $filename . '.' . array_search( $handler, $this->handlers );
		// Load the handler
		$this->load_handler( $this->handler );

	}


	/**
	 * Creates the data directory
	 *
	 * @throws \Alphred\RunningOutsideOfAlfred
	 *
	 * @return bool Whether or not the directory was created or exists
	 */
	private function create_data_directory() {
		// Get the data directory from the Globals array
		if ( $dir = Globals::data() ) {
			// If the directory does not exist, then make it
			if ( ! file_exists( $dir ) ) {
				// Debug-level log message
				\Alphred\Log::log( "Creating data directory.", 0, 'debug' );

				// Make the directory
				return mkdir( $dir, 0775, true );
			} else {
				// Directory exists, so return true
				return true;
			}
		}
		// The workflow_data
		throw new RunningOutsideOfAlfred( "Cannot use Alphred's Config outside of the workflow environment", 4 );
	}

	/**
	 * Gets the path for the config file
	 *
	 * @since 1.0.0
	 *
	 * @return string 		path to config file
	 */
	private function get_config_file() {
		return Globals::data() . '/' . $this->filename;
	}

	/*****************************************************************************
	 * Generic Handler Wrapper Functions
	 ****************************************************************************/

	/**
	 * Loads a handler
	 *
	 * @since 1.0.0
	 *
	 * @param  string $handler the name of the handler
	 * @return bool            success of loading the handler
	 */
	private function load_handler( $handler ) {
		$method = 'load_' . $this->handler;
		if ( ! method_exists( $this, $method ) ) {
			/**
			 * @todo Redo exception
			 */
			throw new Exception( "Method does not exist", 4 );
		}
		\Alphred\Log::console( 'Loading config file `' . $this->get_config_file() . '`', 0 );
		return $this->$method();
	}

	/**
	 * Sets a value in the config
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key    the key to set
	 * @param  mixed  $value  the value to set
	 * @return bool 					success as true/false
	 */
	public function set( $key, $value ) {
		$method = 'set_' . $this->handler;
		if ( ! method_exists( $this, $method ) ) {
			/**
			 * @todo Redo exception
			 */
			throw new Exception( "Method does not exist", 4 );
		}
		return $this->$method( $key, $value );
	}

	/**
	 * Reads a value from the config
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key   the key to read
	 * @return mixed         the values for the key
	 */
	public function read( $key ) {
		$method = 'read_' . $this->handler;
		if ( ! method_exists( $this, $method ) ) {
			/**
			 * @todo Redo exception
			 */
			throw new Exception( "Method does not exist", 4 );
		}
		return $this->$method( $key );
	}

	/**
	 * Unsets a value from the config
	 *
	 * Note: I would name this `unset`, but that's a reserved function name.
	 *
	 * @since 1.0.0
	 *
	 * @param  string 	$key 	the name of the key
	 * @return boolean
	 */
	public function delete( $key ) {
		$method = 'unset_' . $this->handler;
		if ( ! method_exists( $this, $method ) ) {
			/**
			 * @todo Redo exception
			 */
			throw new Exception( "Method does not exist", 4 );
		}
		return $this->$method( $key, $value );
	}

	/*****************************************************************************
	 * JSON Handler
	 ****************************************************************************/

	/**
	 * Loads the json handler
	 *
	 * @since 1.0.0
	 *
	 * @return boolean success if handler was loaded
	 */
	private function load_json() {
		if ( ! file_exists( $this->get_config_file() ) ) {
			file_put_contents( $this->get_config_file(), [] );
		}
		$this->config = json_decode( file_get_contents( $this->get_config_file() ), true );
		return true;
	}

	/**
	 * Sets a config value
	 *
	 * @since 1.0.0
	 *
	 * @param string $key    the name of the key
	 * @param mixed  $value  the value of the key
	 * @return boolean success
	 */
	private function set_json( $key, $value ) {
		// Reload the json file
		$this->load_json();
		$this->config[ $key ] = $value;
		return file_put_contents( $this->get_config_file(), json_encode( $this->config ) );
	}

	/**
	 * Reads a config value
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key the key to read
	 * @return mixed       the value of the key
	 */
	private function read_json( $key ) {
		if ( isset( $this->config[ $key ] ) ) {
			return $this->config[ $key ];
		}
		throw new ConfigKeyNotSet( "`{$key}` is not set.", 1 );
	}

	/**
	 * Unsets a config value
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key the key to unset
	 * @return boolean     true if the key existed; false if the key did not exist
	 */
	private function unset_json( $key ) {
		if ( ! isset( $this->config[ $key ] ) ) {
			return false;
		}
		unset( $this->config[ $key ] );
		file_put_contents( $this->get_config_file(), json_encode( $this->config ) );
		return true;
	}

	/*****************************************************************************
	 * SQLite3 Handler
	 ****************************************************************************/

	/**
	 * Loads the SQLite3 handler
	 *
	 * @since 1.0.0
	 *
	 * @return boolean true on success
	 */
	private function load_sqlite() {
		$this->db = new \SQLite3( $this->get_config_file() );
		$this->init_db_table();
		return true;
	}

	/**
	 * Creates the database table
	 *
	 * @return boolean true on success
	 */
	private function init_db_table() {
		return $this->db->exec(
		  'CREATE TABLE IF NOT EXISTS config (key TEXT NOT NULL PRIMARY KEY, value TEXT) WITHOUT ROWID;'
		);
	}

	/**
	 * Sets a config value
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key   the name of the key
	 * @param  mixed  $value the value to set
	 * @return boolean success
	 */
	private function set_sqlite( $key, $value ) {
		$key   = $this->db->escapeString( $key );
		$value = $this->db->escapeString( $value );
		if ( $overwrite ) {
			$this->db->exec( "DELETE FROM config WHERE key='{$key}';" );
		}
		$query = "INSERT OR REPLACE INTO config (key, value) values ('{$key}', '{$value}');";
		return $this->db->exec( $query );
	}

	/**
	 * Reads a config value
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key the key to read
	 * @return mixed       the value of the key
	 */
	private function read_sqlite( $key ) {
		$query = $this->db->prepare( 'SELECT * FROM config WHERE key=:key' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$result = $query->execute();
		// the assumption is that there will be only one row
		$result = $result->fetchArray( SQLITE3_ASSOC );
		if ( isset( $result[ 'value' ] ) ) {
			return $result['value'];
		}
		throw new ConfigKeyNotSet( "`{$key}` is not set.", 1 );
	}

	/**
	 * Deletes a value from the config
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key  the key to delete
	 * @return boolean      success
	 */
	private function unset_sqlite( $key ) {
		$key = $this->db->escapeString( $key );
		return $this->db->exec( "DELETE FROM config WHERE key = '{$key}';" );
	}

	/*****************************************************************************
	 * Ini Handler
	 ****************************************************************************/

	/**
	 * Loads the ini handler
	 *
	 * @since 1.0.0
	 * @see Ini INI functionality
	 *
	 * @return bool 	true on success
	 */
	private function load_ini() {
		// Check if the file exists
		if ( ! file_exists( $this->get_config_file() ) ) {
			// Create an empty config file since none exists
			Ini::write_ini( [], $this->get_config_file() );
		}
		// Load the config
		$this->config = Ini::read_ini( $this->get_config_file() );
		return true;
	}

	/**
	 * Sets a config value
	 *
	 * @since 1.0.0
	 * @see Ini INI functionality
	 *
	 * @param string $key   the key to set
	 * @param mixed $value the value to set
	 */
	private function set_ini( $key, $value ) {
		// Re-initialize the config variable
		$this->load_ini();
		$this->config[ $key ] = $value;
		// Write the INI file
		return Ini::write_ini( $this->config, $this->get_config_file() );
	}

	/**
	 * Reads a value from a config file
	 *
	 * @since 1.0.0
	 * @see Ini INI functionality
	 * @throws \Alphred\ConfigKeyNotSet when the key is not set
	 *
	 * @param  string $key the key to read
	 * @return mixed       the value of the key
	 */
	private function read_ini( $key ) {
		// Re-initialize the config variable
		$this->config = Ini::read_ini( $this->get_config_file() );
		if ( isset( $this->config[ $key ] ) ) {
			return $this->config[ $key ];
		}
		// If the config key is not sent, throw an exception rather than returning false
		throw new ConfigKeyNotSet( "`{$key}` is not set.", 1 );
	}

	/**
	 * Unsets a config
	 *
	 * @since 1.0.0
	 * @see Ini INI functionality
	 *
	 * @param  string $key the key to unset
	 * @return boolean     true if the key existed; false if the key did not exist
	 */
	private function unset_ini( $key ) {
		// Re-initialize the config variable
		$this->config = Ini::read_ini( $this->get_config_file() );
		if ( isset( $this->config[ $key ] ) ) {
			unset( $this->config[ $key ] );
			Ini::write_ini( $this->config, $this->get_config_file() );
			return true;
		}
		return false;
	}

}


