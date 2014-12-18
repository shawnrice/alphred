#!/bin/bash

if [[ ! $(basename "${PWD}") =~ 'user.workflow.' ]]; then
    echo "You can execute this script only from an Alfred Workflow"
    exit 1
fi

# The location of the pid file
PHP_PID_FILE=/tmp/Alphred-Server.pid
# The location of the keep alive file
KEEP_ALIVE=/tmp/Alphred-Server-Keep-Alive
# The Location of Me
ME=$( cd "$( dirname "$0" )" && pwd )
# Kill Script
KILL_SCRIPT="${ME}/kill.sh"

[[ ! -f "${KILL_SCRIPT}" ]] && echo

# SETTINGS
MIN_QUERY=3
SERVER_PORT=8972

SCRIPT="$1"
QUERY="$2"

function prime_server() {
    # kickoff thread handling scripts if process doesn't exist
    if [[ ! -f ${PHP_PID_FILE} ]] || ( ! ps -p $(cat "${PHP_PID_FILE}") > /dev/null ); then
        # launch the PHP Server in the Workflows Directory and store the PID
        nohup php -S localhost:$SERVER_PORT -t .. &> /dev/null &
        echo $! > "${PHP_PID_FILE}"
        # launch kill script
        nohup "${ME}/kill.sh" &> /dev/null &
    fi
    # Update the Last Triggered file
    echo $(date +%s) > "${KEEP_ALIVE}" &
}

function query_server() {
    # Update the Last Triggered file
    echo $(date +%s) > "${KEEP_ALIVE}" &

    echo $(curl  -fsS --request POST "http://localhost:${SERVER_PORT}/${alfred_workflow_uid}/${SCRIPT}" \
        --data query="${QUERY}"&alfred_workflow_data="${alfred_workflow_data}"&alfred_workflow_bundleid="${alfred_workflow_bundleid}"&alfred_workflow_cache="${alfred_workflow_cache}")

}

prime_server

if [[ ${#QUERY} -ge $MIN_QUERY ]]; then
    query_server
else
    # Currently, there is no fallback..., should we bake one in? Do
    # we return XML or provide an extensible way to take a backup action?
    #
    # We need a statement in order to make this not fail.
    a=a
fi