<?php
namespace Gt\WebEngine\Privacy;

class Protection {
	public static function deregisterGlobals() {
		foreach($GLOBALS as $globalKey => $globalValue) {
			if(is_array($globalValue)) {
				foreach($globalValue as $key => $value) {
					unset($GLOBALS[$globalKey][$key]);
				}
			}
			else {
				unset($GLOBALS[$globalKey]);
			}
		}
		unset($GLOBALS);
	}

	public static function overrideGlobals() {
		$_SERVER =
		$_GET =
		$_POST =
		$_FILES =
		$_COOKIE =
		$_SESSION =
		$_REQUEST =
		$_ENV =
			new GlobalStub();
	}
}