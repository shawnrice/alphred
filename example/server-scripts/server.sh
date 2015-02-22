#!/bin/bash

# SETTINGS
_ALPHRED_MIN_QUERY=0

################# DO NOT MODIFY BELOW THIS LINE ###########################################

# Note: all declared variables have been prepended with "_ALPHRED_" so that
# we avoid any sort of nameclashes that the user might implement

###########################################################################################

# The location of the pid file
_ALPHRED_PHP_PID_FILE=/tmp/Alphred-Server.pid
# The location of the keep alive file
_ALPHRED_KEEP_ALIVE=/tmp/Alphred-Server-Keep-Alive
# The Location of the file
_ALPHRED_ME=$( cd "$( dirname "$0" )" && pwd )
# Kill Script
_ALPHRED_KILL_SCRIPT="${_ALPHRED_ME}/kill.sh"
# The port for the scripts to run on
_ALPHRED_SERVER_PORT=8972

# If we cannot find the kill script, then exit with error code 1.
if [[ ! -f "${_ALPHRED_KILL_SCRIPT}" ]]; then
	echo "ERROR: Cannot find kill script; please reinstall server scripts."
	exit 1
fi

# This is the PHP script that is to be queried
_ALPHRED_SCRIPT="$1"
# This is the query to pass onto the script; encode it with the sed file
_ALPHRED_QUERY=$(echo "$2" | sed -f "${_ALPHRED_ME}/alphred_urlencode.sed")

_ALPHRED_GLOBAL_VARS="alfred_preferences alfred_preferences_localhash alfred_theme alfred_theme_background alfred_theme_subtext alfred_version alfred_version_build alfred_workflow_bundleid alfred_workflow_cache alfred_workflow_data alfred_workflow_name alfred_workflow_uid ALPHRED_IN_BACKGROUND"

# tries to find the symlink, if the workflow is symlinked
function Alphred::find_symlink_dir() {
	OLDIFS=$IFS
	IFS=$'\n'
	for dir in $(cd "${alfred_preferences}/workflows"; echo $(ls -l | grep ^l)); do
		# This is a really long regex that should just grab the directory name
		directory=$(echo $dir | grep "$(pwd)" | sed 's|^[lrwx@-]\{1,\}[ ]*[0-9]\{1,\}[ ]*[^ ]\{1,\}[ ]*[a-zA-Z]\{1,\}[ ]*[0-9]\{1,\}[ ]*[A-Za-z]\{1,\}[ ]*[0-9]\{1,\}[ ]*[0-9]\{2\}:[0-9]\{2\}\([A-Za-z0-9 -]\{1,\}\) \-\> '"$PWD"'[\/]*|\1|g' | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')

		if [ ! -z "${directory}" ]; then
			# we found something, so lets get out of the loop
			break;
		fi
	done
	# Bring back the old separator
	IFS=$OLDIFS
	echo $directory
}

function isset() {
    [[ -n "${1}" ]] && test -n "$(eval "echo "\${${1}+x}"")"
}

function Alphred::prime_server() {
    # kickoff thread handling scripts if process doesn't exist
    if [[ ! -f ${_ALPHRED_PHP_PID_FILE} ]] || ( ! ps -p $(cat "${_ALPHRED_PHP_PID_FILE}") > /dev/null ); then
        # launch the PHP Server in the Workflows Directory and store the PID
        # directory=$(echo "${alfred_preferences}/workflows" | sed 's/ /\\ /g')
        directory="${alfred_preferences}/workflows"
        /usr/bin/nohup php -S "localhost:${_ALPHRED_SERVER_PORT}" -t "${directory}" &> /dev/null &
        echo $! > "${_ALPHRED_PHP_PID_FILE}"
        # launch kill script
        nohup /bin/bash "${_ALPHRED_ME}/kill.sh" &> /dev/null &
        # we need to put in a very small delay to let the server boot up otherwise the first time will fail
        sleep 0.3
    fi
    # Update the Last Triggered file
    echo $(date +%s) > "${_ALPHRED_KEEP_ALIVE}" &
}

function Alphred::query_server() {
    # Update the Last Triggered file
    echo $(date +%s) > "${_ALPHRED_KEEP_ALIVE}" &

    # Get the directory name so that we can route things appropriate
    directory=$(basename "${PWD}")
    if [[ -z $(pwd | grep "${alfred_preferences}") ]]; then
    	# If the directory does not match `pwd`, then we'll find the symlinked directory name
    	# and URL encode it
			directory=$(echo $(Alphred::find_symlink_dir) | sed -f "${_ALPHRED_ME}/alphred_urlencode.sed")
		fi

		data_string=''
		if [[ ! -z "${_ALPHRED_QUERY}" ]]; then
			data_string="query=${_ALPHRED_QUERY}&"
		fi
		for var in $_ALPHRED_GLOBAL_VARS; do
			if [[ '0' == $(isset $var; echo $?) ]]; then
				data_string="${data_string}${var}"=$(eval echo \$$var)'&'
			fi
		done
		if [ ! -z "${data_string}" ]; then
			# remove the trailing ampersand
			len=${#data_string}
			len=$(( len - 1 ))
			data_string="${data_string:0:len}"
			# I might need to, somehow, do a URL encoding of this
			data_string="--data '${data_string}'"
		fi

    cmd="curl -fsS --request POST 'http://localhost:${_ALPHRED_SERVER_PORT}/${directory}/${_ALPHRED_SCRIPT}' ${data_string}"
    echo $(eval $cmd) | xmllint --format -

}

# Prime the server (make sure it's running and keep it alive)
Alphred::prime_server

if [[ ${#_ALPHRED_QUERY} -ge $_ALPHRED_MIN_QUERY ]]; then
    Alphred::query_server
elif [[ '0' == $(type -t Alphred::extend_query_server; echo $?) ]]; then
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