<?php
namespace Gt\WebEngine;

use Gt\Http\Request;
use Gt\Routing\BaseRouter;
use Gt\Routing\Method\Any;
use Gt\Routing\Method\Get;
use Gt\Routing\Method\Post;
use Gt\Routing\Path\PathMatcher;
use Gt\Routing\Path\DynamicPath;
use Gt\WebEngine\View\BaseView;
use Gt\WebEngine\View\HTMLView;

class DefaultRouter extends BaseRouter {
	#[Any(name: "api-route", accept: "application/json,application/xml")]
	public function api(Request $request, PathMatcher $pathMatcher):void {
		echo "API ROUTE CALLBACK", PHP_EOL;
		foreach($pathMatcher->findForUriPath(
			$request->getUri()->getPath(),
			"api/v1",
			"php"
		) as $logicName => $path) {
			$this->addToLogicAssembly($path);
		}
	}

	#[Get(name: "page-route", accept: "text/html,application/xhtml+xml")]
	public function page(PathMatcher $pathMatcher, Request $request):void {
		$sortNestLevelCallback = fn(string $a, string $b):int =>
			substr_count($a, "/") > substr_count($b, "/")
			? 1
			: -1;
		$sortViewOrder = function(string $a, string $b):int {
			$fileNameA = pathinfo($a, PATHINFO_FILENAME);
			$fileNameB = pathinfo($b, PATHINFO_FILENAME);
			if($fileNameA === "_header") {
				return -1;
			}
			elseif($fileNameA === "_footer") {
				return 1;
			}
			elseif($fileNameB === "_header") {
				return 1;
			}
			elseif($fileNameB === "_footer") {
				return -1;
			}
			return 0;
		};

		$pathMatcher->addFilter([$this, "filterUri"]);

		$matchingLogics = $pathMatcher->findForUriPath(
			$request->getUri()->getPath(),
			"page",
			"php"
		);
		usort($matchingLogics, $sortNestLevelCallback);
		foreach($matchingLogics as $path) {
			$this->addToLogicAssembly($path);
		}

		$matchingViewFilePaths = $pathMatcher->findForUriPath(
			$request->getUri()->getPath(),
			"page",
			"html"
		);
		usort($matchingViewFilePaths, $sortNestLevelCallback);
		usort($matchingViewFilePaths, $sortViewOrder);
		foreach($matchingViewFilePaths as $path) {
			$this->addToViewAssembly($path);
		}

		$this->setViewClass(HTMLView::class);
	}

	#[Post(path: "/greet/@name", function: "greet", accept: "text/plain")]
	public function dynamicText(
		DynamicPath $dynamicPath
	):void {
		$this->addToLogicAssembly("class/Output/Greeter.php");
	}

	public function filterUri(
		string $filePath,
		string $uriPath,
		string $baseDir,
		string $subDir
	):bool {
		$fileName = pathinfo($filePath, PATHINFO_FILENAME);
		$fileDir = pathinfo($filePath, PATHINFO_DIRNAME);
		$filePathNoExt = "$fileDir/$fileName";
		$filePathNoExtNoSubDir = substr($filePathNoExt, strlen($subDir));

		$uriPathExpanded = $uriPath;
		if(substr($uriPath, -1) === "/") {
			$uriPathExpanded .= "index";
		}

		if($filePathNoExtNoSubDir === $uriPathExpanded) {
			return true;
		}

		if($fileName[0] === "_") {
			return true;
		}

		return false;
	}
}
