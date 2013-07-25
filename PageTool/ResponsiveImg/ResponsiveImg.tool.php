<?php class ResponsiveImg_PageTool extends PageTool {
/**
 * Responsive Image serving.
 * This PageTool modifies the src attribute of img tags in the page to serve
 * alternative images for smaller-screened devices.
 * 
 * 1) Img tags require the class "responsive" to be present in order to be
 * picked up.
 * 2) Call the go method on this tool: $tool->go("ResponsiveImg");
 * 3) Place lower-resolution images as siblings to the original image file,
 * with the following filename format: `OriginalName.responsive-XXX.jpg` where
 * XXX is the maximum resolution (width) the image will be served at.
 *
 * Devices will be served the largest available file, up to the width of their
 * screen, if the file is present on the server.
 */

private $_pattern = ".responsive-*.";
private $_includedJS = false;

public function go($api, $dom, $template, $tool) {
	parent::clientSide();
	if(!isset($_COOKIE["Tool_ResponsiveImg"])) {
		return;
	}

	$imgList = $dom["img.responsive"];
	foreach ($imgList as $img) {
		$path = APPROOT . $img->src;
		if(!file_exists($path)) {
			continue;
		}

		$pathinfo = pathinfo($path);
		
		$filePattern = $pathinfo["dirname"]
			. "/"
			. $pathinfo["filename"]
			. $this->_pattern
			. $pathinfo["extension"];
		$fileToUse = null;
		$fileMatchList = glob($filePattern);
		$fileMatchList = array_reverse($fileMatchList);
		$sizeOriginal = getimagesize($path);
		$sizes = array($sizeOriginal[0]);

		foreach ($fileMatchList as $file) {
			$matches = array();
			preg_match("/\.responsive-([0-9]+)\./", $file, $matches);
			$size = $matches[1];
			array_unshift($sizes, $size);
			if($size > $_COOKIE["Tool_ResponsiveImg"]) {
				$fileToUse = $file;
			}
		}

		if(!is_null($fileToUse)) {
			$fileToUse = str_replace(APPROOT, "", $fileToUse);
			$img->setAttribute("data-responsive-src", $img->src);
			$img->setAttribute("data-responsive-sizes", implode(",", $sizes));
			$img->src = $fileToUse;
		}
	}
}

/**
 * This function will automatically scale original images on the server and 
 * save the scaled versions as siblings to the original image file. If the
 * scaled file already exists, it will ignore the scaling for that file.
 *
 * @param $img DomElCollection One or more DomEl refering to <img> tags.
 * @param $size int|array      The size or sizes to scale down to.
 */
public function autoScale($img, $size) {
	// NOT YET IMPLEMENTED.
}

}#