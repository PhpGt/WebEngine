Database
========
Within the Database directory, there is a subdirectory for each table collection. A table collection is a group of tables, and each collection's name is that of the parent table that all other tables are dependent on.

Tables that are used to provide common functionality across multiple PHP.Gt projects are stored within the framework's Database directory, and application-specific database functionality can be placed within the Database directory of each application.

Automatic deployment
--------------------
A key feature of PHP.Gt is that the database is auto-deployable. Simply visiting a new  application in your browser will deploy its database along with all required tables and data.

This is done by using special creation scripts, noted with a leading underscore. The first creation scripts that are executed are in the GTROOT/Database directory; `_CreateDatabase-001.sql`, `_CreateDatabase-002-User.sql` and `_CreateDatabase-003-UserPrivileges.sql`. These are very simple scripts that deploy the database and user itself, according to the application's security settings.

Deployment of tables occur on two occasions:

1. A table is attempted to be accessed - this will trigger the creation scripts to execute for that table collection.
2. There has been a creation order assigned within the Application's database configuration settings.

For simpler applications, it is acceptable to only deploy tables *as they are used*, but if the application's complexity requires all tables to be deployed before the application is run, the order of table collection creation can be specified in the application's database configuration settings (APPROOT/Config/Database.cfg.php).

Note that the purpose of the 'creation order' setting is to state which order each table collection is to be deployed in. In most situations, all dependencies are contained within the same table collection, but in advanced scenarios, the table collection creation order may be vital.

Table deployment
----------------
Within each table collection directory, the individual table creation scripts are stored in the following format:

`_CreateTable-xxx-Table_Name.sql`

and

`_InsertData-xxx-Table_Name.sql`

The value after `_CreateTable-` or `_InsertData-` should be a numeric value, used to define which order the scripts execute. The scripts actually execute in alphabetical order, which is why a three-digit number is used to signify this order.

If the alphabetic order of the creation scripts causes SQL errors (for example, deploying a table before one of its dependencies), the *automatic deployment will fail*.

All automatic deployment is done inside a single transaction, so will never leave your database in a semi-deployed state.

Action queries
--------------
Each action that can be performed within each table collection should be stored in its own SQL query. For example, a Shop table collection will have a separate SQL file for each possible database action, including: getAllItems, getItemById, getItemsByPrice. Future versions of PHP.Gt will allow creation of stored procedures to allow common SQL to be stored in the database, eliminating any SQL repetition.

Within each action query, placeholders can be used to allow safe binding of variables from within your code, avoiding SQL injection.

API & Webservices
-----------------
The action queries for each table collection can be accessed from your PageCode files using the following syntax:

`$result = $api["TableCollectionName"]->actionQueryName([
	"PlaceholderName" => "PlaceholderValue"
]);`

By enabling external methods within the Api object for table collections, the same action queries can be accessed via JSON or XML webservices, without any extra work. The syntax for the same example action query is as follows:

`http://example.com/TableCollectionName.json?Method=actionQueryName&PlaceholderName=PlaceholderValue`

Advanced action queries
-----------------------
All data-related logic should be placed in SQL where appropriate, but sometimes it just isn't a good idea to use SQL for complicated data computation. That's where the API object comes in strong.

Action queries can be replaced by PHP functions within the API object. This means that calling `actionQueryName()` can execute a PHP function rather than an SQL query.

API action query functions can be used to either give a platform for advanced data manipulation, or can be used to connect to non-sql data sources, such as files or other external webservices.