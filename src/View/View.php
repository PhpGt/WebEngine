<?php
namespace Gt\WebEngine\View;

abstract class View {
	protected $viewModel;

	public function __construct($viewModel) {
		$this->viewModel = $viewModel;
	}
}