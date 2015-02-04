<?php

namespace Alphred;

// Right now, this class just lets you create key-value configs either for a
// sqlite3 database or a json file.

/**
 *
 * @todo Make query methods uniform
 *
 */
class Config {

	public function __construct( $type = 'json' ) {
		$this->data   = Globals::get( 'alfred_workflow_data' );
		$this->bundle = Globals::get( 'alfred_workflow_bundleid' );
		if ( ! file_exists( $this->data ) ) { mkdir( $this->data, 0755 ); }

		// really, I need to throw an exception
		if ( ! in_array( $type, [ 'json', 'database', 'db' ] ) ) { $type = 'json'; }
		if ( 'json' == $type ) {
			if ( ! file_exists( "{$this->data}/config.json" ) ) {
				file_put_contents( "{$this->data}/config.json", json_encode( [] ) );
				$this->config = [];
			} else {
				$this->config = json_decode( file_get_contents( "{$this->data}/config.json" ), true );
			}
			$this->handler = 'json';
		} else if ( in_array( $type, [ 'db', 'database', 'sqlite', 'SQLite', 'SQLite3' ] ) ) {
			// Right now, we just support SQLite, but, if we expand database handlers, then we
			// can expand the possibilities here.
			$options = [];
			$this->db = new \SQLite3( "{$this->data}/config.sqlite3" );
			$this->init_db_table();
			$this->handler = 'db';
		}
	}

	public function set( $key, $value ) {
		if ( 'json' == $this->handler ) { return $this->set_json( $key, $value ); }
		else if ( 'db' == $this->handler ) { return $this->set_db( $key, $value ); }
		return false; // or raise an exeception
	}

	public function read( $key ) {
		if ( 'json' == $this->handler ) { return $this->read_json( $key ); }
		else if ( 'db' == $this->handler ) { return $this->read_db( $key ); }
		return false; // or raise an exeception
	}

	public function remove( $key ) {
		if ( 'json' == $this->handler ) { return $this->unset_json( $key ); }
		else if ( 'db' == $this->handler ) { return $this->unset_db( $key ); }
		return false; // or raise an exeception
	}

	private function unset_json( $key ) {
		if ( ! isset( $this->config[ $key ] ) ) { return false; }
		unset( $this->config[ $key ] );
		file_put_contents( "{$this->data}/config.json", json_encode( $this->config ) );
		return true;
	}

	private function unset_db( $key ) {
		$key = $this->db->escapeString( $key );
		$this->db->exec(
		  "DELETE FROM config WHERE key = '{$key}';"
		);
	}

	private function set_json( $key, $value ) {
		$this->config[ $key ] = $value;
		file_put_contents( "{$this->data}/config.json", json_encode( $this->config ) );
	}

	private function read_json( $key ) {
		if ( isset( $this->config[ $key ] ) ) { return $this->config[ $key ]; }
		return false; // or throw an exception
	}

	private function set_db( $key, $value, $overwrite = true ) {
		$key   = $this->db->escapeString( $key );
		$value = $this->db->escapeString( $value );
		if ( $overwrite ) {
			$this->db->exec( "DELETE FROM config WHERE key='{$key}';" );
		}
		$query = "INSERT OR REPLACE INTO config (key, value) values ('{$key}', '{$value}');";
		return $this->db->exec( $query );
	}

	private function read_db( $key ) {
		$query = $this->db->prepare( 'SELECT * FROM config WHERE key=:key' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$result = $query->execute();
		// the assumption is that there will be only one row
		$result = $result->fetchArray( SQLITE3_ASSOC );
		return $result['value'];
	}

	private function init_db_table() {
		$this->db->exec(
		  'CREATE TABLE IF NOT EXISTS config (key TEXT NOT NULL PRIMARY KEY, value TEXT) WITHOUT ROWID;'
		);
	}

}