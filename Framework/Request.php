<?php
final class Request {
   public function __construct($pageCode, $settings, $components) {
      $contentType = "text/html";
      if(EXT == "json") { 
         $contentType = "application/json";
      }
      header("Content-Type: $contentType; charset=utf-8");
      header("X-Powered-By: PHP.Gt Version " . VER);

      // Check for framework-reserved requests.
      if(in_array(strtolower(FILE), $settings["App"]->getReserved())
         || in_array(strtolower(BASEDIR), $settings["App"]->getReserved()) ){
         // Request is reserved, pass request on to the desired function.
         $reservedName = BASEDIR == ""
            ? FILE
            : BASEDIR;
         $reservedFile = GTROOT . DS . "Framework" . DS 
            . "Reserved" . DS . ucfirst($reservedName) . ".php";
         if(file_exists($reservedFile)) {
            require($reservedFile);
            exit;
         }
         die("Reserved");
      }

      // Check whether whole request is cached.
      if($settings["App"]->isCached()) {
         
      }

      session_start();
      if(!is_null($pageCode)) {
         $pageCode->beforeRender();
      }
   }
}
?>