<?php
/** TODO:
* The purpose of this is to dispatch the events to the PageCode, which is
* stored in the Request, which is stored in the Response, which is passed in
* to this constructor... :S
*/
final class Dispatcher {
	public function __construct($response, $config) {
		$response->dispatch("init");

		$dal = new Dal($config["Database"]->getSettings());
		if($response->dispatch("apiCall", $dal)) {
			// Quit early if request is api call.
			$response->dispatch("apiOutput");
			return;
		}

		$apiWrapper = new ApiWrapper($dal);

		if(count($_POST) > 0) {
			$response->dispatch("onPost", $_POST);
		}

		$getData = $_GET;
		if(array_key_exists("url", $getData)) {
			unset($getData["url"]);
		}
		if(array_key_exists("ext", $getData)) {
			unset($getData["ext"]);
		}

		if(count($getData) > 0) {
			$response->dispatch("onGet", $getData);
		}

		$response->dispatch("main", $apiWrapper);

		$dom = new Dom($response->getBuffer());
		$response->dispatch("preRender", $dom);

		$organiser = new FileOrganiser();
		$injector  = new Injector($dom);

		// TODO: Obtain extracted elements, pass to response.
		$domElements = $dom->scrape();
		$response->dispatch("render", $dom, $domElements, $injector);

		$dom->flush();
	}
}
?>