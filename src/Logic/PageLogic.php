<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Logic;

use \Gt\Page\TemplateFactory;

abstract class PageLogic extends Logic {

protected $document;
protected $dom;
protected $database;
protected $db;
protected $template;

/**
 * @param Api $api API Access Layer
 * @param Document $content Dom document representing the response's content
 * @param Session $session Session manager
 *
 * @return void
 */
public function __construct($api, $content, $session) {
	parent::__construct($api, $content, $session);

	// Synonyms
	$this->document = $content;
	$this->dom = $this->document;

	$this->template = TemplateFactory::init($content);
}

}#