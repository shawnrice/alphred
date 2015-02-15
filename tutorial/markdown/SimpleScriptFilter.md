To create the simplest script filter, all you need to do is

````php
require_once( 'Alphred.phar' );
$workflow = new Alphred;
$workflow->add_result([ 'title' => 'This is a title' ]);
$workflow->to_xml();
````

The `[]` is a shorthand for `array()`, so the same thing could be written as
````php
require_once( 'Alphred.phar' );
$workflow = new Alphred;
$workflow->add_result( array( 'title' => 'This is a title' ) );
$workflow->to_xml();
````

Or, if you'd prefer to build the array beforehand
````php
require_once( 'Alphred.phar' );
$workflow = new Alphred;
$result = array( 'title' => 'This is a title' );
$workflow->add_result( $result );
$workflow->to_xml();
````

## Error on Empty
If you'd like to make sure that the script filter displays something, then you can do
````php
require_once( 'Alphred.phar' );
$workflow = new Alphred(['error_on_empty' => true ]);
$workflow->to_xml();
````
While the above code in its current state is useless, it does help if you happen to sort through dynamic data and might filter everything out.