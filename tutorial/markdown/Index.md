These files should cover some general usage of how to use the Alphred wrapper (the `Alphred` class).

## What is Alphred?
Alphred is a PHP library to aid in the creation of workflows for Alfred. Most of Alphred should work with PHP 5.3+ (OS X 10.6+), but some features need PHP 5.4+ (OS X 10.9+). Alphred generally needs Alfred v2.5+ to run.

## Features
* Simple AlfredXML generation for [script filters](https://github.com/shawnrice/alphred/blob/master/tutorial/markdown/SimpleScriptFilter.md), including extended XML attributes
* [Create and manage configuration files](https://github.com/shawnrice/alphred/blob/master/tutorial/markdown/Configurations.md) in `ini`, `json`, or `sqlite3` from a few lines of code
* Easy [http requests](https://github.com/shawnrice/alphred/blob/master/tutorial/markdown/Request.md) with get or post, including data caching
* Use the system keychain [to store and retrieve passwords](https://github.com/shawnrice/alphred/blob/master/tutorial/markdown/Passwords.md)
* [Simple logging](https://github.com/shawnrice/alphred/blob/master/tutorial/markdown/Logging.md) to single or multiple files as well as the console with variable log levels
* [Filter results](https://github.com/shawnrice/alphred/blob/master/tutorial/markdown/Filter.md) easily to match a query
* Make your script filters faster by using the cli-server (PHP 5.4+) with almost no change to your existing code
* Easily change dates into strings, exact (1 day, 3 hours, and 23 minutes ago) or fuzzy (yesterday)
* Use title case without any extra work
* fork php scripts to have them run in the background (and they know that they're in the background)
* send asynchonrous notifications with no external library
* know if the user's theme is light or dark (to set different icons)
* write complex workflows in 50-60 lines of code

## Example
An example workflow ([download](https://github.com/shawnrice/alphred/raw/master/example/gh-repos-example.alfredworkflow)) that takes your Github Username and Password and grabs a list of your repos and filters them by query is in the example folder. It uses the cli-server. You can see the well-documented code for the [script filter](https://github.com/shawnrice/alphred/blob/master/example/script-filter.php) and the [action script](https://github.com/shawnrice/alphred/blob/master/example/action.php). Without the comments, they reduce to about 60 lines of code together.

## Read More
See the other markdown files in this directory to read more about:

* Creating configuration files
* Filtering items
* Logging
* Handling passwords
* Making http requests
* Using script filters (and a simpler version)
* Using the individual components
* Adding a workflow.ini file