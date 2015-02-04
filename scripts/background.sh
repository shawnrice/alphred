#!/bin/bash

# This is a total hack to implement backgrounding in PHP without pctnl compiled in

script=$1
args=$2

# Make sure that the script exists before trying to execute it
if [ ! -f "${script}" ]; then
	>&2 echo "${script} does not exist."
	exit 1
fi

# Simply run the php script through nohup and discard the output
nohup php ./"${script}" "${args}" >/dev/null 2>&1 &

# Exit. We're done.
exit 0