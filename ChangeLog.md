v0.1.1
-------
Not yet released

* All configuration settings are made static, so they are accessible from anywhere in the code.

v0.1.0
------
10th May 2013

* Auth class provides wrapper to easy OAuth + social network interaction.
* Optional PageView when PageCode handles request itself.
* @include(path/to/file) to provide HTML includes.
* Introduction of Gt.js client side file v0.0.1.
* Introduction of Gt.css client side file v0.0.1.
* Class system fully replaces Utility classes.
* SCSS files are pre-processed *much* faster.
* Major improvements and bugfixes in User PageTool.
* User PageTool implemented to allow OAuth authentication and anonymous users.
* Blog PageTool initial features.
* Bugfixes in the DOM classes.
* PHP errors and exceptions handled by PHP.Gt.
* Translatable content through data-lang attribute.
* Selection of web-ready fonts included as standard.

v0.0.3
------
20 Dec 2012

* Error handling implemented with exceptions.
* Translatable content, language switchable in URL.
* PHPUnit tests are implemented within repository.
* Major improvements with client side code.
* SCSS (preprocessed CSS files) supported natively.
* Http class for easy OOP interface to cURL.
* Cache handler allows bi-directional cache access from anywhere.
* GT.js and GTUI.css files extraced into separate repositories.
* dbtouch files are 'touched' only when each database table changes.
* Public webroot directories are now named 'www'. Not backwards compatible!
* Vimeo PageTool for interacting with the Vimeo API.
* Database connection is not opened until it is used.
* Various improvements to the efficiency of the DAL.
* DOM interaction more supportive of W3C standards.
* User PageTool allows white-listed domains.
* PageView _Header and _Footer files can be ignored using ignore comments.
* Many changes with the FileOrganiser and client-side Injector classes.

v0.0.2
------
22 Aug 2012

* Multiple improvements on the DOM object.
* Bugs fixed with CSS selectors.
* URL handling improved - automatic case fixing and directory style URLs fixed.
* User PageTool updated to allow anonymous user tracking via UUID.
* Multiple enhancements with GtUi.
* Bugs fixed within DAL.
* Default PHP code style guidelines changed (dropped extra indent).
* Added FakeSlow capability for testing slow connections on AJAX calls.
* Simplified client-side library by removing hacks for out-of-date browsers.

v0.0.1
------
23 Feb 2012

* Automatic database deployment
* Strange unicode characters provided by software such as MS Word now handled.
* Database creation order defined in creation scripts.
* Optional PageCode class suffix
* Content PageTool
* Blog PageTool
* Nested PageCode objects executed in correct order, can be suppressed.
* DOM `map()` function
* DOM shorthand methods such as insertBefore, insertAfter, append, etc.
* JSON webservices without methods supported.
* Debugging JavaScript through query string.
* Error pages added.
* FileOrganiser used to keep original source files out of web root.