<?php
namespace Gt\WebEngine\Route;

use Gt\WebEngine\FileSystem\Path;

class PageRouter extends Router {
	const VIEW_EXTENSIONS = ["htm", "html"];

	public function getBaseViewLogicPath():string {
		return Path::getPageDirectory($this->documentRoot);
	}
}