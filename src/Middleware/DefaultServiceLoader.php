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
use Gt\DomTemplate\ListElementCollection;
use Gt\DomTemplate\PlaceholderBinder;
use Gt\DomTemplate\TableBinder;
use Gt\Http\Header\ResponseHeaders;
use Gt\Http\Request;
use Gt\Http\Response;
use Gt\Http\Uri;
use Gt\ServiceContainer\Container;

class DefaultServiceLoader {
	public function __construct(
		protected Config $config,
		protected Container $container
	) {
	}

	public function loadResponseHeaders():ResponseHeaders {
		$response = $this->container->get(Response::class);
		return $response->headers;
	}

	public function loadDatabase():Database {
		$driver = $this->config->get("database.driver");
		$initQueryParts = [];
		if($initQueryEveryDriver = $this->config->get("database.init_query")) {
			array_push($initQueryParts, $initQueryEveryDriver);
		}
		if($initQuerySpecificDriver = $this->config->get("database.init_query_$driver")) {
			array_push($initQueryParts, $initQuerySpecificDriver);
		}

		$dbSettings = new Settings(
			$this->config->get("database.query_directory"),
			$driver,
			$this->config->get("database.schema"),
			$this->config->get("database.host"),
			$this->config->get("database.port"),
			$this->config->get("database.username"),
			$this->config->get("database.password"),
			$this->config->get("database.connection_name") ?: DefaultSettings::DEFAULT_NAME,
			$this->config->get("database.collation") ?: DefaultSettings::DEFAULT_COLLATION,
			$this->config->get("database.charset") ?: DefaultSettings::DEFAULT_CHARSET,
			implode(";", $initQueryParts) ?: null,
		);
		return new Database($dbSettings);
	}

	public function loadHTMLAttributeBinder():HTMLAttributeBinder {
		return new HTMLAttributeBinder();
	}

	public function loadHTMLAttributeCollection():HTMLAttributeCollection {
		return new HTMLAttributeCollection();
	}

	public function loadPlaceholderBinder():PlaceholderBinder {
		return new PlaceholderBinder();
	}

	public function loadElementBinder():ElementBinder {
		return new ElementBinder(
			$this->container->get(HTMLAttributeBinder::class),
			$this->container->get(HTMLAttributeCollection::class),
			$this->container->get(PlaceholderBinder::class),
		);
	}

	public function loadTableBinder():TableBinder {
		return new TableBinder(
			$this->container->get(ListElementCollection::class),
			$this->container->get(ElementBinder::class),
			$this->container->get(HTMLAttributeBinder::class),
			$this->container->get(HTMLAttributeCollection::class),
			$this->container->get(PlaceholderBinder::class),
		);
	}

	public function loadListElementCollection():ListElementCollection {
		$document = $this->container->get(Document::class);
		return new ListElementCollection($document);
	}

	public function loadListBinder():ListBinder {
		return new ListBinder(
			$this->container->get(ListElementCollection::class)
		);
	}

	public function loadDocumentBinder():DocumentBinder {
		$document = $this->container->get(Document::class);
		return new DocumentBinder(
			$document,
			iterator_to_array($this->config->getSection("view")),
			$this->container->get(ElementBinder::class),
			$this->container->get(PlaceholderBinder::class),
			$this->container->get(TableBinder::class),
			$this->container->get(ListBinder::class),
			$this->container->get(ListElementCollection::class),
		);
	}

	public function loadRequestUri():Uri {
		return $this->container->get(Request::class)->getUri();
	}
}
