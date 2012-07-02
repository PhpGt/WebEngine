/**
 * GT core JavaScript file. Allows accessing applications' APIs, DOM elements,
 * templated elements and PageTools' JavaScript functions using simple syntax.
 *
 * GT is developed by Greg Bowler / PHP.Gt team.
 * Code/licensing: http://phpgt.com/Licence.html
 * Documentation: http://phpgt.com/Docs/ClientSide/GtJs.html
 *
 * Provided as standard:
 * GT.api("Name") - manipulate the application's REST API.
 * GT.dom("selector") - obtain a reference to DOM element, with helpers.
 * GT.template("Name") - obtain a cloned DOM element, taken from the templates.
 * GT.tool("Name") - obtain an object wrapper to the JavaScript extensions of 
 * certain PHP.Gt PageTools.
 *
 * Note that the global GT object acts as a shorthand too:
 * GT(callback) - execute the callback function on the DOM ready event.
 * GT("selector") - shorthand to selecting a DOM element.
 *
 * Gt.js provides helper functions on the native DOM elements, which has been
 * compatible with Google Chrom[e|ium] 12+, Mozilla Firefox 8+, Opera 11+,
 * Internet Explorer 8+. Note that if old browser support is required, the
 * helper functions should not be relied upon, and a larger library should be
 * used instead. To test your browser, visit the PHP.Gt test application in
 * the required browser. http://testapp.phpgt.com 
 */
