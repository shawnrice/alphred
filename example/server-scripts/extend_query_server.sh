#!/bin/bash

function Alphred::extend_query_server() {
  echo "<?xml version='1.0' encoding='UTF-8'?>"
	echo "<items>"
	echo " <item valid='no'>"
	echo "  <title>Error: ${_ALPHRED_MIN_QUERY} characters minimum are needed to perform query.</title>"
	echo "  <subtitle>${alfred_workflow_name}</subtitle>"
	echo "  <icon>/System/Library/CoreServices/CoreTypes.bundle/Contents/Resources/Unsupported.icns</icon>"
	echo " </item>"
	echo "</items>"
}