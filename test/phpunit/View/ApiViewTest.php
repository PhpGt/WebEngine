<?php
namespace Gt\WebEngine\Test\View;

use Gt\DomTemplate\HTMLDocument;
use Gt\Http\Stream;
use Gt\WebEngine\Refactor\ObjectDocument;
use Gt\WebEngine\View\ApiView;
use PHPUnit\Framework\TestCase;

class ApiViewTest extends TestCase {
	public function testGetViewModel() {
		$outputStream = self::createMock(Stream::class);
		$viewModel = self::createMock(ObjectDocument::class);
		$sut = new ApiView($outputStream, $viewModel);
		self::assertInstanceOf(
			ObjectDocument::class,
			$sut->getViewModel()
		);
	}

	public function testStream() {
		$exampleViewModelString = uniqid("view-model-");

		$outputStream = self::createMock(Stream::class);
		$outputStream->expects(self::once())
			->method("write")
			->with($exampleViewModelString);

		$viewModel = self::createMock(HTMLDocument::class);
		$viewModel->expects(self::once())
			->method("__toString")
			->willReturn($exampleViewModelString);

		$sut = new ApiView($outputStream, $viewModel);
		$sut->stream();
	}
}