<?php class Session {
/**
 * Rather than storing everything in its own key within the $_SESSION array,
 * which could cause key-collisions, PHP.Gt uses this Session object as a 
 * wrapper to the $_SESSION variable to encapsulate variables within their
 * own hierarchy - for example, all session data that is stored by the User
 * PageTool will be stored in $_SESSION["PhpGt"]["PageTool"]["User"], but this
 * nested array will need each key initialising before they are attempted to be
 * get/set, otherwise warnings will be triggered.
 *
 * To use, call the static methods set and get:
 * Session::set("name.space", $data); 
 * $data = Session::get("name.space");
 *
 * The namespace can either be a period-delimited list of array keys or an array
 * of keys (i.e. ["name", "space"]).
 */

public static function get($ns) {
	$nsArray = self::getNsArray($ns);
	return self::getRecursive($_SESSION, $nsArray);
}

public static function set($ns, $data) {
	$nsArray = self::getNsArray($ns);

	self::init($_SESSION, $nsArray);
	return self::setData($_SESSION, $nsArray, $data);

}

private static function setData(&$ns, $nsArray, $data) {
	if(count($nsArray) === 1) {
		if(is_array($data)) {
			$ns[$nsArray[0]] = array_merge($ns[$nsArray[0]], $data);
		}
		else {
			$ns[$nsArray[0]] = $data;			
		}
		return $data;
	}

	$nsKey = array_shift($nsArray);
	return self::setData($ns[$nsKey], $nsArray, $data);
}

private static function getNsArray($ns) {
	$nsArray = array();
	if(is_string($ns)) {
		$nsArray = explode(".", $ns);
	}
	else if(is_array($ns)) {
		$nsArray = $ns;
	}
	else {
		throw new HttpError(500, "Session namespace type error.");
		exit;
	}

	return $nsArray;
}

private static function getRecursive(&$arrayContainer, $nsArray, $value = null){
	if(empty($nsArray)) {
		return $value;
	}

	$getKey = array_shift($nsArray);

	if(!isset($arrayContainer[$getKey])) {
		return null;
	}

	return self::getRecursive(
		$arrayContainer[$getKey],
		$nsArray,
		$arrayContainer[$getKey]
	);
}

/**
 * Initialises a nested array, returns reference to the deepest (the leaf).
 */
private static function init(&$arrayContainer, $nsToInit, &$leaf = null) {
	if(empty($nsToInit)) {
		return $leaf;
	}

	$initKey = array_shift($nsToInit);

	if(!isset($arrayContainer[$initKey])) {
		$arrayContainer[$initKey] = array();
	}

	return self::init(
		$arrayContainer[$initKey],
		$nsToInit,
		$arrayContainer[$initKey]
	);
}
}#