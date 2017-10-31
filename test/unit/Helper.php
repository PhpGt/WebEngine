<?php
namespace Gt\Test;

class Helper {
	const TMP_PREFIX = "phpgt-webengine";

	/**
	 * Provides the absolute path to a directory that is unique to the test case.
	 */
	public static function getTempDirectory():string {
		$tmp = sys_get_temp_dir();
		$unique = uniqid(self::TMP_PREFIX, true);
		return "$tmp/$unique";
	}
}