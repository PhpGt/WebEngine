<?php /** @noinspection PhpUnused */
namespace Gt\WebEngine\Middleware;

use Gt\Config\Config;
use Gt\Database\Connection\DefaultSettings;
use Gt\Database\Connection\Settings;
use Gt\Database\Database;
use Gt\Dom\Document;
use Gt\DomTemplate\DocumentBinder;
use Gt\DomTemplate\ElementBinder;
use Gt\DomTemplate\HTMLAttributeBinder;
use Gt\DomTemplate\HTMLAttributeCollection;
use Gt\DomTemplate\ListBinder;
use Gt\DomTemplate\PlaceholderBinder;
use Gt\DomTemplate\TableBinder;
use Gt\DomTemplate\TemplateCollection;
use Gt\Http\Uri;
use Gt\ServiceContainer\Container;
use Gt\ServiceContainer\LazyLoad;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class DefaultServiceLoader {
	public function __construct(
		protected Config $config,
		protected RequestInterface $request,
		protected Container $container
	) {
	}

	#[LazyLoad]
	public function loadDatabase():Database {
		$dbSettings = new Settings(
			$this->config->get("database.query_directory"),
			$this->config->get("database.driver"),
			$this->config->get("database.schema"),
			$this->config->get("database.host"),
			$this->config->get("database.port"),
			$this->config->get("database.username"),
			$this->config->get("database.password"),
			$this->config->get("database.connection_name") ?: DefaultSettings::DEFAULT_NAME,
			$this->config->get("database.collation") ?: DefaultSettings::DEFAULT_COLLATION,
			$this->config->get("database.charset") ?: DefaultSettings::DEFAULT_CHARSET,
		);
		return new Database($dbSettings);
	}

	#[LazyLoad]
	public function loadHTMLAttributeBinder():HTMLAttributeBinder {
		return new HTMLAttributeBinder();
	}

	#[LazyLoad]
	public function loadHTMLAttributeCollection():HTMLAttributeCollection {
		return new HTMLAttributeCollection();
	}

	#[LazyLoad]
	public function loadPlaceholderBinder():PlaceholderBinder {
		return new PlaceholderBinder();
	}

	#[LazyLoad]
	public function loadElementBinder():ElementBinder {
		return new ElementBinder(
			$this->container->get(HTMLAttributeBinder::class),
			$this->container->get(HTMLAttributeCollection::class),
			$this->container->get(PlaceholderBinder::class),
		);
	}

	#[LazyLoad]
	public function loadTableBinder():TableBinder {
		return new TableBinder(
			$this->container->get(TemplateCollection::class),
			$this->container->get(ElementBinder::class),
			$this->container->get(HTMLAttributeBinder::class),
			$this->container->get(HTMLAttributeCollection::class),
			$this->container->get(PlaceholderBinder::class),
		);
	}

	#[LazyLoad]
	public function loadTemplateCollection():TemplateCollection {
		$document = $this->container->get(Document::class);
		return new TemplateCollection($document);
	}

	#[LazyLoad]
	public function loadListBinder():ListBinder {
		return new ListBinder(
			$this->container->get(TemplateCollection::class)
		);
	}

	#[LazyLoad]
	public function loadDocumentBinder():DocumentBinder {
		$document = $this->container->get(Document::class);
		return new DocumentBinder(
			$document,
			iterator_to_array($this->config->getSection("view")),
			$this->container->get(ElementBinder::class),
			$this->container->get(PlaceholderBinder::class),
			$this->container->get(TableBinder::class),
			$this->container->get(ListBinder::class),
			$this->container->get(TemplateCollection::class),
		);
	}

	#[LazyLoad]
	public function loadRequestUri():UriInterface {
		return $this->request->getUri();
	}
}
