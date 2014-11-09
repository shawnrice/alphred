<?php

namespace Alphred\Http;

class http {


    public function testServer() {

    }

    public function tryServers() {

    }

    public function args() {
        // sanitize args
    }

  public function call( $url, $settings = array() ) {
    // Function to simplify a cURL request
  }

  public function auth_call( $url, $user, $pass, $settings = array() ) {
    // Function to simplify an auth cURL request
  }

  public function post( $url, $settings = array()) {
    // Function to simplify a post request
  }

  public function auth_post($url, $user, $pass, $settings = array() ) {
    // Function to simplify an auth post request
  }

  public function dl( $url ) {
    // Function to download a URL easily.
    return file_get_contents($url);
  }

}