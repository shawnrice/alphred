#!/bin/bash

# The location of the pid file
PHP_PID_FILE=/tmp/Alphred-Server.pid
# The location of the keep alive file
KEEP_ALIVE=/tmp/Alphred-Server-Keep-Alive

[[ ! -f "${PHP_PID_FILE}" ]] && exit 1 # the PHP pid file doesn't exist... whoops.

# Read the pid from the pid file
PHP_PID=$(cat "${PHP_PID_FILE}")

# Sleep for a bit...
sleep 20

# Set the flag to die as false
die=0
# We'll stay in a while loop until we're told to die.
while [[ $die -eq 0 ]]; do
  # see if we get a response from the webserver

  # Check to see if the status file has been updated
  updated=$(cat "${KEEP_ALIVE}")    # this is a file that the PHP script updates
  now=$(date +%s)                   # this is now
  updated=$(( $updated + 40 ))      # this is an adjusted time

  # if the server hasn't shown activity in the last 20 seconds, kill it
  [[ $now -gt $updated ]] && die=1

  # sleep for 20 seconds and try it again
  sleep 20

done

kill $PHP_PID

[[ -f "${PHP_PID_FILE}" ]] && rm "${cache}/php.pid"
[[ -f "${KEEP_ALIVE}" ]]   && rm "${cache}/last"

exit 0