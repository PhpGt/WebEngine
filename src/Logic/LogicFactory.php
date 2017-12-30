<?php
namespace Gt\WebEngine\Logic;

class LogicFactory {
	public static function createPageLogicFromPath(
		string $path,
		string $appNamespace,
		string $baseDirectory
	):Page {
		self::getLogicClassFromPath(
			$path,
			$appNamespace,
			"Page",
			$baseDirectory
		);
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

		$docRoot = Path::getApplicationRootDirectory(dirname($path));
		$pageDirectory = Path::getPageDirectory($docRoot);

		$logicPathRelative = substr($path, strlen($pageDirectory));
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