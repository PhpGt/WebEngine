<?php // TestBootstrap.php - loads environment variables required by all tests.
define("GTROOT", getcwd() . "/../");


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
			copy($item, "$dir/" . $subPathName);
		}
	}
}

function removeTestApp() {
	$dir = APPROOT;
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