<?php class Navigation_PageTool extends PageTool {
/**
 * Used to automate an application or website's navigation system.
 * Adds a class of "selected" to the relevant "body nav li".
 */
private $_navElements;

public $selector = "body nav, body menu";

public function go($api, $dom, $template, $tool) {
	$this->_navElements = $dom[$this->selector];

	$target = strtok($_SERVER['REQUEST_URI'], '?');
	$target = strtok($target, '#');
	$target = strtok($target, ".");
	$targetBase = strtok($target, "/");
	
	$target = str_replace("/", "\/", $target);
	$targetBase = str_replace("/", "\/", $targetBase);

	$selected = false;

	foreach($this->_navElements as $nav) {
		$navLiTags = $nav["li"];

		foreach($navLiTags as $li) {
			$pattern = $patternBase = null;

			if($li->hasAttribute("data-selected-pattern")) {
				$pattern = $li->getAttribute("data-selected-pattern");

				if(preg_match($pattern, $_SERVER['REQUEST_URI'])) {
					$li->addClass("selected");
					if($li->hasClass("tree")) {
						$li->addClass("open");
					}
					$selected = true;
				}
				continue;
			}
			else {
				$pattern = "/$target(.html)?/";
				$patternBase = "/$targetBase(.html)?/";
			}

			// Match the current URL with the anchor's href.
			$href = $li["a"]->getAttribute("href");
			if(preg_match($pattern, $href) > 0
			|| ($href === "/" && $target === "\/Index") ) {
				$li->addClass("selected");
				$selected = true;
				if($li->hasClass("tree")) {
					$li->addClass("open");
				}
			}
			else if(preg_match($patternBase, $href) > 0) {
				$li->addClass("selected");
				$selected = true;
				if($li->hasClass("tree")) {
					$li->addClass("open");
				}
			}
		}
	}

	return $selected;
}

}#