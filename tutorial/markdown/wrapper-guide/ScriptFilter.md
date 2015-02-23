
Script Filter Options
* arg
* autocomplete
* icon
* icon_fileicon
* icon_filetype
* subtitle
* subtitle_alt
* subtitle_cmd
* subtitle_ctrl
* subtitle_fn
* subtitle_shift
* text_copy
* text_largetype
* title
* uid
* valid

`valid` must be either `true` or `false`, but all of the others must be strings.

You pass them as an array:
````php
$workflow = new Alphred;
$workflow->add_result([
	'arg' => 'this is an argument to be passed on',
	'autocomplete' => 'when I press tab, this appears',
	'icon' => '/path/to/icon',
	'icon_fileicon' => 'info.plist',
	'icon_filetype' => 'php',
	'subtitle' => 'This is a subtitle',
	'subtitle_alt' => 'This is an alternate subtitle',
	'subtitle_cmd' => 'This is the command subtitle',
	'subtitle_ctrl' => 'This is the control subtitle',
	'subtitle_fn' => 'This is the function subtitle',
	'subtitle_shift' => 'This is the shift subtitle',
	'text_copy' => 'This is the text when you copy',
	'text_largetype' => 'This text will be shown for large type',
	'title' => 'This is the title',
	'uid' => 'This is the uid',
	'valid' => true,
]);
````
Now, that might not work out so well because you have some conflicting information between `icon`, `icon_fileicon`, and `icon_filetype`. Also, the alternate subtitles (when you press the modifier keys) might not work unless the script filter is attached to an action that has those modifier keys.