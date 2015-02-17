Many workflows need to be configured, and Alphred provides an easy way for you to manage simple configurations. Out of the box, it provides methods to write configuration files as `json`, `ini`, or `sqlite3`. They provide simple key-value pairs.

# Note
This is outdated. You can no longer set the handler or the filename through the individual methods. Instead, you have
set them either when you instantiate the Alphred object or in a `workflow.ini` file.

#### Methods
> `config_set( $key, $value, $handler, $filename )`
> `config_read( $key, $handler, $filename )`
> `config_delete( $key, $handler, $filename )`

Configuration files are kept in your workflow's data directory.

You do not need to create a config file ahead of time; instead, you just need to use the functions.
````php
$workflow = new Alphred;
$workflow->config_set( 'username', 'shawnrice' );
````
To read a value (`config_read` will return `null` if the value has not been set):
````php
$username = $workflow->config_read( 'username' );
````

To delete a value, just use
````php
$workflow->config_delete( 'username' );
````

### Handlers
The config methods can use either
1. `ini`,
2. `json`, or
3. `sqlite3`
as a filetype. By default, Alphred uses `ini` files for configuration. These are simpler because users who open them with something like TextEdit will not break them as easily as `json` files.

The `json` and `ini` versions can handle mutliple dimensional arrays easily. The `sqlite` version is not as robust yet. Also (this needs to be verified), the `sqlite` handler will not work with anything pre-Mavericks because the version of `sqlite3` that is standard is too old.

### Choosing a different handler
If you want to use a different handler, then add it on as an argument in each call:
````php
$workflow = new Alphred;
$workflow->config_set( 'username', 'shawnrice', 'json' );
$workflow->config_read( 'username', 'json' );
$workflow->config_delete( 'username', 'json' );
````

### Changing the filename
By default, Alphred will name the file `config` with the appropriate extension (`ini`, `json`, or `sqlite3`). If you want to specify a different filename, then pass it as an argument, after the config type.
````php
$workflow = new Alphred;
$workflow->config_set( 'username', 'shawnrice', 'json', 'my-config-filename' );
$workflow->config_read( 'username', 'json', 'my-config-filename' );
$workflow->config_delete( 'username', 'json', 'my-config-filename' );
````

### Changing the handler and filename in workflow.ini
Code will be written to make this happen.