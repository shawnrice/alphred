<?php

require_once( '../Main.php' );

class MyTestClass {

	public static function test_method() {
		return __METHOD__;
	}

}

print MyTestClass::test_method();
print "\n";

exit(0);


