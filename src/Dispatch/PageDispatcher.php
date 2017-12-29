<?php
namespace Gt\WebEngine\Dispatch;

use Gt\DomTemplate\HTMLDocument;
use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\View\PageView;
use Gt\WebEngine\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PageDispatcher extends Dispatcher {
	protected function getViewModel(string $body):View {
		if(strlen($body) === 0) {

		}
		$document = new HTMLDocument($body);
		$document->extractTemplates();
		$document->expandComponents();
		$view = new PageView($document);
		return $view;
	}

	protected function getLogicClassFromFilePath(string $logicPath):string {
		var_dump($logicPath);die("This is the logic path to look for a class for");
		$appRoot = Path::getApplicationRootDirectory(dirname($logicPath));
		$classDir = Path::getClassDirectory($appRoot);

		var_dump($logicPath, $classDir);die();
	}
}