(function() {
	// Ensures there are no compatibility issues with external libraries.
	var _$ = window.$ || null,
		_$$ = window.$$ || null,
		// An object hash used to store all templated HTML elements.
		_templates = {},
		/**
		 * GT is the global function used throughout the library.
		 * @param callback|String Either a callback function to be executed when
		 * the DOM ready event is triggered, or a CSS selector string to obtain
		 * a reference to.
		 * @param HTMLElement|NodeList (Optional) The context to query the
		 * CSS selector with.
		 */
		GT = function() {
			if(typeof arguments[0] === "function") {
				// Callback function provided, execute on DomReady event.
				return GT.ready(arguments[0], arguments[1]);
			}
			if(typeof arguments[0] === "string"
			|| arguments[0] instanceof NodeList
			|| arguments[0] instanceof HTMLElement) {
				// Return matching DomNodes from CSS selector, with an optional
				// context node as second argument.
				return GT.dom(arguments[0], arguments[1]);
			}
			throw new GT.error("Invalid GT parameters", arguments);
		},
		/**
		 * TODO: Docs.
		 */
		templateScrape = function() {
			var tmplDiv = document.getElementById("PHPGt_Template_Elements"),
				tmplDivNodeCount,
				tmpl,
				name,
				i;

			if(tmplDiv) {
				tmplDivNodeCount = tmplDiv.children.length;
				// 
				for(i = 0; i < tmplDivNodeCount; i++) {
					tmpl = tmplDiv.children[i];
					name = tmpl.getAttribute("data-template");
					_templates[name] = tmpl;
				}
				// 
				tmplDiv.parentNode.removeChild(tmplDiv);
			}
		},
		/**
		 * TODO: Docs.
		 */
		helpers = {
			"addEvent": function(name, callback, useCapture) {
				var useCapture = useCapture || false
				this.addEventListener(name, callback, useCapture);
				return this;
			},
			"addClass": function(name) {
				this.className += " " + name;
				return this;
			},
			"removeClass": function(name) {
				var match = new RegExp(name, "g");
				this.className = this.className.replace(match, "");
				return this;
			},
			"hasClass": function(name) {
				var match = new RegExp(name, "im");
				return this.className.match(match);
			},
			"remove": function() {
				this.parentNode.removeChild(this);
				return this;
			},
			"empty": function() {
				while(this.hasChildNodes()) {
					this.removeChild(this.lastChild);
				}
			},
			"append": function(element) {
				this.appendChild(element);
				return element;
			},
			"prepend": function(element) {
				this.insertBefore(element, this.firstChild);
				return element;
			},
			"before": function(element) {
				this.parentNode.insertBefore(element, this);
				return element;
			},
			"after": function(element) {
				this.parentNode.insertBefore(element, this.nextSibling);
				return element;
			},
			// TODO: BUG: Getting the parent via selector gets all elements
			// of a matching selector, so obtains elements that aren't actually
			// the parent, but have a common ancestor and CSS selector.
			"parent": function(selector) {
				var elArray = [],
					el,
					found,
					result = [],
					i, j;
				if(this instanceof NodeList) {
					for(i = 0; i < this.length; i++) {
						elArray.push(this[i]);
					}
				}
				else {
					elArray.push(this);
				}

				for(i = 0; i < elArray.length; i++) {
					el = elArray[i];
					while(el.parentNode) {
						el = el.parentNode;
						found = GT(selector, el);

						if(found.length > 0) {
							// Check each found element is actually a parent.
							for(j = 0; j < found.length; j++) {
								if(this.isParent(found[j], el)) {
									result.push(found[j]);
								}
							}
							if(result.length > 0) {
								return result;
							}
						}
					}
				}
					
				return null;
			},
			"isParent": function(el, parent) {
				if(parent === el) {
					return true;
				}
				else if(el.parentNode) {
					return this.isParent(el.parentNode, parent);
				}
				else {
					return false;
				}
			}
		},
		/**
		 * TODO: Docs.
		 */
		nodeListWrap = function(me, funcName, args) {
			var i;
			for(i = 0; i < me.length; i++) {
				me[i][funcName].apply(me[i], args);
			}
		},
		/**
		 * TODO: Docs.
		 */
		addHelpers = function() {
			Object.keys(helpers).map(function(key) {
				Element.prototype[key] = helpers[key];
				NodeList.prototype[key] = function() {
					nodeListWrap(this, key, arguments);
				};
				Array.prototype[key] = function() {
					nodeListWrap(this, key, arguments);
				}
			});
		};

	/**
	 * TODO: Docs.
	 */
	GT.error = function(message) {
		var that = this;
		this.name = "GtErrorException";
		this.message = this.name + ": " + message;
		this.arguments = Array.prototype.pop.apply(arguments);
		this.toString = function() {
			return that.message;
		}
	};

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
	 * TODO: Docs.
	 * Provide REST access to public webservices.
	 */
	GT.api = function(name) {

	};

	/**
	 * Wrapper to querySelectorAll method. Pass an optional context node to
	 * perform a query selection within that node.
	 *
	 * @param string selector The CSS selector to find.
	 * @return DomNodeList An array containing the matching elements.
	 */
	GT.dom = function(selector) {
		var context = document,
			i, len, nodes, res;
		if(arguments.length > 1) {
			if(arguments[0] instanceof String) {
				selector = arguments[0];
			}
			if(arguments[1] instanceof String) {
				selector = arguments[1];
			}
			if(arguments[0] instanceof HTMLElement) {
				context = arguments[0];
				selector = arguments[1];
			}
			if(arguments[1] instanceof HTMLElement) {
				context = arguments[1];
			}
			if(arguments[0] instanceof NodeList) {
				context = arguments[0][0];
				selector = arguments[1];
			}
			if(arguments[1] instanceof NodeList) {
				context = arguments[1];
			}
		}

		if(context instanceof Node) {
			// A single element.
			return context.querySelectorAll(selector);
		}
		else {
			// Assumed another NodeList.
			len = context.length;
			result = [];
			for(i = 0; i < len; i++) {
				nodes = context[i].querySelectorAll(selector);
				nodes = Array.prototype.slice.call(nodes);
				result = Array.prototype.slice.call(result);
				result = nodes.concat(result);
			}

			return result;
		}
	};

	/**
	 * TODO: Docs.
	 */
	GT.template = function(name) {
		if(_templates.hasOwnProperty(name)) {
			return _templates[name].cloneNode(true);
		}
		throw new GT.error("Invalid template item", arguments);
	};

	/**
	 * TODO: Docs.
	 * Load and use named tool, providing a wrapper.
	 */
	GT.tool = function(name) {

	};

	/**
	 * Provides a really simple ajax library, intended for modern browsers.
	 * Will automatically parse the response, converting into JSON object when
	 * possible.
	 *
	 * @param string url The url to request, with parameters in the query string
	 * for GET and POST.
	 * @param function callback The function to call when response is ready.
	 * @return XMLHttpRequest The XHR object.
	 */
	GT.ajax = new function(url, callback) {
		var req = function(url, callback, method) {
			var xhr,
				method = method.toUpperCase();
			// Provide compatibility with older IE.
			if(window.XMLHttpRequest) {
				xhr = new XMLHttpRequest();
			}
			else {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			}
			xhr.open(method, url, true);

			if(method === "POST") {
				httpRequest.setRequestHeader(
					"Content-Type", "application/x-www-form-urlencoded");
			}

			xhr.onreadystatechange = function() {
				var response;
				if(xhr.readyState === 4) {
					if(callback) {
						response = xhr.response;
						// Quick and dirty JSON detection (skipping real
						// detection).
						if(xhr.response[0] === "{" || xhr.response[0] === "[") {
							// Real JSON detection (slower).
							try {
								response = JSON.parse(xhr.response);
							}
							catch(e) {}
						}
						// Call the callback function, passing the response. If
						// response is in JSON format, the response will
						// automatically be parsed into an Object.
						callback(response, xhr);
					}
				}
			};

			xhr.send();
			return xhr;
		};
		/**
		 * TODO: Docs.
		 */
		this.get = function(url, callback) {
			return req(url, callback, "get");
		};
		/**
		 * TODO: Docs.
		 */
		this.post = function(url, callback) {
			return req(url, callback, "post");
		};
	};

	GT.setCookie = function(name, value, days) {
		var date, expires = "";
		if(days) {
			date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = "; expires=" + date.toGMTString();
		}
		document.cookie = name + "=" + value + expires + "; path=/";
	};

	GT.getCookie = function(name) {
		var nameEQ = name + "=",
			ca = document.cookie.split(";"),
			i, c;
		for(i = 0; i < ca.length; i++) {
			c = ca[i];
			while(c.charAt(0) == " ") {
				c = c.substring(1, c.length);
			}
			if(c.indexOf(nameEQ) === 0) {
				return c.substring(nameEQ.length, c.length);
			}
		}

		return null;
	};

	GT.removeCookie = function(name) {
		GT.setCookie(name, "", -1);
	};
	// Export the GT variable to the global context.
	window.GT = GT;

	// Perform automatic template collection.
	// The template elements are provided by PHP.Gt just before DOM flushing.
	GT(templateScrape);
	GT(addHelpers);
}());