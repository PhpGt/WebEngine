<?php
namespace Gt\WebEngine\Logic;

use Gt\Routing\Assembly;
use Gt\Routing\LogicStream\LogicStreamNamespace;
use Gt\Routing\LogicStream\LogicStreamWrapper;
use Gt\ServiceContainer\Injector;

class LogicExecutor {
	public function __construct(
		private Assembly $assembly,
		private Injector $injector,
		private string $appNamespace
	) {
		foreach($assembly as $file) {
			$this->loadLogicFile($file);
		}
	}

	public function invoke(string $name):void {
		foreach($this->assembly as $file) {
			$nsProject = (string)(new LogicProjectNamespace(
				$file,
				$this->appNamespace
			));

			$instance = null;

			if(class_exists($nsProject)) {
				$instance = new $nsProject;
			}

			if($instance) {
				if(method_exists($instance, $name)) {
					$this->injector->invoke(
						$instance,
						$name
					);
				}
			}
			else {
				$nsDefault = (string)(new LogicStreamNamespace($file));
				$fqnsDefault = LogicStreamWrapper::NAMESPACE_PREFIX . $nsDefault;
				$fnReferenceArray = [
					"$fqnsDefault\\$name",
					"$nsProject\\$name"
				];

				foreach($fnReferenceArray as $fnReference) {
					if(function_exists($fnReference)) {
						$this->injector->invoke(
							null,
							$fnReference
						);
					}
				}
			}
		}
	}

	private function loadLogicFile(string $file):void {
		$streamPath = LogicStreamWrapper::STREAM_NAME . "://$file";
		require($streamPath);
	}
}
