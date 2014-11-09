<?php

namespace Alphred\Config;

// Right now, this class just lets you create key-value configs either for a
// sqlite3 database or a json file.

class Config {

    public function __construct( $type = 'json' ) {
        $this->data   = $_SERVER['alfred_workflow_data'];
        $this->bundle = $_SERVER['alfred_workflow_bundleid'];
        if ( ! file_exists( $this->data ) ) { mkdir( $this->data, 0755 ); }

        // really, I need to throw an exception
        if ( ! in_array( $type, [ 'json', 'database', 'db' ] ) ) { $type = 'json'; }
        if ( $type == 'json' ) {
            if ( ! file_exists( "{$this->data}/config.json" ) ) {
                file_put_contents( "{$this->data}/config.json", json_encode([]) );
                $this->config = json_decode('');
            } else {
                $this->config = json_decode( file_get_contents( "{$this->data}/config.json" ) );
            }
            $this->handler = 'json';
        } else if ( in_array( $type, [ 'db', 'database', 'sqlite', 'SQLite' ] ) ) {
            require_once( __DIR__ . '/database.php' );
            // Right now, we just support SQLite, but, if we expand database handlers, then we
            // can expand the possibilities here.
            $options = [];
            $this->db = new \Alphred\Database\Database( 'SQLite', "{$this->data}/config.sqlite3", $options );
            $this->init_db_table();
            $this->handler = 'db';
        }
    }

    public function set( $key, $value ) {
        $value = json_encode( $value );
        if ( $this->handler == 'json' ) { return $this->set_json( $key, $value ); }
        else if ( $this->handler == 'db' ) { return $this->set_db( $key, $value ); }
        return false; // or raise an exeception
    }

    public function read( $key ) {
        if ( $this->handler == 'json' ) { return $this->read_json( $key ); }
        else if ( $this->handler == 'db' ) { return $this->read_db( $key ); }
        return false; // or raise an exeception
    }

    public function remove( $key ) {
        if ( $this->handler == 'json' ) { return $this->unset_json( $key ); }
        else if ( $this->handler == 'db' ) { return $this->unset_db( $key ); }
        return false; // or raise an exeception
    }

    private function unset_json( $key ) {
        if ( ! isset( $this->config->$key ) ) { return; }

        unset( $this->config->$key );
        file_put_contents( "{$this->data}/config.json", json_encode( $this->config ) );

    }

    private function unset_db( $key ) {
        $query = "DELETE FROM config WHERE key = '{$key}';";
        $this->db->exec( $query );
    }

    private function set_json( $key, $value ) {
        $this->config->$key = $value;
        file_put_contents( "{$this->data}/config.json", json_encode( $this->config ) );
    }

    private function read_json( $key ) {
        if ( isset( $this->config->$key ) ) { return json_decode( $this->config->$key ); }
        return false; // or throw an exception
    }

    private function set_db( $key, $value ) {
        $query = "INSERT or REPLACE INTO config (key, value) values (\"{$key}\", {$value});";
        $query = $this->db->escapeString( $query );
        return $this->db->exec( $query );
    }

    private function read_db( $key ) {
        $query = $this->db->prepare("SELECT * FROM config WHERE key=:key");
        $query->bindValue( ':key', $key, SQLITE3_TEXT );
        $result = $query->execute();
        // the assumption is that there will be only one row
        $result = $result->fetchArray(SQLITE3_ASSOC);
        return $result['value'];
    }

    private function init_db_table() {
        $sql = "CREATE TABLE IF NOT EXISTS config (key TEXT NOT NULL PRIMARY KEY, value TEXT) WITHOUT ROWID;";
        $this->db->exec( $sql );
    }

}