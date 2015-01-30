<?php
use Behat\MinkExtension\Context\MinkContext;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as PHPUnit;

class FeatureContext extends MinkContext {

private $fingerprint = "0000000000000000";

/**
 * @Then /^the response headers should include:$/
 */
public function theResponseHeadersShouldInclude(TableNode $table) {
	$actualHeaders = $this->getSession()->getResponseHeaders();
	$expectedHeaders = $table->getRowsHash();
	$actualHeaders = array_change_key_case($actualHeaders, CASE_LOWER);
	$expectedHeaders = array_change_key_case($expectedHeaders, CASE_LOWER);

	array_shift($expectedHeaders);

	foreach ($expectedHeaders as $key => $value) {
		PHPUnit::assertArrayHasKey($key, $actualHeaders);
		PHPUnit::assertContains($value, $actualHeaders[$key][0]);
	}
}

/**
 * @Given /^I remember the head fingerprint$/
 */
public function iRememberTheHeadFingerprint() {
	$page = $this->getSession()->getPage();
	$metaFingerprint = $page->find("css", "head>meta[name='fingerprint']");
	$this->fingerprint = $metaFingerprint->getAttribute("content");
}

/**
 * @When /^I go to the fingerprinted file "([^"]*)"$/
 */
public function iGoToTheFingerprintedFile($filename) {
	$filename = str_replace("{FINGERPRINT}", $this->fingerprint, $filename);
	$this->visit($filename);
}


}#