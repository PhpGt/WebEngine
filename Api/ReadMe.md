The API system
==============
The API is a layer on top of the DAL, intended to:

1. Abstract the database, allowing for advanced data manipulation if necessary.
2. Create a single point of access to the data via code *and* webservices.
3. Allow non-standard or external data sources to be accessed easily.

Getting started
---------------
The first thing to mention is that an API file is not required to be able to access the data in the DAL.

For example, if there is a 'Shop' table in a project, and it has been set up correctly according to the PHP.Gt guidelines, there is not need for Shop.api.php in order to query it. If there was an action query file called 'GetAllItems.sql', within a PageCode or PageTool, it would be possible to execute the query with the code `$api["Shop"]->getAllItems()`, even though the API file does not exist.

