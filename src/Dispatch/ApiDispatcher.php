<?php
namespace Gt\WebEngine\Dispatch;

use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\View\View;

class ApiDispatcher extends Dispatcher {
	protected function getViewModel(string $body):View {
		// TODO: Implement getViewModel() method.
		// Use Object builder to represent JSON.
	}

	protected function getBaseLogicDirectory(string $docRoot):string {
		return Path::getApiDirectory($docRoot);
	}
}