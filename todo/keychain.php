<?php

namespace Alphred;


class Keychain {

    public function __construct() {

    }

    public function execute( $command ) {
        $cmd = "osascript -e 'do shell script \"{$command}\" with administrator privileges'";
        return exec( $cmd );
    }

    public function add_generic_password( $opts ) {
        $cmd = "security add-generic-password";

        -a account
        -s service
        -w password
        -u

        return $this->execute( $cmd );
    }

    public function delete_generic_password( $opts ) {
        $cmd = "security delete-generic-password";
        return $this->execute( $cmd );
    }

    public function find_generic_password( $opts ) {
        $cmd = "security find-generic-password";
        return $this->execute( $cmd );
    }





    public function add_internet_password( $opts ) {
        $cmd = "security add-internet-password";
        return $this->execute( $cmd );
    }

    public function delete_internet_password( $opts ) {
        $cmd = "security delete-internet-password";
        return $this->execute( $cmd );
    }

    public function find_internet_password( $opts ) {
        $cmd = "security find-internet-password";
        return $this->execute( $cmd );
    }



}



// Add in keychain scripting
// below are notes on OS X's `security` command line tool
add-generic-password
add-internet-password
find-generic-password
find-internet-password
delete-generic-password
delete-internet-password


 add-generic-password [-h] [-a account] [-s service] [-w password] [options...] [keychain]
            Add a generic password item.

            -a account      Specify account name (required)
            -C type         Specify item type (optional four-character code)
            -s service      Specify service name (required)
            -w password     Specify password to be added
            -U              Update item if it already exists (if omitted, the item cannot already exist)

            By default, the application which creates an item is trusted to access its data without warning.
            You can remove this default access by explicitly specifying an empty app pathname: -T "". If no
            keychain is specified, the password is added to the default keychain.

  add-internet-password [-h] [-a account] [-s server] [-w password] [options...] [keychain]
            Add an internet password item.

            -a account      Specify account name (required)
            -d domain       Specify security domain string (optional)
            -j comment      Specify comment string (optional)
            -p path         Specify path string (optional)
            -P port         Specify port number (optional)
            -r protocol     Specify protocol (optional four-character SecProtocolType, e.g. "http", "ftp ")
            -s server       Specify server name (required)
            -t authenticationType
                            Specify authentication type (as a four-character SecAuthenticationType, default
                            is "dflt")
            -w password     Specify password to be added
            -U              Update item if it already exists (if omitted, the item cannot already exist)

            By default, the application which creates an item is trusted to access its data without warning.
            You can remove this default access by explicitly specifying an empty app pathname: -T "". If no
            keychain is specified, the password is added to the default keychain.



    find-generic-password [-h] [-a account] [-s service] [-options...] [-g] [-keychain...]
            Find a generic password item.

            -a account      Match account string
            -c creator      Match creator (four-character code)
            -C type         Match type (four-character code)
            -D kind         Match kind string
            -G value        Match value string (generic attribute)
            -j comment      Match comment string
            -l label        Match label string
            -s service      Match service string
            -w              Display the password(only) for the item found



     delete-generic-password [-h] [-a account] [-s service] [-options...] [-keychain...]
            Delete a generic password item.

            -a account      Match account string
            -c creator      Match creator (four-character code)
            -C type         Match type (four-character code)
            -D kind         Match kind string
            -G value        Match value string (generic attribute)
            -j comment      Match comment string
            -l label        Match label string
            -s service      Match service string


    delete-internet-password [-h] [-a account] [-s server] [options...] [keychain...]
            Delete an internet password item.

            -a account      Match account string
            -c creator      Match creator (four-character code)
            -C type         Match type (four-character code)
            -d securityDomain
                            Match securityDomain string
            -D kind         Match kind string
            -j comment      Match comment string
            -l label        Match label string
            -p path         Match path string
            -P port         Match port number
            -r protocol     Match protocol (four-character code)
            -s server       Match server string
            -t authenticationType
                            Match authenticationType (four-character code)


    find-internet-password [-h] [-a account] [-s server] [options...] [-g] [keychain...]
            Find an internet password item.

            -a account      Match account string
            -C type         Match type (four-character code)
            -d securityDomain Match securityDomain string
            -p path         Match path string
            -P port         Match port number
            -r protocol     Match protocol (four-character code)
            -s server       Match server string
            -w              Display the password(only) for the item found