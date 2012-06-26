Application Configuration
=========================

The config directory holds all default configuration that is shared across
all projects. Each project is free to change the configuration values by
extending the class.

All config classes suffixed with `_Framework` hold configuration settings that apply to all applications. All protected properties can be overridden by application-specific versions of the class. App-specific versions are stored in the Config application directory, and the classes are named similarly, but drop the `_Framework` suffix.

There are three configuration files required:

Application configuration
-------------------------
Mainly generic settings are stored here, that relate to how the application works and how it is delivered.

The main settings to take note of include:

* isProduction - setting to false enables debug messages to be sent. When true, any errors that are uncaught are replaced with an error 500 page.
* isCached - when true, PHP.Gt caches all pages' output. By default this is done on a time basis, but may be changed to more advanced cache settings in the PageCode of each page.
* isClientCompiled - when true, JavaScript and Cascading Style Sheets files are minified and compiled into one. All references to external js/css files are still placed inside the `<head>` of pages, but multiple `<script>` tags are replaced by a single tag that references the compiled file.

Database configuration
----------------------
All details of the database connection are stored in this file. By default, there are certain connection settings that **need** to be changed per-application, such as the database username and password, and possibly IP address if an external server is used.

The order of automatic deployment of database tables is specified here, so any table dependencies can be specified.

Security configuration
----------------------
Most settings within this file should be overridden by applications as they directly relate to the security of the applications. Having this information visible to the public is not a good idea.

As there is a lot of automatic setup and deployment provided by PHP.Gt, it is possible to white-list IPs and for deployment and administrative login here.