#!/bin/bash

# Note: all declared variables have been prepended with "_ALPHRED_" so that
# we avoid any sort of nameclashes that the user might implement

if [[ ! $(basename "${PWD}") =~ 'user.workflow.' ]]; then
    echo "You can execute this script only from an Alfred Workflow"
    exit 1
fi

# The location of the pid file
_ALPHRED_PHP_PID_FILE=/tmp/Alphred-Server.pid
# The location of the keep alive file
_ALPHRED_KEEP_ALIVE=/tmp/Alphred-Server-Keep-Alive
# The Location of Me
_ALPHRED_ME=$( cd "$( dirname "$0" )" && pwd )
# Kill Script
_ALPHRED_KILL_SCRIPT="${_ALPHRED_ME}/kill.sh"

[[ ! -f "${_ALPHRED_KILL_SCRIPT}" ]] && echo

# SETTINGS
_ALPHRED_MIN_QUERY=3
_ALPHRED_SERVER_PORT=8972

# This is the PHP script that is to be queried
_ALPHRED_SCRIPT="$1"
# This is the query to pass onto the script
_ALPHRED_QUERY="$2"

function Alphred::prime_server() {
    # kickoff thread handling scripts if process doesn't exist
    if [[ ! -f ${_ALPHRED_PHP_PID_FILE} ]] || ( ! ps -p $(cat "${_ALPHRED_PHP_PID_FILE}") > /dev/null ); then
        # launch the PHP Server in the Workflows Directory and store the PID
        nohup php -S "localhost:${_ALPHRED_SERVER_PORT}" -t .. &> /dev/null &
        echo $! > "${_ALPHRED_PHP_PID_FILE}"
        # launch kill script
        nohup "${_ALPHRED_ME}/kill.sh" &> /dev/null &
    fi
    # Update the Last Triggered file
    echo $(date +%s) > "${_ALPHRED_KEEP_ALIVE}" &
}

function Alphred::query_server() {
    # Update the Last Triggered file
    echo $(date +%s) > "${_ALPHRED_KEEP_ALIVE}" &

    echo $(curl -fsS --request POST \
            "http://localhost:${_ALPHRED_SERVER_PORT}/${alfred_workflow_uid}/${_ALPHRED_SCRIPT}" \
            "--data query=${_ALPHRED_QUERY}&" \
                   "alfred_workflow_data=${alfred_workflow_data}&" \
                   "alfred_workflow_bundleid=${alfred_workflow_bundleid}&" \
                   "alfred_workflow_cache=${alfred_workflow_cache}")

}

Alphred::prime_server

if [[ ${#_ALPHRED_QUERY} -ge $_ALPHRED_MIN_QUERY ]]; then
    Alphred::query_server
elif [[ ! -z $(Alphred::extend_query_server) ]]; then
    # If you want to define a fallback for this script to do when then min query is not reached,
    # then define the function "Alphred::extend_query_server"
    #
    # Example:
    # function Alphred::extend_query_server() {
    #   print "<?xml version='1.0' encoding='UTF-8'?>\n" \
    #         "<items>\n" \
    #         " <item valid='no'>\n" \
    #         "  <title>Error: ${_ALPHRED_MIN_QUERY} characters minimum are needed to perform query.</title>\n" \
    #         "  <subtitle>${alfred_workflow_name}</subtitle>\n" \
    #         "  <icon>/System/Library/CoreServices/CoreTypes.bundle/Contents/Resources/Unsupported.icns</icon>\n" \
    #         " </item>\n" \
    #         "</items>\n"
    # }
    #
    Alphred::extend_query_server "${_ALPHRED_QUERY}"
fi