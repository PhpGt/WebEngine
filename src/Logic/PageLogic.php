<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Logic;

use \Gt\Page\TemplateFactory;

abstract class PageLogic extends Logic {

protected $document;
protected $dom;
protected $database;
protected $db;
protected $template;
protected $data;

/**
 * @param Api $api API Access Layer
 * @param Document $content Dom document representing the response's content
 * @param Session $session Session manager
 * @param Data $data Data factory
 *
 * @return void
 */
public function __construct($api, $content, $session, $data) {
	parent::__construct($api, $content, $session, $data);

	// Synonyms
	$this->document = $content;
	$this->dom = $this->document;

	$this->template = TemplateFactory::init($content);
}

}#