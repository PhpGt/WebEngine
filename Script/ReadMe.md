Script
======
All JavaScript files are contained within the Script directory, which is a subdirectory of GTROOT or APPROOT - outside of the public web root. This means that the actual JavaScript files are unavailable over HTTP, which is a security feature allowing PHP.Gt to inject your scripts into a single, compiled and minified script.

Within the GTROOT/Script directory are JavaScript files that are accessible over all shared projects. This includes various JavaScript utilities like the IE HTML5 shiv, a lightweight AJAX library and a complementary UI script.

Scripts that are application specific are placed into APPROOT/Script, and are included in the project in the same way; scripts are placed in the `<head>` tag in their required order. Don't worry about the best practice of how many external scripts to include in the head - PHP.Gt will remove them and replace them with a single, minified and compiled script (when client-side compilation is enabled).

Modular scripts
---------------
For the most consistent, speedy and easy-to-debug client side scripting experience, every PageView within an application should have the same scripts loaded. This will mean the minifier / compiler will only have to work once. Using the `Gt` JavaScript library, it is possible to only execute certain scripts on certain pages, but they should all be present across all pages.