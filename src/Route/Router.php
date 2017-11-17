<?php
namespace Gt\WebEngine\Route;

use Gt\Http\Uri;
use Psr\Http\Message\UriInterface;

class Router {
	public function getCorrectedUri(UriInterface $requestedUri):UriInterface {
		// TODO: Build up the corrected URI according to the path, etc.
		return new Uri("/");
	}
}