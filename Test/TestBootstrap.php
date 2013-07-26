<?php // TestBootstrap.php - loads environment variables required by all tests.
define("GTROOT", getcwd() . "/../");

function createTestApp() {
	$source = "TestApp_Base";
	$dest = "TestApp";

	foreach ($iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator(
			$source, RecursiveDirectoryIterator::SKIP_DOTS),
	RecursiveIteratorIterator::SELF_FIRST) as $item	) {

		if ($item->isDir()) {
			mkdir("$dest/" . $iterator->getSubPathName());
		} else {
			copy($item, "$dest/" . $iterator->getSubPathName());
		}
	}
}