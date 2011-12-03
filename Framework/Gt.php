<?php
final class Gt {
   public function __construct() {
      $settings = array(
         "App"       => new App_Config(),
         "Database"  => new Database_Config(),
         "Security"  => new Security_Config()
      );

      $elements = array(
         "DAL"    = new Dal(),
         "DOM"    = new Dom(),
         "Module" = new Module(),
         "Error"  = new Error()
      );

      $pageCode = new PageCode();
      $request  = new Request($pageCode, $settings, $elements);
      $response = new Response($request);
   }
}
?>