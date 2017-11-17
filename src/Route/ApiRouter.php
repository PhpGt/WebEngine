<?php
namespace Gt\WebEngine\Route;

use Gt\WebEngine\FileSystem\Path;

class ApiRouter extends Router {
	public function getViewLogicPath():string {
		Path::getApiDirectory();
	}
}