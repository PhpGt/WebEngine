<?php
namespace App\Page;

class Index extends \Gt\Page\Logic {

public function go() {
	// DO NOT REMOVE THESE COMMENTS!
	// This source is hidden from Page View.
	// This comment is searched for to ensure it isn't visible in the response.
	// $h1 = $this->dom->querySelector("h1");
	// $h1->textContent .= " EDITED FROM PAGE LOGIC";

	$p = $this->dom->createElement("p");
	$p->textContent = "Hi I'm a paragraph!";
	$body = $this->dom->querySelector("body");
	$body->appendChild($p);
}

}#