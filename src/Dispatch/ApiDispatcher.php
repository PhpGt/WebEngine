<?php
namespace Gt\WebEngine\Dispatch;

use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\Refactor\ObjectDocument;
use Gt\WebEngine\View\ApiView;
use Gt\WebEngine\View\View;
use Psr\Http\Message\StreamInterface;
use stdClass;

class ApiDispatcher extends Dispatcher {
	protected function getView(
		StreamInterface $outputStream,
		string $body,
		string $templateDirectory,
		string $path = null,
		string $type = null
	):View {
		$object = new ObjectDocument($body, $type);
		return new ApiView($outputStream, $object);
	}
}