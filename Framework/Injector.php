<?php
class Injector {
   /**
    * TODO: Docs.
    */
   public function __construct($dom) {
      $this->injectStyleSheets($dom);
      // TODO: Second param indicates whether to compile - add ability to
      // change to false for debugging purposes.
      $this->injectJavaScript($dom, true);
   }

   /**
    * TODO: Docs.
    */
   public function injectStyleSheets($dom) {
      // Find all stylesheets except those
      // that are required to be loaded separately.
      $styleLinkArray = 
         $dom["link[rel='stylesheet'][not(@media)][not(@nocompile)]"];
      $styleString = "";
      $styleCompileFile = APPROOT . DS . "Web" . DS . "Style.css";
      $styleCompileFileModified = 0;
      if(file_exists($styleCompileFile)) {
         $styleCompileFileModified = filemtime($styleCompileFile);
      }
      $styleCacheInvalid = false;

      // Compile them into a single string.
      foreach($styleLinkArray as $styleLink) {
         $filePath = $styleLink->getAttribute("href");
         $href = $filePath;
         // Add directory to filepath if requested.
         if($filePath[0] != "/") {
            $filePath = DS . DIR . DS . $filePath;
         }
         while(strstr($filePath, "//")) {
            $filePath = str_replace("//", "/", $filePath);
         }
         $filePath = str_replace("/", DS, $filePath);

         // Attempt to find the file in the application's directory with a
         // framework fallback.
         $pathArray = array(
            APPROOT . DS . "Style" . $filePath,
            GTROOT . DS . "Style" . $filePath
         );

         $foundStyle = false;
         foreach ($pathArray as $path) {
            if(file_exists($path)) {
               $foundStyle = true;
               $fileMtime = filemtime($path);
               if($fileMtime > $styleCompileFileModified) {
                  $styleCacheInvalid = true;
               }

               $styleString .= "/**********************" . PHP_EOL;
               $styleString .= " * " . $href             . PHP_EOL;
               $styleString .= " *********************/" . PHP_EOL . PHP_EOL;
               $styleString .= file_get_contents($path)  . PHP_EOL . PHP_EOL;

               break;
            }
         }
         if(!$foundStyle) {
            $styleString .= "/**********************"       . PHP_EOL;
            $styleString .= " * CANNOT FIND FILE: " . $href . PHP_EOL;
            $styleString .= " *********************/"       . PHP_EOL . PHP_EOL;
         }
      }

      // Only write the compiled file if there are newer CSS files.
      if($styleCacheInvalid) {
         file_put_contents($styleCompileFile, $styleString);
      }

      $styleCompileFileUrl = str_replace(
         APPROOT . DS . "Web", "", $styleCompileFile);

      // Remove the link elements from the page, replace them with the cache.
      $styleLinkArray->remove();

      // TODO: This is a hack until the DOM's prepend, before, after functions
      // have been written and tested.
      // Simply moves any <link> elements with a "media" attribute to the end
      // of the head, after the compiled file is appended.
      $existingLinks = $dom["head link"];

      $dom["head"]->append(
         $dom->create("link", array(
            "rel"    => "stylesheet",
            "href"   => $styleCompileFileUrl
         ))
      );

      $existingLinks->remove();
      $dom["head"]->append($existingLinks);
   }

   /**
    * TODO: Docs.
    */
   public function injectJavaScript($dom, $compile) {
      // Find all scripts.
      $scriptArray = $dom["script[@src][not(@nocompile)]"];
      $scriptString = "";
      $scriptCompileFile = APPROOT . DS . "WebRoot" . DS . "Script.js";
      $scriptCompileFileModified = 0;
      if(file_exists($scriptCompileFile)) {
         $scriptCompileFileModified = filemtime($scriptCompileFile);
      }
      $scriptCacheInvalid = false;

      // Compile them into a single string.
      foreach($scriptArray as $script) {
         $filePath = $script->getAttribute("src");
         $src = $filePath;
         // Add directory to filepath if requested.
         if($filePath[0] != "/") {
            $filePath = DS . DIR . DS . $filePath;
         }
         while(strstr($filePath, "//")) {
            $filePath = str_replace("//", "/", $filePath);
         }
         $filePath = str_replace("/", DS, $filePath);

         // Attempt to find the file in the application's directory with a
         // framework fallback.
         $pathArray = array(
            APPROOT . DS . "Script" . $filePath,
            GTROOT . DS . "Script" . $filePath
         );

         $foundScript = false;
         foreach ($pathArray as $path) {
            if(file_exists($path)) {
               $foundScript = true;
               $fileMtime = filemtime($path);
               if($fileMtime > $scriptCompileFileModified) {
                  $scriptCacheInvalid = true;
               }

               $scriptString .= "/**********************" . PHP_EOL;
               $scriptString .= " * " . $src              . PHP_EOL;
               $scriptString .= " *********************/" . PHP_EOL . PHP_EOL;
               $scriptString .= file_get_contents($path)  . PHP_EOL . PHP_EOL;

               break;
            }
         }
         if(!$foundScript) {
            $scriptString .= "/**********************"      . PHP_EOL;
            $scriptString .= " * CANNOT FIND FILE: " . $src . PHP_EOL;
            $scriptString .= " *********************/"      . PHP_EOL . PHP_EOL;
         }
      }

      // Only write the compiled file if there are newer CSS files.
      if($scriptCacheInvalid) {
         if($compile) {
            $compiler = new JavaScriptCompiler_Utility($scriptString);
            $scriptString = $compiler->output();
         }
         file_put_contents($scriptCompileFile, $scriptString);
      }

      $scriptCompileFileUrl = str_replace(
         APPROOT . DS . "Web", "", $scriptCompileFile);

      // Remove the link elements from the page, replace them with the cache.
      $scriptArray->remove();
      $dom["head"]->append(
         $dom->create("script", array(
            "src"   => $scriptCompileFileUrl
         ))
      );
   }
}
?>