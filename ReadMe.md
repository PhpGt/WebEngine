[PHP.Gt](http://php.gt)
=======================
A lightweight application development toolkit aimed at automating deployment, streamlining development and respecting web technologies.

[Head over to the Github Wiki for documentation](https://github.com/g105b/PHP.Gt/wiki)

Updates in Beta1
----------------
For a full list of updates, see the [ChangeLog.md](ChangeLog.md) file.

In order of importance: 

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

* [Ask and answer technical questions on Stack Overflow](http://stackoverflow.com/questions/tagged/phpgt). For formal, technical queries, tag your question with "phpgt" and someone will answer you as soon as possible.
* [Join or start a discussion on Google Plus](https://plus.google.com/u/0/communities/100081733478029883187). For general discussion, showcasing your work and the latest news.
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
PHP.Gt is used within a continuous integration environment and has the following rules within its branching:

* **XX-issueName-category** - a rule of thumb is that code shouldn't be written unless the bug/feature/improvement is in the issue tracker. Once the issue starts to be tackled, a new branch is created using this naming convention: the issue number, hyphen, the issue name, hyphen, the category/importance. These branches are tested locally and when finished will make their way into the daily build.
* **daily** - A.K.A. nightly / dev. At the end of every day, all completed branches are merged into the daily branch. While each issue has its own tests, there could be rare cases where code has conflicting side effects - using this branch in the workflow helps reduce the likelihood of bugs being introduced into the master branch.
* **master** - A.K.A. build. When a set of features is ready, they are merged into the master branch. All tests should pass before pushing to master! The master branch, being the default branch on Github, is where new development should be branched from. This branch is not ready for use in production! There are two more branches ahead!
* **next** - When ready to promote a set of changes, the master branch will be merged into next. Next and master should be very close in the tree, as there is only really one difference between them: the development workflow of all PHP.Gt projects uses the next branch to test against production data. If all tests pass on the next branch, it can be safely assumed that they will pass in live.
* **live** - This branch points to the lastest public release. There are no tests run against this branch, it is never debugged, and most importantly - has no bugs! If there is a bug in live, the test methodology needs reconsidering further back down the tree.

Bug fixes, new features and code improvements are done on separate branches, whose names should show the issue number with a descriptive name in the following format:

> issueNum-ShortName-Bug|Feature|Improvement

For example, a new feature allowing URLs to be typed in binary, with issue number #123:

> 123-BinaryURL-Feature

Forking & Pull Requests
-----------------------
When implementing new features or fixing bugs through Github using pull requests, your code will be committed to your own fork, which acts as the branch.

When your code is ready to be added to the PHP.Gt repository, a pull request should be raised, and your code will be added to a new branch using the above naming convention, and merged into master after tests have been completed.

Lastly, the PHP.Gt team accept contributions in patch format, which can be created using the [`git format-patch` command](http://git-scm.com/docs/git-format-patch). These can be sent as attachments in the issues they are fixing, using [Github Gists](https://gist.github.com/).