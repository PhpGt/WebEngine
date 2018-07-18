<?php
namespace Gt\WebEngine\Logic;

use Gt\WebEngine\Refactor\ObjectDocument;

abstract class Api extends AbstractLogic {
	/** @var ObjectDocument */
	protected $document;

	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct($object) {
		$this->document = $object;

		call_user_func_array(
			"parent::__construct",
			func_get_args()
		);
	}
}