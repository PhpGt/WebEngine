<?php
namespace Gt\WebEngine\FileSystem;

class Assembly {
	protected $path;
	protected $extensions;
	protected $order;

	public function __construct(
		string $basePath,
		string $directory,
		array $extensions,
		array $order
	) {
		$this->path = $basePath . $directory;
		$this->extensions = $extensions;
		$this->order = $order;
	}
}