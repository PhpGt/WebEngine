<?php
/**
 * TODO: Docs.
 * Simply uses rsync to ensure web root contains necessary files.
 * TODO: What about Windows? (test)
 * TODO: What about overriding Gt files with App files? (test)
 */
final class FileOrganiser {
	public function __construct() {
		/**
		TODO: BUG - Fix this. Directories are not coppied as needed.
		TODO: rsync needs to retain permissions (there's a flag for that).
		$dirArray = array(
			GTROOT  . DS . "Style" . DS . "Img",
			GTROOT  . DS . "Style" . DS . "Font",
			APPROOT . DS . "Style" . DS . "Img",
			APPROOT . DS . "Style" . DS . "Font"			
		);
		$rsyncCommand = "rsync --recursive --update --delete ";
		foreach($dirArray as $dir) {
			$rsyncCommand .= $dir . " ";
		}
		$rsyncCommand .= " " . APPROOT . DS . "Web";

		exec($rsyncCommand);
		**/
	}
}
?>