Framework
=========
The framework directory is where all the components of PHP.Gt lie, which make up the typical PHP framework.

Included in this directory is:

* Bootstrap.php - loads all required files and calls the all important Gt object.
* Gt.php - the said Gt object, which calls all objects in the correct order to complete the request / response.
* Autoloader.php - uncommon library files that are only used in very specific areas of code are loaded using the autoload function here, only when needed.
* Request.php - deals with all aspects to do with the HTTP request.
* Response.php - deals with all aspects to do with the HTTP response.
* Dispatcher.php - calls all functions on framework objects in the correct order, and passes the objects only the information they require.
* FileOrganiser.php - copies required files from the APPROOT and GTROOT directories into the application's webroot, so they are publicly accessible via HTTP.
* Injector.php - removes and replaces script and link tags into the HTML document when client-side compilation is enabled.
* PageCode.php - an abstract object that defines the entry point to user code.
* PageTool.php - an abstract object that defines the entry point to shared application code.

The subdirectories contained within the framework directory act as follows:

* Component - framework objects that are handled within user code are called components. These include the DOM, DOM elements, API objects and their associated DAL objects, and wrappers for PageTools and Templated elements.
* Error - stores files used when there are errors in code, and html files used to display HTTP errors in the browser.
* Reserved - not actually in use in version 1 of PHP.Gt, this directory itself is reserved. It will contain 'special' files that will give overriding behaviour to page requests, such as /Admin.
* Utility - small and uncommon code libraries are stored here. Typically, a utility does not complete a single task on its own, otherwise it would be classed as a 'tool'. Therefore, tools within PHP.Gt will make use of these utilities.