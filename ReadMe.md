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

Tests
-----
Each feature of [PHP.Gt](http://github.com/g105b/PHP.Gt) has a test to ensure it works in all conditions, and also to detect when other features cause bugs or other problems.

PHP.Gt chooses PHPUnit as the testing framework because of its large and active community, and how it uses the commonly understood testing architectures xUnit and TAP which provide atomic, isolated, automated and uniform tests.

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

Version numbers
---------------
Version numbers are specified in [git tags](http://git-scm.com/book/en/Git-Basics-Tagging) given in the following format:

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

Branches
--------
As master is the default branch, code is committed into master as regularly as possible, leaving unmerged branches open for as little time as possible.

Bug fixes, new features and code improvements are done on separate branches, whose names should show the issue number with a descriptive name in the following format:

> Bugfix|Feature|Improvement-ShortName-issueNum

For example, a new feature allowing URLs to be typed in binary, with issue number #123:

> Feature-BinaryURL-123

Forking & Pull Requests
-----------------------
When implementing new features or fixing bugs through Github using pull requests, your code will be committed to your own fork, which acts as the branch.

When your code is ready to be added to the PHP.Gt repository, a pull request should be raised, and your code will be added to a new branch using the above naming convention, and merged into master after tests have been completed.
