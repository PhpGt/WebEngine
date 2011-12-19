<?php
/**
 * TODO: Docs.
 * Simply uses rsync to ensure web root contains necessary files.
 * TODO: What about Windows? (test)
 * TODO: What about overriding Gt files with App files? (test)
 */
final class FileOrganiser {
	public function __construct() {
		$dirArray = array(
			GTROOT  . DS . "Style",
			GTROOT  . DS . "Script",
			APPROOT . DS . "Style",
			APPROOT . DS . "Script"
		);
		$rsyncCommand = "rsync --recursive --update --delete ";
		foreach($dirArray as $dir) {
			$rsyncCommand .= $dir . " ";
		}
		$rsyncCommand .= " " . APPROOT . DS . "Web";

		exec($rsyncCommand);
	}
}
?>