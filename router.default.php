<?php
namespace Gt\WebEngine;

use Gt\Http\Request;
use Gt\Routing\BaseRouter;
use Gt\Routing\Method\Any;
use Gt\Routing\Method\Get;
use Gt\Routing\Method\Post;
use Gt\Routing\Path\FileMatch\BasicFileMatch;
use Gt\Routing\Path\FileMatch\MagicFileMatch;
use Gt\Routing\Path\PathMatcher;
use Gt\Routing\Path\DynamicPath;
use Gt\WebEngine\View\BaseView;
use Gt\WebEngine\View\HTMLView;

class DefaultRouter extends BaseRouter {
	#[Any(name: "page-route", accept: "text/html,application/xhtml+xml")]
	public function page(Request $request):void {
		$pathMatcher = new PathMatcher("page");
		$this->setViewClass(HTMLView::class);
		$pathMatcher->addFilter(function(string $filePath, string $uriPath, string $baseDir):bool {
// There are three types of matching files: Basic, Magic and Dynamic.
// Basic is where a URI matches directly to a file on disk.
// Magic is where a URI matches a PHP.Gt-specific file, like _common or _header.
// Dynamic is where a URI matches a file/directory marked as dynamic with "@".
			$basicFileMatch = new BasicFileMatch($filePath, $baseDir);
			if($basicFileMatch->matches($uriPath)) {
				return true;
			}

			$magicFileMatch = new MagicFileMatch($filePath, $baseDir);
			if($magicFileMatch->matches($uriPath)) {
				return true;
			}

			return false;
		});
		// TODO: add logic and view assembly in the api directory
		// (configured from $this->routerConfig)

		$sortNestLevelCallback = fn(string $a, string $b) =>
			substr_count($a, "/") > substr_count($b, "/");
		$headerSort = fn(string $a, string $b) =>
		strtok(basename($a), ".") === "_header" ? -1 : 1;
		$footerSort = fn(string $a, string $b) =>
		strtok(basename($a), ".") === "_footer" ? 1 : -1;

		$matchingLogics = $pathMatcher->findForUriPath(
			$request->getUri()->getPath(),
			"page",
			"php"
		);
		usort($matchingLogics, $sortNestLevelCallback);
		foreach($matchingLogics as $path) {
			$this->addToLogicAssembly($path);
		}

		$matchingViews = $pathMatcher->findForUriPath(
			$request->getUri()->getPath(),
			"page",
			"html"
		);
		usort($matchingViews, $sortNestLevelCallback);
		usort($matchingViews, $headerSort);
		usort($matchingViews, $footerSort);

		foreach($matchingViews as $path) {
			$this->addToViewAssembly($path);
		}
	}
}
