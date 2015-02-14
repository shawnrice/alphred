Alphred
=======

[![Code Climate](https://codeclimate.com/github/shawnrice/alphred/badges/gpa.svg)](https://codeclimate.com/github/shawnrice/alphred) [![Test Coverage](https://codeclimate.com/github/shawnrice/alphred/badges/coverage.svg)](https://codeclimate.com/github/shawnrice/alphred)

A PHP library for Alfred Workflows.
> Not quite ready for public consumption.


CLI-Server
===
PHP is quick to run but slow to load. To help out with that, you can write your workflow to use the built-in PHP CLI server. But, remember, that the CLI-Server exists only with PHP 5.4+, so it will not work on stock systems below Mavericks (10.9).

<!-- To "install" the scripts for your workflow, just run: `php  -->


Contributing
===
Feel free to submit a pull request. Make sure the tests work (and write any new tests), and make sure you adhere to
the code standards and documentation. So, use `phpcs`, `phpunit`, `phpdoc`, and `apigen`. To build the phar, you need
to make sure that you have a `php.ini` file that has the line:
````ini
phar.readonly = Off
````

The mock requests tests will fail if you're running a version of PHP < `5.4`.

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

### Files

Tentatively finshed:

1.  [ ] Alfred.php
2.  [ ] Alphred.php
3.  [ ] AppleScript.php
4.  [x] Config.php
6.  [ ] Date.php
7.  [ ] Exceptions.php
8.  [ ] Filter.php
9.  [x] Globals.php
10. [ ] i18n.php
10. [x] Ini.php
10. [x] Keychain.php
10. [x] Log.php
10. [x] Request.php
10. [x] ScriptFilter.php
10. [x] Text.php