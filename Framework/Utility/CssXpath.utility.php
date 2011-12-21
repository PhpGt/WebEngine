<?php
/**
 * Copyright (c) 2005-2011, Zend Technologies USA, Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice,
 *      this list of conditions and the following disclaimer.
 *
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the 
 *      documentation and/or other materials provided with the distribution.
 *
 *    * Neither the name of Zend Technologies USA, Inc. nor the names of its
 *      contributors may be used to endorse or promote products derived from
 *      this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * CssXpath class originally from Zend Framework 2, modified for distribution
 * within PHP.Gt.
 * TODO: Docs.
 */
class CssXpath_Utility {
	private $_selector;			// Original CSS selector.
	private $_xpath;			// XPath version of CSS selector.

	public function __construct($selector) {
		$this->_selector = $selector;
		$this->_xpath = $this->transform($selector);
	}

	public function __toString() {
		return $this->_xpath;
	}

	/**
	* Transform CSS expression to XPath
	*
	* // TODO: Docs.
	* @param  string $path
	* @return string
	*/
	private function transform($path) {
		$path = (string) $path;
		if(strstr($path, ',')) {
			$paths       = explode(',', $path);
			$expressions = array();
			foreach($paths as $path) {
				$xpath = $this->transform(trim($path));
				if(is_string($xpath)) {
					$expressions[] = $xpath;
				}
				else if(is_array($xpath)) {
					$expressions = array_merge($expressions, $xpath);
				}
			}

			return implode('|', $expressions);
		}

		$paths		= array('//');
		$path		= preg_replace('|\s+>\s+|', '>', $path);
		$segments	= preg_split('/\s+/', $path);

		foreach($segments as $key => $segment) {
			$pathSegment = $this->tokenize($segment);
			if(0 == $key) {
				if(0 === strpos($pathSegment, '[contains(')) {
					$paths[0] .= '*' . ltrim($pathSegment, '*');
				}
				else {
					$paths[0] .= $pathSegment;
				}

				continue;
			}

			if(0 === strpos($pathSegment, '[contains(')) {
				foreach($paths as $key => $xpath) {
					$paths[$key] .= '//*' . ltrim($pathSegment, '*');
					$paths[]      = $xpath . $pathSegment;
				}
			}
			else {
				foreach($paths as $key => $xpath) {
					$paths[$key] .= '//' . $pathSegment;
				}
			}
		}

		if(1 == count($paths)) {
		return $paths[0];
		}

		return implode('|', $paths);
	}

	/**
	* Tokenize CSS expressions to XPath
	*
	* @param  string $expression
	* @return string
	*/
	private function tokenize($expression) {
		// Child selectors
		$expression = str_replace('>', '/', $expression);

		// IDs
		$expression = preg_replace(
			'|#([a-z][a-z0-9_-]*)|i',
			'[@id=\'$1\']',
			$expression
		);
		$expression = preg_replace(
			'|(?<![a-z0-9_-])(\[@id=)|i',
			'*$1',
			$expression
		);

		// arbitrary attribute strict equality
		$expression = preg_replace_callback(
			'|\[([a-z0-9_-]+)=[\'"]([^\'"]+)[\'"]\]|i',
			function ($matches) {
				return '[@' . strtolower($matches[1]) 
					. "='" . $matches[2] . "']";
			},
			$expression
		);

		// arbitrary attribute contains full word
		$expression = preg_replace_callback(
			'|\[([a-z0-9_-]+)~=[\'"]([^\'"]+)[\'"]\]|i',
			function ($matches) {
				return "[contains(concat(' ', normalize-space(@"
					. strtolower($matches[1])
					. "), ' '), ' " 
					. $matches[2] . " ')]";
			},
			$expression
		);

		// arbitrary attribute contains specified content
		$expression = preg_replace_callback(
			'|\[([a-z0-9_-]+)\*=[\'"]([^\'"]+)[\'"]\]|i',
			function ($matches) {
				return "[contains(@"
					. strtolower($matches[1])
					. ", '" 
					. $matches[2] . "')]";
			},
			$expression
		);

		// Classes
		$expression = preg_replace(
			'|\.([a-z][a-z0-9_-]*)|i', 
			"[contains(concat(' ', normalize-space(@class), ' '), ' \$1 ')]", 
			$expression
		);

		/** Remove double asterix */
		$expression = str_replace('**', '*', $expression);

		return $expression;
	}
}
?>