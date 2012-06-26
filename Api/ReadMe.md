The API system
==============
The API is a layer on top of the DAL, intended to:

1. Abstract the database, allowing for advanced data manipulation if necessary.
2. Create a single point of access to the data via code *and* webservices.
3. Allow non-standard or external data sources to be accessed easily.

Getting started
---------------
The first thing to mention is that an API file is not required to be able to access the data in the DAL.

For example, if there is a 'Shop' table in a project, and it has been set up correctly according to the PHP.Gt guidelines, there is not need for Shop.api.php in order to query it. An action query file within the Shop directory called 'GetAllItems.sql' would be able to be executed from within a PageCode or PageTool, with the code `$api["Shop"]->getAllItems()`, even though the API file does not exist.

The abstraction that the API object offers is so that no matter where your data is, it will always be accessed in your code in the same way, and if the data source is ever changed, your code will not need to be updated.

The API object
--------------
An API object becomes necessary when either the data is needed to be exposed externally via a webservice, or the data needs manipulating in a way that is not possible with SQL.

### Webservices ###
To expose your database externally via a webservice, each action method needs to be individually expressed as external. This is done by listing the method names in an array: `public $externalMethods = array("Create", "Retrieve");`. This example will allow the methods Create and Retrieve to be accessed via a webservice.

It is now possible to access the data over HTTP using `http://example.com/Shop.json?Method=Create`, substituting `json` for the representation required.

As a full example, continuing from the Shop example above, the following code snippet is the full contents of **Shop.api.php** required to expose 'GetAllItems', 'GetItem' and 'AddToBasket' methods:

```php
<?php
class Shop_Api extends Api {
	public $externalMethods = array("GetAllItems", "GetItem", "AddToBasket");
}
?>
```

### Advanced data manipulation ###
By splitting all data manipulation methods into their individual verbs, it should be possible in most cases to keep all manipulation within SQL, but for cases when it makes more sense to use PHP to manipulate input / output, this can be done by overriding the method name as a method on the API object.

As a trivial example, adding a hashed value to a table can be done via PHP rather than SQL, although note that this simplistic code would make more sense to be in the application code rather than in the API.

```php
<?php
class Shop_Api extends Api {
	// hashAllItems updates the entire Shop_Item table and stores a hash value.
	//This is for explanatory purposes and has no real world usage.
	// Called with $api["Shop"]->hashAllItems()
	
	// The first param will always be bound as the current DAL object.
	public function hashAllItems($dal) {
		$dataList = $dal->query("select `Data` from `Shop_Item`");
		foreach ($dataList as $data) {
			$hash = hash("sha1", $data);
			$dal->query("update `Shop_Item` set `Hash` = :Hash",
				array("Hash" => $hash)
			);
		}
	}
}
?>
```