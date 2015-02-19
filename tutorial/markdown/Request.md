Alphred provides a simple requests library to aid in making `get` and `post` requests. It provides more functionality than `file_get_contents( $url );` and is easier to use than PHP's cURL functionality (because it is a wrapper to simplify that exact functionality). But, if you do need something more advanced, then I recommend using [Guzzle](http://guzzle.readthedocs.org/en/latest/), writing your own, or extending the `Alphred\Request` class.

The component supports basic http authentication but nothing as fancy as oauth.

#### Methods
> get( $url, $options = false, $cache_ttl = 600, $cache_bin = true )

> post( $url, $options = false, $cache_ttl = 600, $cache_bin = true )

> clear_cache( $bad = false )

Options are set as an associative array. Keys can be:

*  params     (array as $key => $value)
*  auth       (array as [ username, password ] )
*  user_agent (string)
*  headers    (array as list of headers to add)

The `$cache_bin` is a sub-folder in the workflow's cache directory where the requests are saved. If `$cache_bin` is set to `true`, then it will use a folder that is the hostname of the URL. So, a call to `api.github.com` will create a folder named `api.github.com` where all requests are saved. It's recommended that you use a cache_bin. `$cache_ttl` is the cache "time-to-live," or how long the cache will be valid for, measured in seconds. By default, it's set to 600 seconds, or ten minutes. If the cache file is older than the `$cache_ttl` value, then the library will attempt to contact the server; if it fails (computer offline, or whatever else), then it will return old cached data, if available. The cache data is saved raw, and a unique hash is made depending on all the parameters, so different queries are cached differently.

Basic example:
```php
// This assumes that $username and $password have already been set

// Github advises us to explicitly add the header below
$options['headers'] = [ 'Accept: application/vnd.github.v3+json' ];
// Github also demands that we set a user-agent
$options['user_agent'] = 'alfred';
// Github gives us a default of 30 repos in the response, but we can push it to 100. Let's get 100.
$options['params'] = [ 'per_page' => 100 ];
// Lastly, we're using basic authorization with Github rather than any Oauth or Access Tokens, so
// we'll go ahead and add in the basic authorization with the username and password below.
$options['auth'] = [ $username, $password ];
// The request variables have been set, so let's execute it. If we wanted to adjust the caching options,
// then we'd pass another argument.
$repos = $Alphred->get( "https://api.github.com/users/{$username}/repos", $options );
// We know that we're getting JSON data, so we'll also decode it into an easily accessible array.
$repos = json_decode( $repos, true );
````

Granted, we could have written the same thing as:
````php
$repos = json_decode( $Alphred->request_get( "https://api.github.com/users/{$username}/repos", [
	'params' => [ 'per_page' => 100 ],
	'auth' => [ $username, $password ],
	'user_agent' => 'alfred',
	'headers' => [ 'Accept: application/vnd.github.v3+json' ]
]), true );
````

The above example uses the automatic cache bin and the default cache life of ten minutes. Also, since it knows it's getting `json` data, it decodes the data.