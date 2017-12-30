<?php
namespace Gt\WebEngine\Dispatch;

use Gt\DomTemplate\HTMLDocument;
use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\Logic\ClassName;
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
		$basePageNamespace = implode("\\", [
			$this->appNamespace,
			"Page",
		]);

		$docRoot = Path::getApplicationRootDirectory(dirname($logicPath));
		$pageDirectory = Path::getPageDirectory($docRoot);

		$logicPathRelative = substr($logicPath, strlen($pageDirectory));
// The relative logic path will be the filename with page directory stripped from the left.
// /app/src/page/index.php => index.php
// /app/src/page/child/directory/thing.php => child/directory/thing.php
		$className = ClassName::transformUriCharacters(
			$logicPathRelative,
			$basePageNamespace,
			"Page"
		);

		return $className;
	}
}