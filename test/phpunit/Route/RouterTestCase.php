<?php
namespace Gt\WebEngine\Test\Route;

use Gt\WebEngine\Test\WebEngineTestCase;

class RouterTestCase extends WebEngineTestCase {
	public function dataUri():array {
		$data = [];

		for($i = 0; $i < 20; $i++) {
			$params = [];

			$uri = "/";
			if($i > 0) {
				$nesting = rand(1, 10);
				for($n = 0; $n < $nesting; $n++) {
					$uri .= uniqid("nest-") . "/";
				}

				if($i % 10 === 0) {
					$uri .= "index";
				}
			}

			$params []= $uri;
			$data []= $params;
		}

		return $data;
	}
}