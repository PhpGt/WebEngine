User PageTool is used to provide authentication within your application, along with anonymous users for applications that don't require signing up but do require persistent storage. Anonymous users can then be converted into full users by regular authentication.

Examples
========

The common PageCode can be used to check for authentication requests:

```php
<?php class _Common_PageCode extends PageCode {

public function go($api, $dom, $template, $tool) {
	$user = $tool["User"]->getUser();

	if(array_key_exists("Authenticate", $_REQUEST)) {
		$tool["User"]->auth($_REQUEST["Authenticate"]);
	}
	else if(array_key_exists("Unauthenticate", $_REQUEST)) {
		$tool["User"]->unAuth();
	}
}

}?>
```

The PageCode within a certain page can be used to perform user actions:

```php
<?php class Index_PageCode extends PageCode {

public function go($api, $dom, $template, $tool) {
	// Only allow certain whitelisted users to log in.
	$tool["User"]->addWhiteList("*@g105b.com");

	// A simple check on isAuthenticated can be trusted for displaying
	// authentication-sensitive data.
	if($tool["User"]->isAuthenticated) {
		$dom["div#anonymous"]->remove();
		$dom["div#authenticated p span"]->text = $tool["User"]->username;
	}
	else {
		$dom["div#authenticated"]->remove();
	}

	// If an already-identified user logs in, the orphaned anonymous user is
	// still present in the database... this can now be merged by your app
	// if required.
	if(!is_null($tool["User"]->orphanedID)) {
		$dom["p#output"]->text = 
			"Merge existing user " 
			. $tool["User"]->id
			. " with anonymous user "
			. $tool["User"]->orphanedID;
	}
}

}?>
```