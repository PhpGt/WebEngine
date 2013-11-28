<?php final class FileOrganiser {
/**
 * This class works closely with Manifest and ClientSideCompiler to ensure that
 * all source files are stored ouside of the webroot (www directory), but the
 * compiled or minified versions are copied correctly when required.
 */

private $_wwwDir;
private $_manifestList;

public function __construct($manifestList) {
	// TODO: Got to know about the manifest here!
	$this->_wwwDir = APPROOT . "/www";
	$this->_manifestList = $manifestList;
}
}#