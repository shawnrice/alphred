#!/bin/bash

# This is a bash script that wraps around phpunit to run the tests. Since we have to simulate the environment
# and create a temporary test server to use for the requests library, I decided to write it as a bash script.

if [ ! $(which phpunit) ]; then
	echo "ERROR: phpunit cannot be found, so please install it before running the tests."
fi

function start_server() {
	nohup /usr/bin/php -S localhost:8888 resources/router.php  >/dev/null 2>&1 &
	# php -S localhost:8888 resources/router.php &
	echo $!
}

function kill_server() {
	kill $SERVER_PID
}




echo "Do remember that your computer is going to start bouncing through some windows."
echo "And tests will fail if you don't have iTerm or Google Chrome installed...."
echo "Lastly, there is required interaction."
sleep 1
echo "----------------------------"
echo "Ready to start running tests."
echo "----------------------------"
cd tests
SERVER_PID=$(start_server) # Starts the server and returns the PID
sleep 1
phpunit

kill_server