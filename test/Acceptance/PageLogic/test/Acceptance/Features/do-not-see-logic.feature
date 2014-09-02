Feature: Page Logic is hidden from user
	In order to be secure
	As a user
	I should never see the Page Logic source

	Scenario: Open homepage
		Given I go to the homepage
		Then I should see "PageLogic Test"
		And I should see "This is the index page"
		And I should see "EDITED FROM PAGE LOGIC"
		And the response should not contain "hidden from Page View"