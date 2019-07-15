Feature: Page Logic
	As a WebEngine developer
	In order to write DRY code
	I want to execute common logic across multiple pages

	Scenario: Page titles
		Given I am on the homepage
		Then I should see "Base Index page"
		When I follow "Go to dir"
		Then I should see "Dir Index page"
		When I follow "Go to nested"
		Then I should see "Nested Index page"