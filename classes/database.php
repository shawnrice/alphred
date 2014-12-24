<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * PHP version 5
 *
 * @copyright  Shawn Patrick Rice 2014
 * @license    http://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @author     Shawn Patrick Rice <rice@shawnrice.org>
 * @link       http://www.github.com/shawnrice/alphred
 * @link       http://shawnrice.github.io/alphred
 * @since      File available since Release 1.0.0
 *
 */


namespace Alphred\Database;

/**
 * A Data Indexer Class
 *
 *
 * @see Index
 */
class Data {


	/**
	 * An internal SQLite3 data object, representing the data database
	 *
	 * @var \SQLite3 Object
	 */
	private $db;



	/**
	 * Constructs object
	 *
	 * @param string $database File path to SQLite3 data db
	 */
	public function __construct( $database ) {
		$this->db = new \SQLite3( $database );
		$this->create_table();
		$this->create_meta();
	}

	/**
	 * Closes data base connection
	 *
	 */
	public function __destruct() {
		$this->db->close();
	}

	/**
	 * Creates the data table
	 *
	 * Note: this library is meant to store JSON strings as data. So,
	 * the data table is simply a key-val storage that the index can
	 * then read. You can use this independent of the Index class, but
	 * if you are using the Index class, then make sure you feed this
	 * JSON.
	 *
	 * @return boolean      success of sqlite query
	 */
	private function create_table() {
		$schema = 'CREATE TABLE IF NOT EXISTS
					data(
						id TEXT PRIMARY KEY,
						time INTEGER NOT NULL,
						data TEXT NOT NULL
					);';
		return $this->db->exec( $schema );
	}

	/**
	 * Creates the internal meta table
	 *
	 * @return boolean      success of sqlite query
	 */
	private function create_meta() {
		$schema = 'CREATE TABLE IF NOT EXISTS
					meta(
						key TEXT PRIMARY KEY,
						value TEXT NOT NULL
					);';
		return $this->db->exec( $schema );
	}

	/**
	 * Adds a record to the data table
	 *
	 * Note: if you do not provide a unique identifier for the record, then
	 * one will be generated as a hash of the record, but it will be harder
	 * to curate the data table without one.
	 *
	 * @param string    $record     ideally, a string of JSON
	 * @param string    $unique     optional: the unique identifier for the record
	 * @param boolean   $overwrite  optional: whether or not to overwrite a record
	 */
	public function add( $record, $unique = false, $overwrite = false ) {
		if ( false === $unique ) {
			$unique = md5( $record );
		}
		if ( $overwrite ) {
			// We're going to overwrite, but, in case we don't have a good identifier, then
			// we'll just delete the field first rather than using an INSERT OR REPLACE query
			$this->db->exec(
				'DELETE FROM data WHERE id=\'' . $this->db->escapeString( $unique ) . '\';', true
			);

		} else if ( $result = $this->get( $unique ) ) {
			// Since we're not
			return $result;
		}
		$this->set_meta( 'update', time() );
		$query = 'INSERT INTO data (id, time, data) VALUES (' .
				 '\'' . $this->db->escapeString( $unique ) . '\', ' .
				 '\'' . time() . '\', ' .
				 '\'' . $this->db->escapeString( json_encode( $record ) ) . '\');';

		// Returns true on success false on error. We can build in error-checking later
		return $this->db->exec( $query );
	}

	/**
	 * Get a specific record from the data table
	 *
	 * @param  string   $unique     the unique identifier of the record
	 * @return string               a string (probably JSON) that has the data
	 */
	public function get( $unique ) {
		$unique = $this->db->escapeString( $unique );
		$result = $this->db->querySingle(
			"SELECT data from data WHERE id='{$unique}';", true
		);
		if ( 0 === count( $result ) ) {
			return false;
		}
		return $result;
	}

	/**
	 * Deletes a record from the data db
	 *
	 * @param  string   $unique     the unique identifier for a data record
	 * @return boolean              returns false on failure
	 */
	public function delete( $unique ) {
		if ( empty( $unique ) ) {
			return false;
		}
		$unique = $this->db->escapeString( $unique );
		$this->db->exec( "DELETE FROM data WHERE id='{$unique}';" );

		// If a record was actually deleted, then update the last updated
		// time on the meta table
		if ( $this->db->changes() ) {
			$this->set_meta( 'update', time() );
		}
	}

	/**
	 * Set the value of a key on the meta table
	 *
	 * @param string $key name of the key
	 * @param mixed  $value  the value of the key
	 */
	public function set_meta( $key, $value ) {
		$key   = $this->db->escapeString( $key );
		$value = $this->db->escapeString( $value );
		return $this->db->exec(
			"INSERT OR REPLACE INTO meta(key, value) VALUES ('{$key}', '{$value}');"
		);
	}

	/**
	 * Get the value of a key from the meta table
	 *
	 * @todo Add in a different return value for when the key does not exist
	 *
	 * @param  string $key    name of the key
	 * @return mixed            value of the key
	 */
	public function get_meta( $key ) {
		$key = $this->db->escapeString( $key );
		$value = $this->db->querySingle(
			"SELECT value from meta WHERE key='{$key}';"
		);
		return $value;
	}

}








