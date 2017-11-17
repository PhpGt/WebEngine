<?php
namespace Gt\WebEngine\Route;

use Gt\WebEngine\FileSystem\Path;

class PageRouter extends Router {
	public function getViewLogicPath():string {
		Path::getPageDirectory();
	}
}