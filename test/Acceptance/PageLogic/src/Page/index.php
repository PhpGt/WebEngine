<?php
namespace App\Page;

class Index extends \Gt\Page\Logic {

public function go() {
	// This source is hidden from Page View.
	// This comment is searched for to ensure it isn't visible in the response.
	$h1 = $this->dom->getElementsByTagName("h1")->item(0);
	$h1->nodeValue .= " EDITED FROM PAGE LOGIC";
}

}#