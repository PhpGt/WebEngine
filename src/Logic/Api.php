<?php
namespace Gt\WebEngine\Logic;

use Gt\WebEngine\Refactor\ObjectDocument;

abstract class Api extends AbstractLogic {
	/** @var ObjectDocument */
	protected $document;

	/** @param $objectDocument ObjectDocument */
	public function __construct($objectDocument) {
		$this->document = $objectDocument;

		parent::__construct(
			...func_get_args()
		);
	}
}