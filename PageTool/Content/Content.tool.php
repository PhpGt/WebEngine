<?php class Content_PageTool extends PageTool {
/**
 * TODO: Docs.
 * Editable content blocks in web pages.
 */
private $_elements = null;

public function go($api, $dom, $template, $tool) {
	$this->_elements = $dom["*[@data-editable]"];
	foreach ($this->_elements as $element) {
		$content = $api[$this]->get(array("Name" => $element->id));
		if(empty($content["L_Type"])) {
			continue;
		}
		switch($content["L_Type"]) {
		case "TextPlain":
			if(!empty($content["Value"])) {
				$element->text = $content["Value"];
			}
			break;
		case "Text":
		case "TextTitle":
		case "TextRich":
			if(!empty($content["Value"])) {
				$element->html = $content["Value"];
			}
			break;
		case "Image":
			if(!empty($content["Value"])) {
				$element->setAttribute("src", $content["Value"]);
			}
			break;
		default: 
			break;
		}
	}
}

}#