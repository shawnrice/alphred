Alphred [![Code Climate](https://codeclimate.com/github/shawnrice/alphred/badges/gpa.svg)](https://codeclimate.com/github/shawnrice/alphred) [![Test Coverage](https://codeclimate.com/github/shawnrice/alphred/badges/coverage.svg)](https://codeclimate.com/github/shawnrice/alphred)
=======


## A PHP library for [Alfred](http://www.alfredapp.com) Workflows.

### Status
> Not quite ready for public consumption.

Some tests still need to be written. The ones that have been written are a bit of a mess, but everything seems to be working.

The library will be released after there is 100% test coverage (or what is practical, I can't get all the exceptions to be thrown on my machine, but they might be thrown on different systems).

CLI-Server
===

> This is an experimental feature. Let me know how it works.

PHP is quick to run but slow to load. To help out with that, you can write your workflow to use the built-in PHP CLI server. But, remember, that the CLI-Server exists only with PHP 5.4+, so it will not work on stock systems below Mavericks (10.9).

To "install" the scripts for your workflow, just run: `php Alphred.phar create-server-scripts`. Doing so will create (or unpack) four files:
  1. server.php,
  2. server.sh,
  3. kill.sh, and
  4. alphred_urlencode.sed

Place these somewhere in your workflow. For each php script that you want to use with the server (probably just the script filters), add in `include_once( 'server.php' );` before you do much of anything. Then, in Alfred, make the script filters all `bash` and just use the line:
````shell
bash server.sh script-filter.php "{query}"
````
That's it. The rest is taken care of for you.

Or, if you're using sub-directories:
````shell
bash lib/server.sh src/script-filter.php "{query}"
````

__Important__
The console log will not work when you're using the cli-server.

Tests
===
Currently, the tests cover everything in the wrapper (the `Alphred` class, thus, most of the library). They don't cover some of the more exotic Exceptions that I cannot create on my machine (but I imagine someone could at some point). Also, they are not very strict yet and are fairly disorganized. Getting these into better shape is important.

Documentation
===
The codebase is heavily commented (except for the tests), and automatic API documentation is generated using PHPDocumentor and Apigen. There are also a few markdown files in the `tutorial` section.

Tutorial
===
I still need to write a tutorial for the library. There are some notes as markdown files in the `tutorial` folder, and there is a partly working example in the `example` folder. I say partly working because it's a dis-embodied workflow.

Contributing
===
Feel free to submit a pull request. Make sure the tests work (and write any new tests), and make sure you adhere to
the code standards and documentation. So, use `phpcs`, `phpunit`, `phpdoc`, and `apigen`. To build the phar, you need
to make sure that you have a `php.ini` file that has the line:
````ini
phar.readonly = Off
````

The mock requests tests will fail if you're running a version of PHP < `5.4`.


Credit
===
This library was written keeping [Dean Jackson's](http://www.deanishe.net/) Python library, [Alfred Workflow](https://github.com/deanishe/alfred-workflow), as a standard. The `Filter` class is almost a direct translation of it.


TODO
====
Generally,

1. finish / add to command-line functionality,
2. finish commenting / documenting,
3. finish test coverage, and
4. write tutorials.

I should also automate all the build-scripts.

I've decided to delay a more robust Indexer class that lies on top of Sqlite3 because it's just such a mess trying
to support the number of versions of FTS and the changing implementation. It can be added later.
