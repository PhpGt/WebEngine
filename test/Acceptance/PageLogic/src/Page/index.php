<?php
namespace App\Page;

class Index extends \Gt\Page\Logic {

public function go() {
	// This source is hidden from Page View.
	$h1 = $this->dom->getElementsByTagName("h1")->item(0);
	$h1->nodeValue .= " EDITED FROM PAGE LOGIC";
}

}#