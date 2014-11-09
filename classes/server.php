<?php

namespace Alphred;

class Server {

    public function __construct() {
        $this->port = '9947';
        $this->dir  = basename( $_SERVER['PWD'] );
        if ( PHP_MINOR_VERSION < 4 ) {
            // here we throw an exception
            return false;
        }
    }

    private function bootstrap( $options = [] ) {
        $this->base_path = "/tmp/alphred";
        if ( ! file_exists( $this->base_path ) ) { mkdir( $this->base_path, 0755 ); }
        $this->keep_alive = $this->base_path . "/zombie";
        $this->kill_script = $this->base_path . "/kill_script.sh";

    }

    private function update_zombie() {
        file_put_contents( $this->keep_alive, time() );
    }

    private function check_server() {
        $cmd = "ps aux | grep 'php -S localhost:{$this->port}' | grep -v grep | awk '{print \$2}'";;
        $pid = exec( $cmd );
        return ( $pid ) ? $pid : false;
    }

    public function start_server() {
        $this->bootstrap();
        if ( ! $this->check_server() ) {
            $cmd = "nohup php -S localhost:{$this->port} -t '" . dirname( $_SERVER['PWD'] ) . "' >/dev/null 2>&1 &";
            exec( $cmd );
            $this->do_kill_script();
        }
        $this->update_zombie();
    }

    public function get( $path, $vars = [] ) {
        if ( count( $vars ) > 0 ) {
            $fields = '?';
            foreach( $vars as $k => $v ) :
                $fields .= "{$k}={$v}&";
            endforeach;
            $fields = substr( $fields, 0, -1 );
            $path = $path . $fields;
        }
        $this->request( $path );
        $result = curl_exec( $this->c );
        // This needs error handlings
        // curl_error(ch)
        curl_close( $this->c );
        return $result;
    }

    public function post( $path, $vars = [] ) {
        $this->request( $path );
        curl_setopt( $this->c, CURLOPT_POST, 1 );
        if ( count( $vars ) > 0 ) {
            curl_setopt( $this->c, CURLOPT_POSTFIELDS, $vars );
        }
        $result = curl_exec( $this->c );
        // This needs error handlings
        // curl_error(ch)
        curl_close( $this->c );
        return $result;
    }

    public function request( $path ) {
        $this->c = curl_init();
        $this->url = "http://localhost:{$this->port}/{$this->dir}/{$path}";
        curl_setopt_array( $this->c, array(
            CURLOPT_URL => $this->url,
            CURLOPT_PORT => $this->port,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FAILONERROR => 1,
            CURLOPT_FOLLOWLOCATION => 1,
        ));
        $this->update_zombie();
    }

    private function do_kill_script() {
        $pid = $this->check_server();
        if ( ! $pid ) return false; // or throw an exception

        $script = '#!/bin/bash
PHP_PID=' . $pid . '
sleep 20
die=0
while [[ $die -eq 0 ]]; do
  updated=$(cat "' . $this->keep_alive . '")
  now=$(date +%s)
  updated=$(( $updated + 40 ))
  [[ $now -gt $updated ]] && die=1
  sleep 20
done
kill $PHP_PID
[[ -f "' . $this->keep_alive . '" ]] && rm "' . $this->keep_alive . '"
[[ -f "' . $this->kill_script . '" ]] && rm "' . $this->kill_script . '"
exit 0
';

        file_put_contents( $this->kill_script, $script );
        $cmd = "nohup bash '{$this->kill_script}' >/dev/null 2>&1 &";
        exec( $cmd );
    }

}