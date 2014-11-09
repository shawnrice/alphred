<?php

namespace Alphred\Security;

// /usr/bin/security
//
// Save password:

//  wf = Workflow()

 // wf.save_password('hotmail-password', 'password1lolz')

 // password = wf.get_password('hotmail-password')

 // wf.delete_password('hotmail-password')

 // # raises PasswordNotFound exception
 // password = wf.get_password('hotmail-password')

class Security {

    public function keychain() {

    }
  // Functions for encrypting and decryting strings
  public function setSalt( $salt ) {
    $this->salt = $salt;
  }

  public function getSalt() {
    return $this->salt;
  }

  public function encryptString( $string, $salt = FALSE ) {
    if ( $salt == FALSE )
      $salt = $this->salt;
    $string  = $salt . $string . $salt;
    $cmd = 'out=$(echo "' . $string . '" | openssl base64 -e); echo "${out}"';
    return exec( $cmd );
  }

  public function decryptString( $string, $salt = FALSE ) {
    if ( $salt == FALSE )
      $salt = $this->salt;
    $cmd   = 'out=$(echo "' . $string . '" | openssl base64 -d); echo "${out}"';
    return str_replace( $salt, '', exec( $cmd ) );
  }

  public function writeProtected( $key, $value ) {
    $value = $this->encryptString( $value );
    $this->writeSetting( $key, $value );
  }

  public function readProtected( $key ) {
    return $this->decryptString( $this->readSetting( $key ) );
  }



  public function createKeyPair() {

  }

  public function sign() {

  }

  public function checkSignature() {

  }

}