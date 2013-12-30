<?php // TestBootstrap.php - loads environment variables required by all tests.
if(!defined("GTROOT")) {
	$cwd = getcwd();
	chdir("..");
	define("GTROOT", getcwd());
	chdir($cwd);
	define("APPROOT", getcwd() . "/TestApp");

	define("URL",		"/");
	define("APPNAME",   "TestApp");
	define("DIR",       "");
	define("BASEDIR",   "");
	define("PATH",		"/");
	define("FILE",      "Index");
	define("EXT",       "html");	
}

require_once(GTROOT . "/Framework/Autoloader.php");
require_once(GTROOT . "/Class/Log/Log.class.php");
require_once(GTROOT . "/Class/Log/Logger.class.php");
require_once(GTROOT . "/Config/Config.cfg.php");
require_once(GTROOT . "/Config/App.cfg.php");
require_once(GTROOT . "/Config/Database.cfg.php");
require_once(GTROOT . "/Config/Security.cfg.php");
require_once(GTROOT . "/Framework/Gt.php");

date_default_timezone_set("UTC");
$gt = new Gt(microtime(), true);

function createTestApp() {
	$source = "TestApp_Base";
	$dir = APPROOT;

	foreach ($iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($source, 
			RecursiveDirectoryIterator::SKIP_DOTS),
	RecursiveIteratorIterator::SELF_FIRST) as $item) {

		$subPathName = $iterator->getSubPathName();
		if($item->isDir()) {
			if(!is_dir("$dir/" . $subPathName)) {
				mkdir("$dir/" . $subPathName, 0775, true);				
			}
		} else {
			if($item->getFilename()[0] == ".") {
				continue;
			}
			copy($item, "$dir/" . $subPathName);
		}
	}
}

function removeTestApp() {
	$dir = APPROOT;
	if(!is_dir($dir)) {
		return;
	}
	
	foreach ($iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir,
			RecursiveDirectoryIterator::SKIP_DOTS),
	RecursiveIteratorIterator::CHILD_FIRST) as $item) {

		$subPathName = $iterator->getSubPathName();
		if($item->isDir()) {
			rmdir("$dir/$subPathName");
		} else {
			unlink("$dir/$subPathName");
		}
	}

	rmdir($dir);
}