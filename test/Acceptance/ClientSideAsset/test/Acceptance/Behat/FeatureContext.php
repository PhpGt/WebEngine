<?php
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\Step\Given;
use Behat\Behat\Context\Step\When;
use Behat\Behat\Context\Step\Then;
use PHPUnit_Framework_Assert as PHPUnit;

class FeatureContext extends MinkContext {

/**
 * @Then /^I should see image asset "([^"]*)"$/
 */
public function iShouldSeeImageAsset($imageFileName) {
	$page = $this->getSession()->getPage();
	$img = $page->find("css", "img");
	$srcAttr = strtolower($img->getAttribute("src"));

	$uri = "/Asset/$imageFileName";

	PHPUnit::assertEquals(strtolower($uri), $srcAttr);

	return [
		new When("I go to \"$uri\""),
		new Then("the response status code should be 200"),
	];
}

}#