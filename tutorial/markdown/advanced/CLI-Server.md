CLI-Server
===
Alphred contains a few scripts that let you run your workflow over a pop-up webserver rather than just the regular PHP CLI.

> Alphred uses the built-in CLI-Server SAPI that comes with PHP 5.4+. Thus, it will not work with OS X less than Mavericks.

## Simple Usage
Make sure that the code to run your script filter (in Alfred's Script Filter dialog box) is:
````shell
bash path/to/server.sh path/to/php/script-filter.php "{query}"
````
Then, in `script-filter.php` (or whatever you call it), make sure you include the line:
````php
require_once( __DIR__ . '/path/to/server.php' );
````
The file `server.php` sets all the Alfred global variables that you would expect to have, and it maps "{query}" to `$argv[1]`, which is where you would normally find it. Thus, minimal changes are needed to your code.

## Creating the scripts
The scripts come packaged in `Alphred.phar`, and, to get access to them, just run
````shell
php Alphred.phar create-server-scripts
````

and the scripts will be created for you. Move them wherever you like, but make sure that they are all in the same directory.

# Editing and extending the scripts
Don't edit the scripts, unless you want to set a minimum query length (i.e. don't run the script filter unless there is a query of at least three characters).

## Setting a minimum query length
Open `server.sh` and change line 4 from
````shell
_ALPHRED_MIN_QUERY=0
````

to whatever you need. So, if you want to have the minimum query set at 3, you would use:
````shell
_ALPHRED_MIN_QUERY=3
````

## Creating a fallback
If you set min query, then you might want to provide some feedback for the user to tell them to keep typing. The easiest way to do this is to create a file called `extend_query_server.sh` and place it in a function called `Alphred::extend_query_server`. Here is an example:
````shell
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
````

# Debugging
The console log is not available when you're using the cli-server. This is because `STDERR` is directed to `/dev/null/`, which is needed in order to make the server run in the background. Instead, you can either log everything to a log file:
````php
// Log using the wrapper
$workflow->log( 'This is a log message', 1 );

// Log using the Log component:
Alphred\Log::file( 'This is a log message', 1 );
````

Or, debug without the cli-server by just running the script filter with:
````shell
php src/script-filter.php "{query}"
````

And, once that works, turn on the cli-server with the regular invocation:
````shell
bash server-scripts/server.sh src/script-filter.php "{query}"
````

# What's to gain?

## Why create a server?
Well, PHP is fast to run, but it's slow to start, partly because the php binary is so large. Thus, there is some noticeable lag, especially when running a script for the first time in a while.

> PHP compiles your script to OpCode before running it. If the script has been run recently, then it uses the OpCode; otherwise, it needs to recompile it, slowing things down.

The server runs quickly, making everything feel more responsive. It adds that extra little performance to your script filters.


## Can I use it on my other scripts?
Yes, you could, but you don't get nearly as much value from it. Script filters deal with user-interaction, and so we notice when they run slowly. We don't notice, nearly as much, when other scripts run slowly.

## Running the server
Alphred's cli-server scripts launch the server on `http://localhost:8792`, and then makes cURL calls to it. But, we don't want to launch a server and leave it running forever on the user's computer, so Alphred launches a kill script at the same time that stops the server after a period of inactivity, usually 20-30 seconds.

## Won't it clash with other workflows that use it?
No. The server is launched so that the "web root" is in the directory where all the workflows are stored. The `server.sh` file routes the request into your workflow. This makes sure that workflows won't fight over the cli-server. But it also means that you have to be more careful with your paths in the PHP scripts because `$PWD` is now set not in the workflow root, but one directory level below it, where all the workflows are. So, set file paths either as absolute (if you're access the cache or data directories), or make them more explicitly relative using `__DIR__`

> `__DIR__` is the directory of the current php file.

So, if you had a directory setup like:
````shell
info.plist
icon.png
workflow.ini
icons/icon1.png
icons/icon2.png
server-scripts/alphred_urlencode.sed
server-scripts/extend_query_server.sh
server-scripts/kill.sh
server-scripts/server.php
server-scripts/server.sh
src/action.php
src/script-filter.php
````

Then, to access the icons from `script-filter.php`, you would write:
````php
$icon1 = __DIR__ . '/../icons/icon1.png';
$icon2 = __DIR__ . '/../icons/icon2.png';
````

To include the server script, you would write:
````php
require_once( __DIR__ . '/../server.php' );
````

## Important Notes
The console log will not work, which makes debugging a bit harder. Well, the console log still works, but the console is just taken somewhere else that the Alfred debugger cannot read. File logs still work just fine.

If you get curl errors with a 500 http response code, it probably means that there is a bug in the php script.

Do not include files with `require_once( './src/my_file.php' );` because they will not be found. Instead, use something like: `require_once( __DIR__ . '/src/my_file.php' );` The magic `__DIR__` is the directory where the php file is located.

Make sure that you escape double-quotes; otherwise, your entire query might not end up in `$argv[1]`.