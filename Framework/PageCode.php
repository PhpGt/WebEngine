<?php
abstract class PageCode {
   private $_api = array();
   private $_data = array();

   public function __construct() {
      // TODO: Set all member variables here for use within PageCode.
   }

   public function flush($dom) {
      $dom->outputHtml();
   }

   private function injectApi($name) {
      
   }

   /**
    * Called at the start of the PageCode's life. All APIs used within this
    * page must be initialised here, using the private method injectApi().
    */
   abstract protected function init();

   /**
    * When an HTTP POST request is made, this function is called before any
    * others in the PageCode class.
    * @param array $data The posted data, in an associative array.
    */
   abstract protected function onPost($data);

   /**
    * Where the majority of all calculations are made and logic is executed
    * for this page. Access to the APIs and manipulation/storage of data should
    * be done at this stage.
    */
   abstract protected function main();

   /**
    * Called before any DOM elements are scraped, so DOM at this stage
    * represents the original HTML perfectly. Any API usage should have been
    * done in main(), where preRender() and render() can access the result from
    * $this->_data.
    * @param Dom $dom The Dom object inherits from PHP's DOMDocument, giving it
    * added functionality. Manipulations are updated before being sent to the
    * browser.
    */
   abstract protected function preRender($dom);

   /**
    * Called just before flushing the output buffer in the response. DOM
    * manipulation is performed here, and DOM elements scraped from the HTML
    * are accessible here. Any API usage should have been done in main(),
    * where preRender() and render() can access the result from $this->_data.
    * @param Dom $dom The Dom object inherits from PHP's DOMDocument, giving it
    * added functionality. Manipulations are updated before being sent to the
    * browser.
    * @param array $domElements An associative array of DomElements, which have
    * already been scraped from the DOM. To allow for structuring dynamic 
    * content within the HTML, any nodes with data-phpgt attributes will be
    * collected for manipulation, and the values of the data-phpgt attributes
    * will be used for the array keys.
    */
   abstract protected function render($dom, $domElements );
}
?>