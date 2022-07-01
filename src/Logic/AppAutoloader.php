<?php /** @noinspection PhpIncludeInspection */
namespace Gt\WebEngine\Logic;

class AppAutoloader {
	public function __construct(
		private string $namespace,
		private string $classDir,
	) {
	}

	public function setup():void {
		if(!is_dir($this->classDir)) {
			return;
		}

		spl_autoload_register(fn(string $className) => $this->autoload($className));
	}

	private function autoload(string $className):void {
		if(!str_starts_with($className, $this->namespace . "\\")) {
			return;
		}

		$classNameWithoutAppNamespace = substr(
			$className,
			strlen($this->namespace) + 1
		);

		$phpFilePath = "./" . $this->classDir;
		foreach(explode("\\", $classNameWithoutAppNamespace) as $classPart) {
			$phpFilePath .= "/";
			$phpFilePath .= ucfirst($classPart);
		}

		$phpFilePath .= ".php";
		if(is_file($phpFilePath)) {
			require($phpFilePath);
		}
	}
}
