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
 * A data indexer class
 *
 * Feed objects of this class JSON objects, and it will automagically organize them
 * into searchable data. The data need not been uniform either.
 *
 * It is better to have a single data structure, however.
 *
 * @todo Add in proper exceptions
 * @todo Add in optional callbacks so you can track the progress -- partially done
 * @todo Reduce number of public functions
 * @todo Attempt to shorten file
 * @todo Write search classes (regular and cache)
 *
 */
class Index {

	// For testing purposes.
	public $index;
	public $data;

	/**
	 * [__construct description]
	 *
	 *
	 *  options:
	 *      update => true : updates the index to match the data db
	 *      progress_callback => string : name of function for progress bar
	 *
	 *
	 *
	 * @param [type] $data    [description]
	 * @param [type] $index   [description]
	 * @param [type] $options [description]
	 */
	public function __construct( $data, $index, $options = [] ) {

		$this->index      = new \SQLite3( $index );
		$this->data       = new \SQLite3( $data );

		$this->index->exec( 'PRAGMA foreign_keys = ON;' );
		$this->create_meta();
		$this->create_internal_data_table();

		if ( isset( $options['update'] ) && ( $options['update'] ) ) {
			if ( $this->check_for_update() ) {
				if ( isset( $options[ 'progress_callback' ] ) ) {
					$this->sync_index( $options[ 'progress_callback' ] );
				} else {
					$this->sync_index();
				}
			}
		}
	}

	/**
	 * Closes the connections to the data and index database files
	 */
	public function __destruct() {
		$this->data->close();
		$this->index->close();
	}


	/***************************************************************************
     * Meta Table Functions
     **************************************************************************/

	/**
	 * Creates a meta table that is simply key-value storage
	 *
	 * @return bool
	 */
	private function create_meta() {
		$schema = 'CREATE TABLE IF NOT EXISTS
					meta(
						key     TEXT        NOT NULL,
						value   TEXT        NOT NULL,
						time    INTEGER
					);';
		return $this->index->exec( $schema );
	}

	/**
	 * Sets a value in the meta table
	 *
	 * @param string    $key        key to be set
	 * @param mixed     $value      value of key
	 */
	public function set_meta( $key, $value, $force_overwrite = false ) {
		$key   = $this->index->escapeString( $key );
		$value = $this->index->escapeString( $value );
		if ( $force_overwrite ) {
			$this->delete_meta( $key );
		}
		return $this->index->exec(
			"INSERT OR REPLACE INTO meta(key, value) VALUES ('{$key}', '{$value}');"
		);
	}

	/**
	 * Retrieves a value from the meta table
	 *
	 * @param  string   $key    meta key
	 * @return mixed            the value of the key
	 */
	public function get_meta( $key ) {
		$key = $this->index->escapeString( $key );
		return $this->index->querySingle( "SELECT value from meta WHERE key='{$key}';" );
	}

	/**
	 * Deletes a record (or records) from meta table matching key
	 *
	 * @param  string   $key    the key to delete
	 * @return boolean          success or failure of query
	 */
	public function delete_meta( $key ) {
		$key = $this->index->escapeString( $key );
		return $this->index->exec( "DELETE FROM meta WHERE key='{$key}';" );
	}

	/**
	 * Logs update information to the meta table
	 *
	 * @param  mixed    $data_ids    the data id(s) of the object(s) changed
	 * @param  string   $type        either 'add' or 'delete'
	 * @return boolean or null
	 */
	private function log_update( $data_ids, $type ) {
		if ( ! in_array( $type, [ 'add', 'delete' ] ) ) {
			// Throw an exception really.
			return false;
		}
		if ( empty( $data_ids ) ) {
			// We need something here.
			return false;
		}

		// If the IDs are in an array, then join it with commas
		if ( is_array( $data_ids ) ) {
			$data_ids = implode( ',', $data_ids );
		}

		// Escape the string to be sure
		$data_ids = $this->index->escapeString( $data_ids );

		$now = time();
		// Run the query
		return $this->index->exec(
			"INSERT INTO meta('key', 'value', 'time') VALUES ('{$type}', '{$data_ids}', '{$now}');"
		);
	}

