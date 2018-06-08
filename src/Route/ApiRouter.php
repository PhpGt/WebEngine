<?php
namespace Gt\WebEngine\Route;

use Gt\WebEngine\FileSystem\Path;

class ApiRouter extends Router {
	const VIEW_EXTENSIONS = ["json", "xml"];

	public function getBaseViewLogicPath():string {
		return Path::getApiDirectory($this->documentRoot);
	}
}