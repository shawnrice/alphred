Passwords can be a sensitive subject. You might need to store them, and, if you do, you should, obviously, store them securely, not in plain text. Alphred provides a few handy helpers. The first is the `Keychain` class that wraps around OSX's `security` command, allowing command line access to the keychain.

#### Methods
> `save_password( $account, $password )`
> `get_password( $account )`
> `delete_password( $account )`

#### Explanation
So, to use this through the wrapper, you can simply do the following
````php
// some code above populates the $password variable
$workflow = new Alphred;
$workflow->save_password( 'github.com', $password );
````

That's it. The password is now securely in the user's Keychain. To access it later, just use:
````php
$workflow = new Alphred;
$password = $workflow->get_password( 'github.com' );
````

If you need to delete it from the Keychain, just use the `delete_password` method as such
````php
$workflow = new Alphred;
$workflow->delete_password( 'github.com' );
````
You'll be happy to know that this command will not delete everything associated with Github in the Keychain; instead, it deletes only the entry stored by your workflow.

## Hidden Input

#### Method
> `get_password_dialog( $title, $text, $icon )`

#### Explanation
You can also request a password using a "hidden input" display, just like most password boxes on webforms. Alphred does this by creating an AppleScript dialog for you:
````php
$workflow = new Alphred;
$password = $workflow->get_password_dialog();
````
If the user presses `Cancel`, then it will return a value of `canceled`, so make sure you check for that. My assumption is that no one will use the password `canceled`, and, if they do, then they should change it anyway.

By default, the title will be the title of your workflow, and the text will be "Please enter the password". You can change these by passing options to the `get_password_dialog` method.
````php
$password = $workflow->get_password_dialog( 'This is my new title', 'This is the new message prompt.' );
````
You can also add in an icon. Make sure that you use the full path, however. So, if you want to use your workflow's icon:
````php
$icon = realpath( 'icon.png' );
$password = $workflow->get_password_dialog( 'This is my new title', 'This is the new message prompt.', $icon );
````