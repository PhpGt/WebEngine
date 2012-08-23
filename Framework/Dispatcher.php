<?php final class Dispatcher {
/** 
* The dispatcher is used to link the Response, Request and PageCode objects, 
* and call all related events in the correct order. The main aim of dispatching
* events like this is to only pass around required data, so objects only have
* access to exactly what they need.
*/
public function __construct($response, $config) {
	$dal = new Dal($config["Database"]->getSettings());
	if($response->dispatch("apiCall", $dal)) {
		// Quit early if request is api call.
		$response->dispatch("apiOutput");
		return;
	}

	// On root URLs, the query string may be used as the "url" key.
	// Ensure the $_GET variable is consistant across different webservers,
	// also, remove the GET parameters that are used by PHP.Gt's internals.
	$mapDataArr = array(&$_GET, &$_REQUEST);
	foreach($mapDataArr as &$mapData) {
		$data = $mapData;
		if(array_key_exists("url", $data)) {
			if(strstr($data["url"], "?")) {
				$keyValuePair = substr($data["url"],
					strpos($data["url"], "?") + 1);
				
				$keyValuePair = explode("=", $keyValuePair);
				if(empty($keyValuePair[1])) {
					$data[$keyValuePair[0]] = null;
				}
				else {
					$data[$keyValuePair[0]] = $keyValuePair[1];
				}
			}
			unset($data["url"]);
		}
		if(array_key_exists("ext", $data)) {
			unset($data["ext"]);
		}
		$mapData = $data;
	}

	// Start building the objects used across the PageCodes...

	// Load the DOM from the current buffer, include any externally linked
	// PageViews from <include> tags. 
	$dom = new Dom($response->getBuffer());
	$response->includeDom($dom);

	// Compile and inject <script> and <link> tags, organise the contents
	// of the Asset, Style, Script directories to be accessible through
	// the web root.
	$isCompiled = $config["App"]->isClientCompiled();
	$injector  = new Injector($dom, $isCompiled);
	$organiser = new FileOrganiser($config["App"]);

	// Create the wrapper classes for easy access to components.
	$apiWrapper = new ApiWrapper($dal);
	$templateArray = $dom->template();
	$templateWrapper = new TemplateWrapper($templateArray);
	$toolWrapper = new PageToolWrapper($apiWrapper, $dom, $templateWrapper);

	// Allows the PageCode objects to have access to the important
	// dependency injector variables internally.
	$response->dispatch(
		"setVars",
		$apiWrapper,
		$dom,
		$templateWrapper,
		$toolWrapper);

	// Dispatch the all important "go" event, that is the entry point to
	// each PageCode, and has access to all required components.
	$response->dispatch(
		"go",
		$apiWrapper,
		$dom,
		$templateWrapper,
		$toolWrapper);

	$dom->templateOutput($templateWrapper);
	$dom->flush();
}

}?>