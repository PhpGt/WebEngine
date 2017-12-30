<?php
namespace Gt\WebEngine\Dispatch;

use Gt\DomTemplate\HTMLDocument;
use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\View\PageView;
use Gt\WebEngine\View\View;
use Psr\Http\Message\StreamInterface;

class PageDispatcher extends Dispatcher {
	protected function getView(StreamInterface $outputStream, string $body):View {
		$document = new HTMLDocument($body);
		$document->extractTemplates();
		$document->expandComponents();
		$view = new PageView($outputStream, $document);
		return $view;
	}

	protected function getBaseLogicDirectory(string $docRoot):string {
		return Path::getPageDirectory($docRoot);
	}
}