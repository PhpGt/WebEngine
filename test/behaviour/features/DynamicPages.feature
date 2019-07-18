Feature: Dynamic Pages
	As a WebEngine developer
	In order to write dynamic applications
	I want to route certain requests through dynamic logic

	Scenario: Page titles with index
		Given I am on the homepage
		Then I should see "Base Index page"
		When I follow "Go to dir"
		Then I should see "Dir Index page"
		When I follow "Go to nested"
		Then I should see "Nested Index page"
		When I follow "Go to abcdefg"
		Then I should see "Dynamic page"
		And I should see "Dynamic page requested: abcdefg."
		When I am on "/dir/nested/test-123-behat"
		Then I should see "Dynamic page requested: test-123-behat"

	Scenario: Page titles without index
		Given I am on the homepage
		And I follow "Go to dir"
		And I follow "Go to nested without index"
		Then I should see "Dynamic page"
		And I should see "Dynamic page requested: index."
		When I am on "/dir/nested-no-index/another-test-456"
		Then I should see "Dynamic page"
		Then I should see "Dynamic page requested: another-test-456"