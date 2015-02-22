The filter method allows you to search through results and filter out irrelevant ones. It is fairly powerful and flexible. It is a translation of the filter functionality of Deanishe's Python library Alfred Workflow, and it works fairly well.

#### Method
> filter( $haystack, $needle, $key = false, $options = [] )

#### Explanation

The filter method filters an array (`$haystack`) by removing results that do not match a query (`$needle`). If the array is associative and you want to match one particular key, then set the `$key`.

Passing an empty query ($needle) to this method will simply return the initial array. If you have `fold` on, then this will fail on characters that cannot be translitered into regular ASCII, so most Asian character sets.

The options to be set are:
* max_results  -- the maximum number of results to return (default: false)
* min_score    -- the minimum score to return (0-100) (default: false)
* return_score -- whether or not to return the score along with the results (default: false)
* fold         -- whether or not to fold diacritical marks, thus making
									`über` into `uber`. (default: true)
* match_type	 -- the type of filters to run. (default: MATCH_ALL)

The match_type is defined as constants, and so you can call them by the flags or by the integer value. Options:
* Match items that start with the query
* 1: MATCH_STARTSWITH
* Match items whose capital letters start with ``query``
* 2: MATCH_CAPITALS
* Match items with a component "word" that matches ``query``
* 4: MATCH_ATOM
* Match items whose initials (based on atoms) start with ``query``
* 8: MATCH_INITIALS_STARTSWITH
* Match items whose initials (based on atoms) contain ``query``
* 16: MATCH_INITIALS_CONTAIN
* Combination of MATCH_INITIALS_STARTSWITH and MATCH_INITIALS_CONTAIN
* 24: MATCH_INITIALS
* Match items if ``query`` is a substring
* 32: MATCH_SUBSTRING
* Match items if all characters in ``query`` appear in the item in order
* 64: MATCH_ALLCHARS
* Combination of all other ``MATCH_*`` constants
* 127: MATCH_ALL

#### Usage
Assume that you have an array that looks like:
````php
$array[] = [ 'title' => 'The ABCs of Gardening', 'author' => 'Amanda Hugenkiss' ];
````
and that there are multiple values in `$array`. You will need to specify a `key` to filter on, either `title` or `author`. An array that would look like:
````php
$array[] = 'The ABCs of Gardening';
````
would keep `false` as the key.

So, the easiest way to use the filter mechanism is:

````php
$workflow = new Alphred;
// we assume an array that looks like the one with 'title' and 'author' mentioned above and that $query has
// already been set
$results = $workflow->filter( $array, $query, 'title' );
````