	/**
	 * Returns an array of all the updates in the meta table
	 *
	 * @param  mixed    $since    something that indicates a time
	 * @param  mixed    $type     either 'add' or 'delete' or false for both
	 * @return [type]         [description]
	 */
	public function read_updates( $since = 0, $type = false ) {

		// Wrangle "since" into a time
		if ( ! is_integer( $since ) ) {
			// We assume that integer is in Unix Epoch time, so everything else
			// is a string that needs to be converted to that.
			$since = date( 'U', strtotime( $since ) );
		}

		// We can select only adds and deletes
		if ( ! in_array( strtolower( $type ), [ 'add', 'delete' ] ) ) {
			// Well, you're trying to get something other than add or delete,
			// so we'll just give you everything instead (really, I should throw an exception).
			$type = false;
		}
		if ( false === $type ) {
			$results = $this->index->query( "SELECT * FROM meta WHERE time > {$since};" );
		} else {
			$type = strtolower( $type );
			$results = $this->index->query( "SELECT * FROM meta WHERE time > {$since} AND key='{$type}';" );
		}
		$updates = [];
		// Push them all into a multi-dimensional array
		while ( $result = $results->fetchArray( SQLITE3_ASSOC ) ) :
			$updates[] = $result;
		endwhile;

		return $updates;
	}


	/***************************************************************************
   * Internal Data Table Functions
   *
   * Note: we keep a copy of the data table in the index. This should
   * be a simple copy. The logic is that we can alter the actual data_db without
   * having to touch the index so that we can defer indexing until later, if necessary.
   **************************************************************************/


