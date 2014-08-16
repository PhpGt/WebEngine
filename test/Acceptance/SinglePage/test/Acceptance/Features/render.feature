Feature: Test that users see the page
	In order to use the application
	As a user
	I should be able to see the homepage

	Scenario: Open homepage
		Given I go to the homepage
		Then I should see "Test Page (Single Page Application)"

	Scenario: Access invalid page
		Given I go to "/invalid-page"
		Then the response status code should be 404