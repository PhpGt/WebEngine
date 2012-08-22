[PHP.Gt](http://php.gt)
=======================
A lightweight application development toolkit aimed at automating deployment, streamlining development and respecting web technologies.

Updates in Alpha2
-----------------
For a full list of updates, see the ChangeLog.md file.

In order of importance: 

* Multiple improvements on the DOM object.
* Bugs fixed with CSS selectors.
* URL handling improved - automatic case fixing and directory style URLs fixed.
* User PageTool updated to allow anonymous user tracking via UUID.
* Multiple enhancements with GtUi.
* Bugs fixed within DAL.
* Default PHP code style guidelines changed (dropped extra indent).
* Added FakeSlow capability for testing slow connections on AJAX calls.
* Simplified client-side library by removing hacks for out-of-date browsers.

System Requirements
-------------------
PHP.Gt requires PHP version 5.4 at least to run. By version 1, PHP.Gt shall be webserver/OS agnostic, but as of now has only been tested on Lighttpd within Debian based systems.

Community, getting help & feedback.
-----------------------------------
There are a number of ways to get help with your PHP.Gt development.

* [Ask and answer technical questions on Stack Overflow](http://stackoverflow.com/questions/tagged/phpgt"). For formal, technical queries, tag your question with "phpgt" and someone will answer you as soon as possible.
* [Join or start a discussion on Google Plus](https://plus.google.com/s/%23phpgt"). Use the hashtag #phpgt to alert the developers, and you will get some responses coming your way in no time.
* [Receive full technical support with managed hosting packages](http://php.gt/Hosting.html). Full technical support packages are provided with the managed hosting offered by Bright Flair.

Licensing
---------
The open source codebase is available on Github: http://github.com/g105b/PHP.Gt

This software is covered by the Apache License. Like any free software license, the Apache License allows you the freedom to use the software for any purpose, to distribute it, to modify it, and to distribute modified versions of the software, under the terms of the license. *For full licensing details, please see the License.md file.* Any distributions of PHP.Gt must include a readable version of the NOTICE file.

Versions
--------
Version numbers are given in the following format:

> vRelease.Beta.Alpha

So the first public release is

> v1.0.0

The first ever public beta (before v1 release) is

> v0.1.0

**Alpha versions** are a collection of minor related code changes. Their progress can be quantified, and a version increments when the whole collection is completed.

Between alpha versions, any point of the codebase can become unstable.

**Beta versions** are collections of alpha releases that create a substantial change to the functionality, or fix major problems with the codebase.

Between beta versions, only pre-defined areas of the codebase can become unstable, and no beta release should break functionality out of this area.

**Public versions** are finalised, finished and fully tested collections of beta updates. Public versions can only increment when there are **no known bugs** - future releases should not be fixes of public versions, but new features or improvements. 