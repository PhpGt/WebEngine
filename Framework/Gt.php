<?php
final class Gt {
   public function __construct() {
      $settings = array(
         "App"       => new App_Config(),
         "Database"  => new Database_Config(),
         "Security"  => new Security_Config()
      );

      $components = array(
         "DAL"       => new Dal(),
         "DOM"       => new Dom(),
         "Module"    => new Module(),
         "Error"     => new Error()
      );

      $pageCode = null;
      $pageCodeFile  = APPROOT . DS . "PageCode" . DS . FILEPATH . ".php";
      $pageCodeClass = FILECLASS . "_PageCode";
      if(class_exists($pageCodeClass)) {
         $pageCode   = new $pageCodeClass();
      }
      $request       = new Request($pageCode, $settings, $components);
      $response      = new Response($request);
      $injector      = new Injector($response);
   }
}
?>