<?php final class Dispatcher {
/** 
* The dispatcher is used to link the Response, Request and PageCode objects, 
* and call all related events in the correct order. The main aim of dispatching
* events like this is to only pass around required data, so objects only have
* access to exactly what they need.
*/
public function __construct($response, $config) {
	$dal = new Dal($config["Database"]::getSettings());
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
	
	$apiWrapper = new ApiWrapper($dal);
	$emptyObject = new EmptyObject();
	
	if($response->mtimeView === false) {
		// There is no PageView! Allow PageCode's go function to be invoked,
		// but there's no need to pass in any Dom, Template or Tool.
		$pageToolWrapper = new PageToolWrapper(
			$apiWrapper, $emptyObject, $emptyObject);
		$response->dispatch(
			"setVars",
			$apiWrapper,
			$emptyObject,
			$emptyObject,
			$pageToolWrapper);
		$response->dispatch("go",
			$apiWrapper,
			$emptyObject,
			$emptyObject,
			$pageToolWrapper);
		throw new HttpError(404);
	}


	// Load the DOM from the current buffer, include any externally linked
	// PageViews from <include> tags. 
	$dom = new Dom($response->getBuffer());
	$response->addMetaData($dom);

	// Remove any elements in the incorrect language.
	$dom->languageScrape();

	// Create the wrapper classes for easy access to components.
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
		$toolWrapper,
		$config["App"]);

	// Dispatch the all important "go" event, that is the entry point to
	// each PageCode, and has access to all required components.
	$response->dispatch(
		"go",
		$apiWrapper,
		$dom,
		$templateWrapper,
		$toolWrapper);
	$response->dispatch(
		"endGo",
		$apiWrapper,
		$dom,
		$templateWrapper,
		$toolWrapper);

	$fileOrganiser = new FileOrganiser();
	$cacheInvalid = $fileOrganiser->checkFiles();
	if($cacheInvalid) {
		$clientSideCompiler = new ClientSideCompiler();
		$fileOrganiser->clean();
		$fileOrganiser->update($dom["head > *[data-pagetool]"]);
		$fileOrganiser->process($clientSideCompiler);
		$fileOrganiser->compile($clientSideCompiler);
	}

	$dom->templateOutput($templateWrapper);
	return $dom->flush();
}

}#