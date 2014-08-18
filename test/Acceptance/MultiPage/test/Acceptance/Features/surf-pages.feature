Feature: Test that users can surf between pages
	In order to use the application
	As a user
	I should be able to access pages by uri

	Scenario: Open homepage
		Given I go to the homepage
		Then I should see "MultiPage Test"
		And I should see "This is the index page"

	Scenario: Open second page
		Given I go to "/second-page"
		Then I should see "MultiPage Test"
		And I should see "This is the second page"

	Scenario: Open third page
		Given I go to "/third-page"
		Then I should see "MultiPage Test"
		And I should see "This is the third page"

	Scenario: Access directory as file
		Given I go to "/directory"
		Then I should see "MultiPage Test"
		And I should see "This is the index of the directory"

	Scenario: Access nested file
		Given I go to "/directory/page-inside-directory"
		Then I should see "MultiPage Test"
		And I should see "This is the first page inside a directory"

	Scenario: Access another nested file
		Given I go to "/directory/secondary-page-inside-directory"
		Then I should see "MultiPage Test"
		And I should see "This is the second page inside a directory"

	Scenario: Access a file with extension
		Given I go to "/directory/page-inside-directory.html"
		Then I should be on "/directory/page-inside-directory"

	Scenario: Access a file with invalid case
		Given I go to "/Directory/Page-Inside-Directory"
		Then I should be on "/directory/page-inside-directory"

	Scenario: Access a file with extension and invalid case
		Given I go to "/Directory/Page-Inside-Directory.html"
		Then I should be on "/directory/page-inside-directory"

	Scenario: Access a file with caps locks accidentally on
		Given I go to "/DIRECTORY/PAGE-INSIDE-DIRECTORY.HTML"
		Then I should be on "/directory/page-inside-directory"