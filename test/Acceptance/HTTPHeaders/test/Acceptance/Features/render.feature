Feature: Test that HTTP response headers are sent
	In order to view static files
	As a web browser
	I should see the correct headers in the HTTP response

	Scenario: Open homepage
		Given I go to the homepage
		Then I should see "This is the homepage"
		And the response status code should be 200
		And the response headers should include:
		| Header name		| Header value		|
		| Content-type		| text/html			|

	Scenario: View file placed in www
		Given I go to "/readme.txt"
		Then the response should contain "Read Me"
		And the response status code should be 200
		And the response headers should include:
		| Header name		| Header value		|
		| Content-type		| text/plain		|

	Scenario: View stylesheet placed in style source directory
		Given I go to the homepage
		And I remember the head fingerprint
		When I go to the fingerprinted file "/Style-{FINGERPRINT}/my-style.css"
		Then the response status code should be 200
		And the response should contain "body {"
		And the response headers should include:
		| Header name		| Header value		|
		| Content-type		| text/css			|

	Scenario: View JavaScript placed in script source directory
		Given I go to the homepage
		And I remember the head fingerprint
		When I go to the fingerprinted file "/Script-{FINGERPRINT}/my-script.js"
		Then the response status code should be 200
		And the response should contain "Hello from JavaScript!"
		And the response headers should include:
		| Header name		| Header value				|
		| Content-type		| application/javascript	|