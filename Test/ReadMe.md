Test
====
Each feature of PHP.Gt has a test to ensure it works in all conditions, and also to detect when other features cause bugs or other problems.

PHP.Gt chooses [PHPUnit](https://github.com/sebastianbergmann/phpunit/) as the testing framework because of its large and active community, and how it uses the commonly understood testing architectures [xUnit](http://wikipedia.org/wiki/XUnit) and [TAP](http://wikipedia.org/wiki/Test_Anything/Protocol) which provide atomic, isolated, automated and uniform tests.

Running the tests
-----------------
All tests for PHP.Gt internals are stored within this Tests directory, and are named using the standard PHPUnit conventions so they can be executed from within the class they are testing.

Requirements
------------
The system requirements to run PHP.Gt tests depend on the requirements of PHPUnit.

* PHPUnit 3.8 or higher.
* PHP 5.4.7 or higher.
* [PHP_CodeCoverage](http://github.com/sebastianbergmann/php-code-coverage), the library that is used by PHPUnit to collect and process code coverage information, depends on [Xdebug](http://xdebug.org/) 2.2.1 (or later).

PHP.Gt releases & projects
--------------------------
Every version release of PHP.Gt is fully tested against all tests stored in the Test directory of the repository. Version releases are done in git using tags.

To write your own unit tests for projects running on PHP.Gt, there simply needs to be a Test directory in the root of your projects' repositories. All tests should use the same naming convention to be automatically picked up by PHPUnit. There is no extra setup required to run the tests.