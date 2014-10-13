<?php
use Behat\MinkExtension\Context\MinkContext;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as PHPUnit;

class FeatureContext extends MinkContext {

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


}#