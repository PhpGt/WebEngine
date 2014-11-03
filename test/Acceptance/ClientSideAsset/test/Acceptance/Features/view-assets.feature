Feature: Test that users can view assets
	In order to test the FileOrganiser
	As a user
	I should see a different asset on each page

	Scenario: Open page 1
		Given I go to "/page1"
		Then I should see image asset "image1.jpg"

	Scenario: Open page 2
		Given I go to "/page2"
		Then I should see image asset "image2.jpg"