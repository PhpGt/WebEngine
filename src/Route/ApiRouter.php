<?php
namespace Gt\WebEngine\Route;

use Gt\WebEngine\FileSystem\Path;

class ApiRouter extends Router {
	public function getBaseViewLogicPath():string {
		return Path::getApiDirectory();
	}
}