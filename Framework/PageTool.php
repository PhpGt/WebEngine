<?php abstract class PageTool {
/**
 * PageTool objects operate in a similar way to PageCode objects. PageTools can
 * be seen as groupings of reusable code, that is made accessible to all
 * PageCode. 
 *
 * A PageTool is usually created when code within a PageCode becomes required
 * elsewhere in an application. This code is then packaged into a tool, that
 * serves a particular purpose.
 *
 * Common tools are provided with PHP.Gt, but application specific tools can
 * be used to keep the PageCode clean.
 */
protected $_name = null;
protected $_api = null;
protected $_dom = null;
protected $_template = null;
protected $_tool = null;

public function __construct($api, $dom, $template, $tool) {
	$className = get_called_class();
	$this->_name = substr($className, 0, strrpos($className, "_PageTool"));
	$this->_api = $api;
	$this->_dom = $dom;
	$this->_template = $template;
	$this->_tool = $tool;
}

/**
 * Injects any client side files used by the current tool into the DOM.
 */
public function clientSide() {
	$scriptDirArray = array(
		APPROOT . "/PageTool/{$this->_name}/Script/",
		GTROOT  . "/PageTool/{$this->_name}/Script/"
	);
	$styleDirArray = array(
		APPROOT . "/PageTool/{$this->_name}/Style/",
		GTROOT  . "/PageTool/{$this->_name}/Style/"
	);

	$domHead = $this->_dom["html > head"];
	$ptDir = "PageTool/{$this->_name}/";
	$wwwDir = APPROOT . "/www/$ptDir";

	foreach ($scriptDirArray as $scriptDir) {
		if(!is_dir($scriptDir)) {
			continue;
		}

		$dir = dir($scriptDir);
		while(false !== ($file = $dir->read()) ) {
			if($file[0] == ".") {
				continue;
			}
			$fullPath = $scriptDir . $file;
			$scriptDir = $wwwDir . "Script";
			$dest =  $scriptDir . "/$file";
			if(!is_dir($scriptDir)) {
				mkdir($scriptDir, 0775, true);
			}
			copy($fullPath, $wwwDir . "Script/$file");
			$domHead->append("script", [
				"src" => "/{$ptDir}Script/$file"
			]);
		}
	}

	foreach ($styleDirArray as $styleDir) {
		if(!is_dir($styleDir)) {
			continue;
		}

		$dir = dir($styleDir);
		while(false !== ($file = $dir->read()) ) {
			if($file[0] == ".") {
				continue;
			}
			$fullPath = $styleDir . $file;
			$styleDir = $wwwDir . "Style";
			$dest = $styleDir . "/$file";
			if(!is_dir($styleDir)) {
				mkdir($styleDir, 0775, true);
			}
			$fileContents = file_get_contents($fullPath);

			if(pathinfo($fullPath, PATHINFO_EXTENSION) == "scss") {
				$dest = preg_replace("/\.scss$/", ".css", $dest);
				$file = preg_replace("/\.scss$/", ".css", $file);
				$sass = new Sass($fullPath);
				$fileContents = $sass->parse();
			}

			if(false === file_put_contents($dest, $fileContents)) {
				die("OOPS!!!");
			}
			$domHead->append("link", [
				"rel" => "stylesheet", 
				"href" => "/{$ptDir}Style/$file"
			]);
		}
	}
}

/**
 * Works in the same way that PageCode's go() function does.
 * @param ApiWrapper $api Used exactly like the $api variable from within
 * PageCode, but with access to this tool's TableCollections.
 * @param Dom $dom An extended DomDocument, providing helpful functions and
 * most notably element CSS selection. Any manipulation that is made to the
 * DOM will be sent to the browser.
 * @param array $template An associative array containing all DOM elements
 * that have been scraped out of the DOM with data-template attributes.
 * Each element keeps its designed form from how it appears in the HMTL.
 * @param ToolWrapper $tool The object that acts as a single entry point
 * to all PageTools. Can activate a PageTool by calling it through this
 * Associative array.
 */
abstract protected function go($api, $dom, $template, $tool);

}?>