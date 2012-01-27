/**
 * GT core JavaScript file. Contains the most essential utility functions.
 * TODO: Docs.
 * [jQuery's Dom Ready functionality included as standard]
 * [DomAssistant required to use query selectors in < IE9 properly 
 * (also adds nice DOM manipulation features)].
 *
 * Recommended to use DOMAssistant for lightweight, cross browser functionality,
 * but if only compliant browsers are targetted (i.e. for Chrome applications)
 * then GT object will handle some functions simply like ajax and querySelector.
 */

(function() {
	var _$ = window.$ || null,
		_$$ = window.$$ || null;
	
	/**
	 * TODO: Docs.
	 */
	var GT = function() { 
		if(typeof arguments[0] === "function") {
			// Execute it on DomContentLoaded event.
			return GT.ready(arguments[0], arguments[1]);
		}
		if(typeof arguments[0] === "string") {
			// Return matching DomNodes from CSS selector.
			return GT.querySelector(arguments[0], arguments[1]);
		}
		// Must be an element reference - attempt to pass it to the $ function.
		if(_$) {
			return _$(arguments[0]);
		}
		else {
			return arguments[0];
		}
	};

	GT.templates = [];
	
	/**
	 * TODO: Docs.
	 * [Will only trigger callback when no page is given, or current url
	 * matches given page]
	 */
	GT.ready = function(callback, page) {
		var dollar, doubleDollar,
			pathname = window.location.pathname;
		
		if(page) {
			if(page instanceof RegExp) {
				if(!page.test(pathname)) {
					return;
				}
			}
			else if(page !== pathname) {
				return;
			}
		}

		// Pass what was stored in the dollar and double dollar signs before 
		// harmonization into the callback function.
		dollar = _$;
		doubleDollar = _$$;

		// Attack the event listener in real browsers.
		if(document.addEventListener) {
			document.addEventListener("DOMContentLoaded", function() {
				document.removeEventListener(
					"DOMContentLoaded",
					arguments.callee,
					false
				);
				return callback(dollar, doubleDollar);
			}, false);
		}
		// Hack the event listener in IE.
		else if(document.attachEvent) {
			document.attachEvent("onreadystatechange", function() {
				if(document.readyState === "complete") {
					document.detachEvent(
						"onreadystatechange",
						arguments.callee
					);
					return callback(dollar, doubleDollar);
				}
			});

			if(document.documentElement.doScroll && window == window.top) {
				(function(c_callback, c_dollar, c_doubleDollar) {
					try {
						document.documentElement.doScroll("left");
					}
					catch(error) {
						setTimeout(arguments.callee, 0);
						return;
					}

					c_callback(c_dollar, c_doubleDollar);
				})(callback, dollar, doubleDollar);
			}
		}
	};

	/**
	 * This function on its own is called from the GT main function as a
	 * shorthand method for the browser's querySelectorAll function - but is
	 * extended by including DomAssistant.js for advanced functionality.
	 * NOTE: DomAssistant.js must be included for querySelector functionality
	 * in Internet Explorer 8 and lower.
	 * @param string selector The CSS selector to find.
	 * @param bool quickReference If true, will return a reference to a single
	 * DOM element.
	 * @return NodeList An array like object containing the matching elements.
	 */
	GT.querySelector = function(selector, quickReference) {
		var testEl;
		// Check if DOMAssistant is loaded.
		if(window.DOMAssistant) {
			if(quickReference) {
				return window.DOMAssistant.$$(selector);
			}
			return window.DOMAssistant.$(selector);
		}
		else if(document.querySelectorAll) {
			// IE8 has querySelector, but is limited to CSS2.1 tags.
			// Test for compatibility before continuing.
			testEl = document.createElement("section");
			document.body.appendChild(testEl);
			if(document.querySelector("section")) {
				document.body.removeChild(testEl);

				if(quickReference) {
					return document.querySelector(selector);
				}
				else {
					return document.querySelectorAll(selector);
				}
			}
			document.body.removeChild(testEl);
		}
		
		// At this point, no querySelector techniques are available in the
		// browser. Emulate very simple selectors with regex, otherwise fail.
		// TODO: Regex emulation.
		alert("Error: QuerySelector not available in your browser!");
	};

	/**
	 * A stripped-down ajax function for use only in compliant browsers.
	 * Provides basic functionality when script filesize and complexity needs
	 * to be kept to a minimum. If more functionality is required, load
	 * DOMAssistant which injects ajax functions into DOMNodes.
	 * @param string url The url to request.
	 * @param string method GET or POST.
	 * @param function callback Optional. The function to call with the ajax
	 * response, passing the XMLHttpRequest object.
	 */
	GT.ajax = function(url, method, callback) {
		var xhr,
			readyState,
			status = -1,
			statusText = "",
			createAjaxObj = function(url, method, callback) {
				var params = null;
				if(/POST/i.test(method)) {
					url = url.split("?");
					params = url[1];
					url = url[0];
				}	
				return {
					"url": url,
					"method": method,
					"callback": callback,
					"params": params,
					"headers": {},
					"responseType": "text" 
				};
			},
			makeCall = function(ajaxObj) {
				var response;

				if(!xhr) {
					alert("Error: Something went wrong with the AJAX call.");
					return false;
				}
				if(/POST/i.test(ajaxObj.method)) {
					ajaxObj.headers["Content-type"] =
						"application/x-www-form-urlencoded";
					/*
					ajaxObj.headers["Content-length"] =
						ajaxObj.params 
							? ajaxObj.params.length
							: 0;
					*/
				}

				ajaxObj.headers["X-Requested-With"] =
					"XMLHttpRequest(GT.ajax)";

				xhr.open(ajaxObj.method, ajaxObj.url);

				for(header in ajaxObj.headers) {
					if(ajaxObj.headers.hasOwnProperty(header)) {
						xhr.setRequestHeader(
							header,
							ajaxObj.headers[header]);
					}
				}

				if(typeof ajaxObj.callback === "function") {
					xhr.onreadystatechange = function(e) {
						readyState = xhr.readyState;
						if(xhr.readyState === 4) {
							status = xhr.status;
							statusText = xhr.statusText;
							response = xhr.response;
							// Automatically attempt JSON parsing on URLs to
							// JSON files.
							if(ajaxObj.url.match(/\.json(\?.*)?/i)) {
								if(window.JSON) {
									try {
										response = window.JSON.parse(response);
									}
									catch(e) {
										console.log(
											"Json request, non-json response.");
										console.log(ajaxObj);
									}
								}
							}

							ajaxObj.callback.apply(this, [response, xhr]);
						}
					}
				}

				xhr.send(ajaxObj.params);
			};

		if(window.XMLHttpRequest) {
			xhr = new XMLHttpRequest();
		}
		else if(window.ActiveXObject) {
			try {
				xhr = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(e) {
				xhr = new ActiveXObject("Microsoft.XMLHHTP")
			}
		}
		if(!xhr) {
			alert("Error: Ajax functionality not present in current browser.");
			return false;
		}

		return {
			"get": function(url, callback) {
				return makeCall(createAjaxObj(url, "GET", callback));
			},
			"post": function(url, callback) {
				return makeCall(createAjaxObj(url, "POST", callback));
			}
		};
	}();

	/**
	 * Ensures all imported JavaScript libraries can co-exist with eachother by
	 * replacing global dollar sign usage to what it was before DOMAssistant
	 * loaded.
	 */
	GT.harmonize = function() {
		// Avoid global namespace collision of $ and $$ from other libraries.
		if(window.DOMAssistant) { 
			window.DOMAssistant.harmonize();
		}
	};

	GT.unharmonize = function() {
		if(window.DOMAssistant) {
			window.$ = window.DOMAssistant.$;
			window.$$ = window.DOMAssistant.$$;
		}
	}

	/**
	 * Toggles the harmonization of DOMAssistant.
	 */
	GT.$ = function() {
		if(window.DOMAssistant && window.$) {
			if(window.$ === window.DOMAssistant.$) {
				GT.harmonize();
			}
			else {
				GT.unharmonize();
			}
		}
	};

	/**
	 * Returns a DomNodeList of all elements that have the template attribute,
	 * and removes them from the DOM. Attempts to do this using DOMAssistant if
	 * available (for speed), or falls back to a simple loop system.
	 * Note that looping over many elements is slow without DOMAssistant.
	 */
	GT.template = function() {
		var templateDiv = document.getElementById("PHPGt_Template_Elements"),
			templateDivNodeCount,
			i;

		if(templateDiv) {
			templateDivNodeCount = templateDiv.children.length;
			for(i = 0; i < templateDivNodeCount; i++) {
				GT.templates.push(templateDiv.children[i]);
			}
			templateDiv.parentNode.removeChild(templateDiv);
		}
	};

	/**
	 * TODO: Docs.
	 */
	GT.getTemplate = function(name, templateAttribute){
		var i, templateNodesCount, el, clone;

		if(!templateAttribute) {
			templateAttribute = "data-template";
		}

		templateNodesCount = GT.templates.length;
		for(i = 0; i < templateNodesCount; i++) {
			el = GT.templates[i];
			if(el.getAttribute(templateAttribute) === name) {
				clone = el.cloneNode(true);
				clone.removeAttribute(templateAttribute);
				if(window.DOMAssistant) {
					return GT(clone);
				}
				return clone;
			}
		}
		return false;
	};

	GT.harmonize();
	GT.ready(GT.harmonize);
	GT.ready(GT.template);

	window.GT = GT;
}());
