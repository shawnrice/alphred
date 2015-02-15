Most of this tutorial has assumed that you will be using the wrapper, or the `Alphred` class to access everything. However, you can also access the components directly and opt not to use the wrapper. Watch out for Exceptions that some of the methods throw natively that you need not worry about with the wrapper. Consult the API documentation for more.

Do note that every class is under the namespace `Alphred`, and so to access the Log class directly, for instance, you'll need to do something like:
````php
Alphred\Log::console( 'This is a console message' );
````
