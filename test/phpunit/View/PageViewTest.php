<?php
namespace Gt\WebEngine\Test\View;

use Gt\DomTemplate\HTMLDocument;
use Gt\Http\Stream;
use Gt\WebEngine\View\PageView;
use PHPUnit\Framework\TestCase;

class PageViewTest extends TestCase {
	public function testGetViewModel() {
		$outputStream = self::createMock(Stream::class);
		$viewModel = self::createMock(HTMLDocument::class);
		$sut = new PageView($outputStream, $viewModel);
		self::assertInstanceOf(
			HTMLDocument::class,
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

		$sut = new PageView($outputStream, $viewModel);
		$sut->stream();
	}
}