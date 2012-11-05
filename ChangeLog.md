v0.0.3
------
Not yet released

* Major improvements with client side code.
* Http class for easy OOP interface to cURL.
* Cache handler allows bi-directional cache access from anywhere.
* dbtouch files are 'touched' only when each database table changes.
* Public webroot directories are now named 'www'. Not backwards compatible!
* Vimeo PageTool for interacting with the Vimeo API.
* Database connection is not opened until it is used.
* Various improvements to the efficiency of the DAL.
* User PageTool allows white-listed domains.
* PageView _Header and _Footer files can be ignored using ignore comments.

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