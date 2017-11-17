<?php
namespace Gt\WebEngine\Route;

use Gt\Http\Uri;
use Psr\Http\Message\UriInterface;

abstract class Router {
	abstract public function getViewLogicPath():string;
}