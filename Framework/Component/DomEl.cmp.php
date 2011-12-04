<?php
class DomEl {
   private $_dom;
   private $_node;

   /**
    * A wrapper to PHP's native DOMElement, adding more object oriented
    * features to be more like JavaScript's implementation.
    */
   public function __construct(
      $dom,
      $element,
      $attrArray  = null,
      $value      = null) {

      $this->_dom = $dom;

      if($element instanceof DOMElement) {
         $this->_node = $element;
      }
      else if(is_string($element)) {
         // TODO: New feature: Allow passing in CSS selector to create the el.
         // i.e. createElement("div.product.selected");
         $this->_node = $dom->createElement($element, $value);
      }

      if(is_array($attrArray)) {
         foreach($attrArray as $key => $value) {
            $this->_node->setAttribute($key, $value);
         }
      }
   }

   /**
    * TODO: Docs.
    */
   public function __call($name, $args = array()) {
      if(method_exists($this->_node, $name)) {
         return call_user_func_array(array($this->_node, $name), $args);
      }
      else {
         return false;
      }
   }

   /**
    * TODO: Docs.
    */
   public function __get($key) {
      switch($key) {
      case "innerHTML":
      case "innerHtml":
      case "innerText":
         return $this->_node->nodeValue;
         break;
      default: 
         if(property_exists($this->_node, $key)) {
            // Attempt to never pass a native DOMElement without converting to
            // DomEl wrapper class.
            if($this->_node->$key instanceof DOMELement) {
               return $this->_dom->createElement($this->_node->$key);
            }
            return $this->_node->$key;
         }
         break;
      }
   }

   /**
    * TODO: Docs.
    */
   public function __set($key, $value) {
      switch($key) {
      case "innerHTML":
      case "innerHtml":
      case "innerText":
         $this->_node->nodeValue = $value;
         break;
      default:
         $this->_node->setAttribute($key, $value);
         break;
      }

      $this->updateDom();
   }

   /**
    * TODO: Docs.
    */
   private function updateDom() {
      $this->_dom->update();
   }
}
?>