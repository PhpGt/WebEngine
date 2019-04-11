<?php
namespace Gt\WebEngine\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cli\Parameter\NamedParameter;
use Gt\Cli\Parameter\Parameter;

class RunCommand extends Command {
	public function getName():string {
		return "run";
	}

	public function getDescription():string {
		return "Run a local server with build and cron background processes";
	}

	/** @return  NamedParameter[] */
	public function getRequiredNamedParameterList():array {
		return [];
	}

	/** @return  NamedParameter[] */
	public function getOptionalNamedParameterList():array {
		return [];
	}

	/** @return  Parameter[] */
	public function getRequiredParameterList():array {
		return [];
	}

	/** @return  Parameter[] */
	public function getOptionalParameterList():array {
		return [];
	}

	public function run(ArgumentValueList $arguments = null):void {
		$this->writeLine("I AM RUNNING RUN!");
	}
}