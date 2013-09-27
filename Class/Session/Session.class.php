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
 * The namespace can either be a period-delimited list of array keys, an array
 * of keys (i.e. ["name", "space"]) or an object. Passing an object will attempt
 * to create the correct namespace according to given object's type.
 */

public static function get($ns) {
	
}

public static function set($ns, $data) {

}
}#