#!/bin/bash

# This is a bash script that wraps around phpunit to run the tests. Since we have to simulate the environment
# and create a temporary test server to use for the requests library, I decided to write it as a bash script.

if [ ! $(which phpunit) ]; then
	echo "ERROR: phpunit cannot be found, so please install it before running the tests."
fi

echo "Ready to start running tests."
echo "----------------------------"
cd tests
phpunit