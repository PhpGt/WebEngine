<?php
namespace Gt\WebEngine\Logic;

class ClassName {
	public static function transformUriCharacters(
		string $uri,
		string $prefix,
		string $suffix
	):string {
		$uri = $prefix . $uri;

		$uri = strtok($uri, ".");
		$uri = trim($uri, "/");
		$uri = str_replace("/", "\\", $uri);
		$uri = ucwords($uri, "\\-");
		$uri = str_replace("-", "", $uri);

		$uri .= $suffix;
		$uri = ltrim($uri, "\\");
		$uri = "\\$uri";

		return $uri;
	}
}