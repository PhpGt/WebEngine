<?php
namespace Gt\WebEngine\Dispatch;

use Gt\Csrf\HTMLDocumentProtector;
use Gt\DomTemplate\HTMLDocument;
use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\View\PageView;
use Gt\WebEngine\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class PageDispatcher extends Dispatcher {
	protected function getView(
		StreamInterface $outputStream,
		string $body,
		string $templateDirectory,
		string $path = null,
		string $type = null
	):View {
		$document = new HTMLDocument(
			$body,
			$templateDirectory
		);
		$document->expandComponents();
		$document->extractTemplates();

		if(!is_null($path)) {
			$pathHyphens = str_replace("/", "-", $path);
			$document->body->classList->add("uri-$pathHyphens");

			$dirParts = "";
			$pathParts = explode("/", $path);
			foreach($pathParts as $pathPart) {
				if(empty($pathPart)) {
					continue;
				}

				$dirParts .= "-$pathPart";
				$document->body->classList->add("dir-$dirParts");
			}
		}

		$view = new PageView($outputStream, $document);
		return $view;
	}

	protected function getBaseLogicDirectory(string $docRoot):string {
die("I THINK THIS METHOD IS DEAD.");
		return Path::getPageDirectory($docRoot);
	}
}