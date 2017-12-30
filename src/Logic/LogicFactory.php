<?php
namespace Gt\WebEngine\Logic;

use Gt\WebEngine\FileSystem\Path;

class LogicFactory {
	public static function createPageLogicFromPath(
		string $path,
		string $appNamespace,
		string $baseDirectory
	):Page {
		$className = self::getLogicClassFromPath(
			$path,
			$appNamespace,
			"Page",
			$baseDirectory
		);
		return new $className();
	}

	protected static function getLogicClassFromPath(
		string $path,
		string $appNamespace,
		string $logicTypeNamespace,
		string $baseDirectory
	):string {
		$basePageNamespace = implode("\\", [
			$appNamespace,
			$logicTypeNamespace,
		]);

		$logicPathRelative = substr($path, strlen($baseDirectory));
// The relative logic path will be the filename with page directory stripped from the left.
// /app/src/page/index.php => index.php
// /app/src/api/child/directory/thing.php => child/directory/thing.php
		$className = ClassName::transformUriCharacters(
			$logicPathRelative,
			$basePageNamespace,
			$logicTypeNamespace
		);

		return $className;
	}
}