Alphred
=======

[![Code Climate](https://codeclimate.com/github/shawnrice/alphred/badges/gpa.svg)](https://codeclimate.com/github/shawnrice/alphred)

[![Test Coverage](https://codeclimate.com/github/shawnrice/alphred/badges/coverage.svg)](https://codeclimate.com/github/shawnrice/alphred)

php library for alfred workflows -- under heavy development.

TODO
====

First and foremost: figure out where the line is between bloat and not bloat.

### Files

Tentatively finshed:

1.  [ ] Alfred.php
2.  [ ] Alphred.php
3.  [ ] AppleScript.php
4.  [x] Config.php
5.  [ ] Database.php
6.  [ ] Date.php
7.  [ ] Exceptions.php
8.  [ ] Filter.php
9.  [x] Globals.php
10. [ ] i18n.php
10. [ ] Index.php
10. [x] Ini.php
10. [x] Keychain.php
10. [x] Log.php
10. [x] Request.php
10. [x] ScriptFilter.php
10. [ ] Text.php
10. [ ] Web.php


### Classes

#### All
1. Clean up
2. Add in exceptions
3. Document code

#### Alphred\Alfred
1. Decide if I want to keep this class or merge it into another

#### Alphred\ScriptFilter
1. Clean up
2. sort out options
3. Play with usage

#### Alphred\Result
1. Do I need this?

#### Alphred\AppleScript\AppleScript
1. Doesn't the namespacing make it sound redundant?
2. What is missing? I know that there could be some more good stuff here.

#### Alphred\Choose
1. This is messy. Clean, refactor a bit more?

#### Alphred\Dialog
1. Way too many public methods. Clean, consolidate.

#### Alphred\Notification
1. I hate this class. It's ugly. Does it even need to be a class?
2. Do I fold this into Alphred\AppleScript\AppleScript?

#### Alphred\Config
1. Write Tests
2. Make it pluggable

#### Alphred\Database\Database
1. Abstraction class over a single class... this feels dumb.

#### Alphred\Database\SQLite3
1. This class doesn't feel worth it at all right now

#### Alphred\Date
1. Find missing functionality

#### FuzzySearch
1. Finalize from BookLibrary and move here

#### Alphred\Globals
1. Write tests

#### Alphred\i18n
1. Test more thoroughly, initially it looks like it's working.

#### Alphred\Text
1. What else belongs here?

#### Alphred\Log
1. Write Tests

#### Alphred\Web
1. Clean up hardily

#### Alphred\Workflows
1. Document
2. See if there is anything else necessary in here

#### ????
What else do we add? What are we missing that would really help things through?

1. Color: should I bother?
1. Image... for processing. How much do I use this?
1. OAuth -- should I bake this in or just leave it out?
1. Network (stuff for bluetooth/wifi... is this necessary?)
1. Security Class (keychains)
1. System Functions (zip/tar/system information, etc...) FORK!



### Scripts

#### Pop-up server scripts
1. Write them entirely (or at least adapt them from my other sources)

### Documentation
#### Internal Documentation
1. Write the entire damn lot of it

#### User Documentation
1. Write the whole damn lot of it (somewhat dependent on above)