<?php final class Manifest {
/**
 * This class works as an extension of the FileOrganiser and ClientSideCompiler
 * classes when a more advanced structure is required in the application.
 *
 * Client-side assets (JavaScript files and Style Sheets) have to be strictly
 * included in *all* HTML HEADs throughout the application, and in the same
 * order. This is so that the browser only needs to cache the assets once, 
 * and more importantly so the server only has to compile/combine/serve the 
 * assets once.
 *
 * For applications that use a single _Header.html file, the Manifest is not at
 * all used. It is when multiple _Header.html files are used that it becomes a
 * requirement of PHP.Gt to use the Manifest object, to avoid having some HEADs
 * different to others.
 *
 * The only outcome of using the Manifest object is a construction of the HEAD
 * sub-elements. 
 *
 * To read more about the usage of the Manifest, read the online docs:
 * https://github.com/g105b/PHP.Gt/wiki/structure~Manifest
 */

public function __construct($domHead) {
	// Search for a meta tag with name of manifest.
	$manifest = null;
	$metaList = $domHead["meta"];
	foreach ($metaList as $meta) {
		if($meta->hasAttribute("name")) {
			if(strtolower($meta->getAttribute("name")) === "manifest") {
				if(!$meta->hasAttribute("content")) {
					throw new HttpError(500, 
						"Manifest meta tag has no content attribute");
				}
				$manifest = $meta->getAttribute("content");
				$meta->remove();
				break;
			}
		}
	}

	if(is_null($manifest)) {
		return;
	}

	$typeArray = array("Script", "Style");
	foreach ($typeArray as $type) {
		$path = APPROOT . "/$type";
		$filePath = "$path/$manifest.manifest";
		if(!file_exists($filePath)) {
			continue;
		}

		$fh = fopen($filePath, "r");
		while(false !== ($line = fgets($fh)) ) {
			// TODO: 103: Include referenced files into head.
		}
		fclose($fh);
	}
}

}#