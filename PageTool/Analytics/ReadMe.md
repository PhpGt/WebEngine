Google Analytics integration PageTool
=====================================

This PageTool simply puts the correct JavaScript in the HTML head, injecting your Google Analytics tracking code. This should be done on all pages that are required to be tracked. For most situations, this can be achieved by starting the PageTool tracking within the highest _Common PageCode.

Example:

```php
<?php class Common_PageCode extends PageCode {

public function go($api, $dom, $template, $tool) {
	$tool["Analytics"]->track("UA-12345678-1");
}

}#
```