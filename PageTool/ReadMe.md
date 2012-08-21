PageTool
========
Within any PHP.Gt application, a page's code is put inside individual PageCode objects, and is executed with the `go()` function.

There comes a time when code within PageCode objects is required on other pages, or from within other applications. A PageTool can be seen as PageCode that is required by one or many applications, from multiple areas in code.

Just like a PageCode object executes from the `go()` function, so does a PageTool, which is triggered from within PageCode via the `$tool` wrapper.

Common tools are included with the PHP.Gt codebase, which are shared across all applications. Applications can have their own private tools, or even *override shared tools* if they require different functionality without affecting other applications.

Included PageTools
------------------
The following PageTools are included within the latest public release of PHP.Gt:

* Analytics - allows easy insertion of all client-side code required by Google Analytics, by simply providing your Google Analytics account code.
* Blog - provides a traditional weblog article publishing platform.
* Content - provides dynamic, database-driven content within websites. Content is extracted from the database and inserted into HTML elements automatically according to the elements' IDs.
* Facebook - a wrapper to Facebook's social graph API, exposing functions to fetch/update Facebook statuses, pictures, etc.
* Navigation - simple adds a 'selected' class to the correct menu element within an application, according to the current URL.
* PayPal - a wrapper to PayPal's API, exposing functions to convert HTML buttons to 'Buy It Now' buttons, and create recurring billing options.
* User - provides user authentication using OAuth, along with anonymous user tracking.

Getting more tools
------------------
The PHP.Gt management console provides a catalogue of complete tools for use within your applications. This catalogue is available on the web at http://tools.php.gt