	/**
	 * Creates the internal data table
	 *
	 * @return bool
	 */
	private function create_internal_data_table() {
		$query = 'CREATE TABLE IF NOT EXISTS data (
						data_id     TEXT        PRIMARY KEY,
						time        INTEGER     NOT NULL,
						data        TEXT        NOT NULL
					  ) WITHOUT ROWID;';
		return $this->index->exec( $query );
	}


	/**
	 * Adds a record to the internal data table
	 *
	 * Note: the internal data table has two meaningful values:
	 *     (1) a unique identifier (data_id), and
	 *     (2) a json data string.
	 *
	 *      A timestamp is included as a third column.
	 *
	 *
	 * @param string    $data_id   the data_id that matches to the external database
	 * @param string    $data      a json string
	 */
	public function add_internal_data( $data_id, $data ) {
		if ( empty( $data ) || is_null( $data ) ) {
			echo "We have empty data for $data_id" . PHP_EOL;
			// We should probably log an exception.
			return false;
		}
		$query = 'INSERT OR REPLACE INTO data (data_id, time, data) VALUES (' .
		'\'' . $data_id . '\', ' .
		'\'' . time() . '\', ' .
		'\'' . $this->index->escapeString( json_encode( $data ) ) . '\');';
		// Returns true on success false on error. We can build in error-checking later
		$this->index->exec( $query );

		// If the record was added, then also index it
		if ( $this->index->changes() ) {
			$this->add_record( $data_id, $data );
		}
	}


	/**
	 * Gets a record from the internal data table
	 *
	 * @param  string   $data_id    the data_id of the data record
	 * @return mixed                returns false if the data is not found; otherwise returns data
	 */
	public function get_internal_data( $data_id ) {
		$data_id = $this->index->escapeString( $data_id );
		$result = $this->index->querySingle(
			"SELECT data from data WHERE data_id ='{$data_id}';", true
		);
		if ( 0 === count( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Deletes a record from the internal data table
	 *
	 * @param  string   $data_id    the data_id of the data record
	 */
	public function delete_internal_data( $data_id ) {
		$data_id = $this->index->escapeString( $data_id );

		$this->index->exec( "DELETE FROM data WHERE data_id ='{$data_id}';" );

		// If a row was actually deleted, then log it
		if ( $this->index->changes() ) {
			$this->log_update( $data_id, 'delete' );
		}
	}

	/**
	 * Performs a full copy of the external data_db
	 *
	 * Note: this is good to use when creating the DB for the first time if you want
	 * to index everything immediately; however, this function can be slow.
	 *
	 */
	private function copy_data_table() {
		$results = $this->data->query( 'SELECT * from data;' );
		while ( $row = $results->fetchArray() ) :
			$this->add_internal_data( $row['id'], $row['data'] );
		endwhile;
	}


	/***************************************************************************

     **************************************************************************/


	/**
	 * Checks if a word is reserved in SQLite
	 *
	 * @param  string  $word    word to be checked
	 * @return boolean
	 */
	private function is_reserved( $word ) {
		$reserved = [
			'abort', 'action', 'add', 'after', 'all', 'alter', 'analyze',
			'and', 'as', 'asc', 'attach', 'autoincrement', 'before', 'begin',
			'between', 'by', 'cascade', 'case', 'cast', 'check', 'collate',
			'column', 'commit', 'conflict', 'constraint', 'create', 'cross',
			'current_date', 'current_time', 'current_timestamp', 'database',
			'default', 'deferrable', 'deferred', 'delete', 'desc', 'detach',
			'distinct', 'drop', 'each', 'else', 'end', 'escape', 'except',
			'exclusive', 'exists', 'explain', 'fail', 'for', 'foreign',
			'from', 'full', 'glob', 'group', 'having', 'if', 'ignore',
			'immediate', 'in', 'index', 'indexed', 'initially', 'inner',
			'insert', 'instead', 'intersect', 'into', 'is', 'isnull', 'join',
			'key', 'left', 'like', 'limit', 'match', 'natural', 'no', 'not',
			'notnull', 'null', 'of', 'offset', 'on', 'or', 'order', 'outer',
			'plan', 'pragma', 'primary', 'query', 'raise', 'recursive',
			'references', 'regexp', 'reindex', 'release', 'rename', 'replace',
			'restrict', 'right', 'rollback', 'row', 'savepoint', 'select',
			'set', 'table', 'temp', 'temporary', 'then', 'to', 'transaction',
			'trigger', 'union', 'unique', 'update', 'using', 'vacuum',
			'values', 'view', 'virtual', 'when', 'where', 'with', 'without',
			'data', 'meta' // These two are used internally
		];
		if ( in_array( $word, $reserved ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Appends a string to a word if the word is RESERVED
	 *
	 * @param  string   $name   the word to check (usually a column name)
	 * @return string           a sanitized version of the column name
	 */
	private function sanitize_column_name( $name ) {
		if ( $this->is_reserved( $name ) ) {
			return "{$name}_AVOID_RESERVED";
		}
		return $name;
	}

	/***************************************************************************
   * Index Table Methods
   **************************************************************************/

	/**
	 * Parses a data object and creates the relevant tables from it
	 *
	 * @todo FIX THIS FUNCTION
	 * Right now, this function iterates over all data. Really, we should just feed it a data object
	 * and have it create those tables via that....
	 *
	 */
	private function create_index_tables( $data ) {
		$bools    = [];
		$lists    = [];
		$numbers  = [];
		$strings  = [];

		if ( ! is_array( $data ) ) {
			$data = json_decode( $data, true );
		}

		// This is a hack. Find out why this error could be thrown and
		// ensure that it isn't.
		if ( ! is_array( $data ) ) {
			file_put_contents( 'php://stderr', "ERROR: Ran into a non-array in index table creation: $data" );
			return;
		}

		// Cycle through all of the items in the data array to get their types;
		foreach ( $data as $key => $value ) :
			$type = gettype( $value );
			if ( 'array' == $type ) {
				$lists[] = $key;
			} else if ( 'bool' == $type ) {
				$bools[] = $key;
			} else if ( 'string' == $type ) {
				$strings[] = $key;
			} else if ( 'integer' == $type || 'double' == $type ) {
				// We treat integers and floats as the same
				$numbers[] = $key;
			}

			// We ignore null values because we don't actually know what their type is supposed to be, and
			// there is no reason to index null data.
		endforeach;

		// This shouldn't be necessary...
		$types = [ 'bools', 'lists', 'numbers', 'strings' ];
		foreach ( $types as $type ) :
			$$type = array_unique( $$type );
		endforeach;

		// Avoid SQL's reserved words
		foreach ( $types as $type ) :
			array_walk( $$type, function( &$value, $key ) {
				$value = $this->sanitize_column_name( $value );
			});
		endforeach;

		// These are used later to create and update the index data
		$this->lists    = $lists;
		$this->strings  = $strings;
		$this->bools    = $bools;
		$this->numbers  = $numbers;

		$extant_tables  = $this->get_tables( $this->index );

		foreach ( $lists as $table ) :
			if ( in_array( $table, $extant_tables ) ) { continue; }
			$this->create_table( $table, 'TEXT' );
		endforeach;
		foreach ( $strings as $table ) :
			if ( in_array( $table, $extant_tables ) ) { continue; }
			$this->create_table( $table, 'TEXT' );
		endforeach;
		foreach ( $bools as $table ) :
			if ( in_array( $table, $extant_tables ) ) { continue; }
			$this->create_table( $table, 'INTEGER' );
		endforeach;
		foreach ( $numbers as $table ) :
			if ( in_array( $table, $extant_tables ) ) { continue; }
			$this->create_table( $table, 'NUMERIC' );
		endforeach;

	}

	/**
	 * Creates a table structure for a field
	 *
	 * This also creates the FTS tables along with the triggers to keep the FTS tables up to date.
	 *
	 * @param  string   $table  name of the table
	 * @param  string   $type   SQLite data type for the stored data (TEXT, INTEGER, NUMERIC, etc...)
	 */
	private function create_table( $table, $type ) {
		// Upcase the type
		$type = strtoupper( $type );

		// Create the main table
		$this->index->exec(
			"CREATE TABLE IF NOT EXISTS {$table} (
                data_id     TEXT        NOT NULL,
                data        {$type}     NOT NULL,
                FOREIGN KEY (data_id) references data(data_id) ON DELETE CASCADE
            );"
		);

		// Create the FTS table using the main table's content
		$this->index->exec(
			"CREATE VIRTUAL TABLE IF NOT EXISTS {$table}_search USING fts4(
                content='{$table}', data
            );"
		);

		// Create a trigger to delete the corresponding record in the FTS table on UPDATE
		$this->index->exec(
			"CREATE TRIGGER IF NOT EXISTS {$table}_bu BEFORE UPDATE ON {$table}
            BEGIN
                DELETE FROM {$table}_search WHERE docid=old.rowid;
            END;"
		);

		// Create a trigger to delete the corresponding record in the FTS table on DELETE
		$this->index->exec(
			"CREATE TRIGGER IF NOT EXISTS {$table}_bd BEFORE DELETE ON {$table}
            BEGIN
                DELETE FROM {$table}_search WHERE docid=old.rowid;
            END;"
		);

		// Create a trigger to insert the new data into the FTS table on UPDATE
		$this->index->exec(
			"CREATE TRIGGER IF NOT EXISTS {$table}_au AFTER UPDATE ON {$table}
            BEGIN
                INSERT INTO {$table}_search(docid, data) VALUES(new.rowid, new.data);
            END;"
		);

		// Create a trigger to insert the new data into the FTS table on INSERT
		$this->index->exec(
			"CREATE TRIGGER IF NOT EXISTS {$table}_ai AFTER INSERT ON {$table}
            BEGIN
                INSERT INTO {$table}_search(docid, data) VALUES(new.rowid, new.data);
            END;"
		);
	}

	/**
	 * Adds a record to the index
	 *
	 * Note: Currently (and maybe in perpetuity), this ignores multi-dimensional
	 * arrays.
	 *
	 * @param [type] $data_id [description]
	 * @param [type] $data    [description]
	 */
	private function add_record( $data_id, $data ) {
		if ( ! is_array( $data ) ) {
			$data = json_decode( $data, true );
			// Add an exception if fails...
		}

		// Make sure that the data tables exist for the object
		$this->create_index_tables( $data );

		// Add the data to the index
		// Basically, what we're going to do here is to walk through the
		// data array and consider each key a table, and we're going to push
		// value to that table. For arrays, we'll push each value to the table;
		// however, we ignore multi-dimensional arrays.
		foreach ( $data as $table => $value ) :
			if ( ! is_array( $value ) ) {
				$this->insert( $table, $data_id, $value );
			} else {
				foreach ( $value as $v ) :
					// Exit if value is also an array
					if ( is_array( $v ) ) {
						continue; }
					$this->insert( $table, $data_id, $v );
				endforeach;
			}
		endforeach;

		// Log the update
		$this->log_update( $data_id, 'add' );
	}


	/**
	 * Inserts a record into a field table
	 *
	 * @param  string   $table      the field tablename
	 * @param  string   $data_id    the data_id
	 * @param  string   $data       a json string of data
	 * @return bool
	 */
	private function insert( $table, $data_id, $data ) {

		if ( empty( $data ) || is_null( $data ) ) {
			// Don't add empty data into the index
			return false;
		}

		$table   = $this->sanitize_column_name( $table );
		$data_id = $this->index->escapeString( $data_id );
		$data    = $this->index->escapeString( $data );
		return $this->index->exec(
			"INSERT OR REPLACE INTO {$table} (data_id, data) VALUES ( '{$data_id}', '{$data}' );"
		);
	}


	/**
	 * Removes escaping characters from a JSON string stored in the database
	 *
	 * @param  string   $data   the string to be unescaped
	 * @return string           a valid JSON string
	 */
	private function unescape_data( $data ) {
		// Remove the slashes and teh first and second quote marks around the JSON string
		return substr( stripslashes( $data ), 1, -1 );
	}


	/**
	 * Gets a list of tables in a SQLite3 database
	 *
	 * @param  SQLite3 object   $db     An already instantiated SQLite3 object
	 * @return array                    An array of tablenames
	 */
	private function get_tables( $db ) {
		$results = $db->query( 'PRAGMA stats;' );
		$tables = [];
		while ( $row = $results->fetchArray() ) :
			if ( 'sqlite_master' === $row['table'] ) {
				continue; }
			$tables[] = $row['table'];
		endwhile;
		$this->fields = array_unique( $tables );
		return $this->fields;
	}


	/**
	 * Forces a sync between the data db and the index
	 *
	 *
	 */
	public function sync_index( $callback = false ) {
		$this->delete_extra_records();
		$this->add_missing_records( 0, $callback );
	}


	/**
	 * Deletes and re-adds a record from the data db
	 *
	 * @param  string   $id     unique identifier of the record
	 */
	public function reindex_record( $id ) {
		$this->delete_internal_record( $record );
		$data = $this->data->get( $record );
		if ( ! $data ) {
			$this->add_internal_data( $this->index->escapeString( $record ), $data );
		}
	}


	/**
	 * Deletes records that are in the index but not in the data db
	 *
	 */
	public function delete_extra_records() {
		foreach ( $this->find_extra_records() as $delete ) :
			$this->delete_internal_data( $delete );
		endforeach;
	}

	/**
	 * Adds all records from the data db that are not in the index
	 *
	 * @todo Implement callbacks
	 *
	 * @param  integer  $number     the number of records to index
	 *                              (use 0 to index all)
	 * @param  function $callback   name of function to use as callback for
	 *                              progress updates
	 */
	public function add_missing_records( $number = 0, $callback = false  ) {
		if ( ! is_integer( $number ) ) {
			// throw an exception.
			return false;
		}
		if ( 0 === $number ) {
			$number = count( $this->find_missing_records() );
		}
		$progress = 0;
		foreach ( $this->find_missing_records() as $add ) :
			$record = $this->data->querySingle(
				"SELECT data from data WHERE id='{$add}';"
			);
			if ( $record ) {
				$this->add_internal_data( $this->index->escapeString( $add ), $record );
			}
			$progress++;

			// Callback
			if ( is_callable( $callback ) ) {
				call_user_func_array( $callback, [ $progress, $number ] );
			}
		endforeach;
	}

	/**
	 * Returns a list of records that are in the index but not in the data
	 *
	 * @param  boolean $force   forces a regeneration of the data if true
	 * @return array            an array of data ids
	 */
	private function find_extra_records( $force = false ) {
		// If the value has already been set, then let's not
		// for the expensive operation unless asked.
		if ( false === $force ) {
			if ( isset( $this->extra_records ) ) {
				return $this->extra_records;
			}
		}
		$this->extra_records = array_diff(
			$this->find_all_index_records(), $this->find_all_data_records()
		);
		return $this->extra_records;
	}

	/**
	 * Returns a list of records that are in the data db but not indexed
	 *
	 * @param  boolean $force   forces a regeneration of the data if true
	 * @return array            an array of data ids
	 */
	private function find_missing_records( $force = false ) {
		// If the value has already been set, then let's not
		// for the expensive operation unless asked.
		if ( false === $force ) {
			if ( isset( $this->missing_records ) ) {
				return $this->missing_records;
			}
		}
		$this->missing_records = array_diff(
			$this->find_all_data_records(), $this->find_all_index_records()
		);
		return $this->missing_records;
	}

	/**
	 * Returns the ids of all records in the data db
	 *
	 * @param  boolean $force   forces a regeneration of the data if true
	 * @return array            an array of data ids
	 */
	private function find_all_data_records( $force = false ) {
		return $this->find_all_records( 'data', $force );
	}

	/**
	 * Returns the ids of all the records in the index
	 *
	 * @param  boolean $force   forces a regeneration of the data if true
	 * @return array            an array of data ids
	 */
	private function find_all_index_records( $force = false ) {
		return $this->find_all_records( 'index', $force );
	}


	/**
	 * A generic method to do the work of "find_all_index_records" and the same for data
	 * @param  string 	$type   either 'data' or 'index'
	 * @param  bool 		$force 	whether or not to force new data
	 * @return array        		an array of data ids
	 */
	private function find_all_records( $type, $force ) {

		// This functions works with only 'index' or 'data'
		if ( 'data' == $type ) {
			$row_name = 'id';
		} else if ( 'index' == $type ) {
			$row_name = 'data_id';
		} else {
			// or throw an exception
			return false;
		}

		$type_records = "{$type}_records";

		// If the value has already been set, then let's not
		// for the expensive operation unless asked.
		if ( false === $force ) {
			if ( isset( $this->$type_records ) ) {
				return $this->$type_records;
			}
		}

		// Query the data db for all their record ids
		$data = $this->$type->query( "SELECT {$row_name} FROM data;" );
		$records = [];

		// Get all the data IDs
		while ( $row = $data->fetchArray( SQLITE3_ASSOC ) ) :
			$records[] = $row[ $row_name ];
		endwhile;

		// Set the records and return them
		return $this->$type_records = $records;

	}

	/**
	 * Checks if the index table needs updating
	 *
	 * @return boolean  whether or not the index needs updating
	 */
	private function check_for_update() {
		$data_update = $this->data->querySingle( "SELECT value from meta WHERE key='update';" );
		if (  $data_update > $this->get_last_update() ) {
			return true;
		}
		return false;
	}

	/**
	 * Gets the last updated time of the data table
	 *
	 * @return integer  the last update time as seconds since the Unix Epoch
	 */
	public function get_last_update() {
		return $this->index->querySingle(
			"SELECT time FROM meta WHERE key='add' OR key='delete' LIMIT 1;"
		);
	}

	/**
	 * Removes all add/delete log notices from the meta table
	 *
	 */
	public function flush_update_log() {
		$this->index->exec( 'DELETE FROM meta WHERE key="add" OR key="delete"' );
	}


}