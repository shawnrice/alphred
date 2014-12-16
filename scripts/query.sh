#!/bin/bash

# To Query from a local webserver

if [[ ! $(basename "${PWD}") =~ 'describe-' ]]; then
    echo "You can execute this script only from an Alfred Workflow"
    exit 1
fi

port='8972'
root=$(dirname "${PWD}")
user.workflow.

echo $root


# So,...
#  1. we launch a reusable webserver from the below workflow root.
#  2. we route all calls to the correct file... "automatically"?
#  3. we might need to set the correct alfred variables...
#  4. send things as post variables...
curl -fsS --request POST "localhost:${port}" \
     --data 'username=myusername&password=mypassword'