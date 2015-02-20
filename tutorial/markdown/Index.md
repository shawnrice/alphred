These files should cover some general usage of how to use the Alphred wrapper (the `Alphred` class).

## What is Alphred?
Alphred is a PHP library to aid in the creation of workflows for Alfred. Most of Alphred should work with PHP 5.3+ (OS X 10.6+), but some features need PHP 5.4+ (OS X 10.9+). Alphred generally needs Alfred v2.5+ to run.

## Features
* Simple AlfredXML generation for script filters, including extended XML attributes
* Create and manage configuration files in `ini`, `json`, or `sqlite3` from a few lines of code
* Easy http requests with get or post, including data caching
* Use the system keychain to store and retrieve passwords
* Simple logging to single or multiple files as well as the console with variable log levels
* Filter results easily to match a query
* Make your script filters faster by using the cli-server (PHP 5.4+) with almost no change to your existing code
* Easily change dates into strings, exact (1 day, 3 hours, and 23 minutes ago) or fuzzy (yesterday)
* Use title case without any extra work
* fork php scripts to have them run in the background (and they know that they're in the background)
* send asynchonrous notifications with no external library
* know if the user's theme is light or dark (to set different icons)
* write complex workflows in 50-60 lines of code

Look at https://github.com/shawnrice/alphred/blob/master/example/script-filter.php