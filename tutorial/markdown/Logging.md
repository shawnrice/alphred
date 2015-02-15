Alphred gives you easy access to two different kinds of logs: a console and a file. The console log writes to `STDERR` so that it does not interfere with script filters, and it shows up in the Alfred debugger window.

### Log Levels
There is a pre-defined set of log levels indicating severity. These cannot be changed without extending the Log class. By default there are:
0. DEBUG
1. INFO
2. WARNING
3. ERROR
4. CRITICAL

### Setting a log level
Alphred sets the default log level to `WARNING` (2). Thus, by default, you will not see any `DEBUG` or `INFO` messages.

You can set your log level either in a `workflow.ini` or by defining it before the including Alphred.phar. If you do not, then it will default to `WARNING`. Setting a log level means that messages that level or above will be displayed, so if you set your log level to `ERROR` (3), then only `ERROR` and `CRITICAL` messages will be either written to the file or the console.

To define the level in the code, place something like:
````php
define( 'ALPHRED_LOG_LEVEL', 2 );
````
before you include Alphred.phar. You can also use the more verbose version:
````php
define( 'ALPHRED_LOG_LEVEL', 'WARNING' );
````

My recommendation is to include a `workflow.ini` file with your workflow. Place it next to `info.plist`. In it, place the section
````ini
[alphred]
log_level = INFO
````
or whatever you want the level to be set at. If you do this, then it is easy to change both for yourself and for others if they're running into trouble. So, make your workflow log to the console often with debug messages, and then set the default log level to something higher before you distribute it.

### Console Logging
The simplest way to log a message to a console:
````php
$workflow = new Alphred;
$workflow->console( 'This is a message' );
````
That's it, and it will display the above text with a log level 'INFO'. If you haven't set your log level, then the message will not show up (because Alphred has a default setting of `WARNING`).

To specify the log level, just add the next argument:
````php
$workflow->console( 'This is a message', 2 );
````
or
````php
$workflow->console( 'This is a message', 'WARNING' );
````
They're the same.

### File Logging
Alphred can also create a log file for you. It will be located in your workflow's data directory. By default, the file will be called `workflow.log`, but you can change the name, and you can also use multiple logs.

Writing a log message works exactly the same as writing a console message, except we use `log` instead of `console`.
````php
$workflow = new Alphred;
$workflow->log( 'This is a log message' );
````
will write the above text to the file `workflow.log` in your data directory. You can specify a level just like with console:
````php
$workflow->log( 'This is a log message', 'WARNING' );
````
To specify the log file, use a third argument that is the basename of the file. (All files will have the extension `.log`)
````php
$workflow->log( 'This is a log message', 'WARNING', 'my-log' );
````
This makes it super easy for you to have multiple log files:
````php
$workflow->log( 'This message will go in the "requests" log', 'WARNING', 'requests' );
$workflow->log( 'This message will go in the "llamas" log', 'WARNING', 'llamas' );
````