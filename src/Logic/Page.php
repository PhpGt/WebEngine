<?php
namespace Gt\WebEngine\Logic;

use Gt\DomTemplate\HTMLDocument;

abstract class Page extends AbstractLogic {
	/** @var HTMLDocument */
	protected $document;

	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(HTMLDocument $viewModel) {
		$this->document = $viewModel;
		call_user_func_array(
			"parent::__construct",
			func_get_args()
		);
	}
}