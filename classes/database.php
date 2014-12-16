<?php

namespace Alphred\Database;

class Database {

    // This should be a database abstraction layer... right now, we only use SQLite3
    // So, it seems kind of pointless
    public function __construct( $type, $db, $options = [] ) {
        if ( ! in_array( $type, [ 'SQLite3' ] ) ) { $type = 'SQLite3'; }
        $type = "\Alphred\Database\\{$type}";
        $this->db = new $type( $db, $options );
        return $this->db;
    }

    // Theoretically, this should work out just fine if we add different types of databases
    public function __call( $method, $arguments ) {
        if ( count( $arguments ) > 1 ) {
            return $this->db->$method( $arguments );
        } else {
            return $this->db->$method( $arguments[0] );
        }
    }
}

class SQLite3 {

    public function __construct( $db, $options = [] ) {
        // TODO: add in options
        $this->db = new \SQLite3( $db );
        return $this->db;
    }

    public function exec( $query ) {
        return $this->db->exec( $query );
    }

    public function escapeString( $query ) {
        return $this->db->escapeString( $query );
    }

    public function prepare( $query ) {
        return $this->db->prepare( $query );
    }

    // What else needs to be in here? Right now, it covers _only_ the config use case

}

// We can add in support for other databases here later...