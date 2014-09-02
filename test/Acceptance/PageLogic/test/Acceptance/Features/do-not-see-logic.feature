Feature: Page Logic is hidden from user
	In order to be secure
	As a user
	I should never see the Page Logic source

	Scenario: Open homepage
		Given I go to the homepage
		Then I should see "MultiPage Test"
		And I should see "PageLogic Test"
		And the response should not contain "hidden from Page View"