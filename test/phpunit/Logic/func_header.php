<?php
namespace Gt\WebEngine\Logic;

use Gt\WebEngine\Test\Helper\FunctionOverride\Override;

function header(string $header, bool $replace = false, int $code = 303) {
	Override::recordCall(__FUNCTION__, func_get_args());
	return false;
}