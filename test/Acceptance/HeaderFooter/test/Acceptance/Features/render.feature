Feature: Test that HTML headers and footers are included
	In order to re-use common HTML
	As a developer
	I should be able to share HTML prefix and suffix files

	Scenario: Open homepage
		Given I go to the homepage
		Then I should see "Look. The homepage!"
		And I should see "Test Website (base header)"
		And I should see "This is the footer"

	Scenario: Open another page
		Given I go to "another-page"
		Then I should see "Look. Another page!"
		And I should see "Test Website (base header)"
		And I should see "This is the footer"

	Scenario: Open nested page with shared header footer
		Given I go to "/directory-with-shared-header-footer"
		Then I should see "Look. A nested page!"
		And I should see "Test Website (base header)"
		And I should see "This is the footer"

	Scenario: Open nested page with own footer
		Given I go to "/directory-with-own-header"
		Then I should see "This page should have its own header"
		And I should see "Test Website (overridden header)"
		And I should not see "Test Website (base header)"
		And I should see "This is the footer"