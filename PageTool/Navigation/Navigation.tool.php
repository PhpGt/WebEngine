<?php class Navigation_PageTool extends PageTool {
/**
 * Used to automate an application or website's navigation system.
 * Adds a class of "selected" to the relevant "body nav li".
 */
private $_navElements;

public function go($api, $dom, $template, $tool) {
	$this->_navElements = $dom["body nav"];

	$target = strtok($_SERVER['REQUEST_URI'], '?');
	$target = strtok($target, '#');
	$target = str_replace("/", "\/", $target);
	$target = str_replace(".", "\.", $target);

	foreach($this->_navElements as $nav) {
		$navLiTags = $nav["ul li, ol li"];

		foreach($navLiTags as $li) {
			if($li->hasAttribute("data-selected-pattern")) {
				$pattern = $li->getAttribute("data-selected-pattern");

			}
			else {
				$pattern = "/$target/";
			}
			
			// Match the current URL with the anchor's href.
			$href = $li["a"]->getAttribute("href");
			if(preg_match($pattern, $href) > 0
			|| ($href === "/" && $target === "\/Index\.html") ) {
				$li->addClass("selected");
				if($li->hasClass("tree")) {
					$li->addClass("open");
				}
			}
		}
	}
}

}#