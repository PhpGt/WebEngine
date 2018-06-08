<?php
namespace Gt\WebEngine\Logic;

abstract class Api extends AbstractLogic {
	protected $object;

	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct($object) {
		$this->object = $object;
		call_user_func_array(
			"parent::__construct",
			func_get_args()
		);
	}
}