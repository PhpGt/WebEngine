/**
 * GT core JavaScript file. Allows accessing applications' APIs, DOM elements,
 * templated elements and PageTools' JavaScript functions using similar syntax.
 *
 * GT is developed by Greg Bowler / PHP.Gt team.
 * Code/licensing: http://phpgt.com
 * Documentation: http://phpgt.com/Docs/ClientSide/GtJs.html
 *
 * Provided as standard:
 * GT.api("Name") - manipulate the application's REST API.
 * GT.dom("selector") - obtain a reference to the native DOM element.
 * GT.template("Name") - obtain a cloned DOM element, taken from the templates.
 * GT.tool("Name") - obtain an object wrapper to the JavaScript extensions of 
 * certain PHP.Gt PageTools.
 *
 * Note that the global GT object acts as a shorthand too:
 * GT(callback) - execute the callback function on the DOM ready event.
 * GT("selector") - shorthand to selecting a DOM element.
 */
(function() {
	var _$ = window.$ || null,
		_$$ = window.$$ || null,
		_templates = {},
		GT = function() {
			if(typeof arguments[0] === "function") {
				// Callback function provided, execute on DomReady event.
				return GT.ready(arguments[0], arguments[1]);
			}
			if(typeof arguments[0] === "string"
			|| arguments[0] instanceof NodeList
			|| arguments[0] instanceof HTMLElement) {
				// Return matching DomNodes from CSS selector.
				return GT.dom(arguments[0], arguments[1]);
			}
			throw new GT.error("Invalid GT parameters", arguments);
		},
		templateScrape = function() {
			var tmplDiv = document.getElementById("PHPGt_Template_Elements"),
				tmplDivNodeCount,
				tmpl,
				name,
				i;

			if(tmplDiv) {
				tmplDivNodeCount = tmplDiv.children.length;
				for(i = 0; i < tmplDivNodeCount; i++) {
					tmpl = tmplDiv.children[i];
					name = tmpl.getAttribute("data-template");
					_templates[name] = tmpl;
				}
				tmplDiv.parentNode.removeChild(tmplDiv);
			}
		},
		helpers = {
			"addClass": function(name) {
				this.className += " " + name;
				return this;
			},
			"removeClass": function(name) {
				this.className.replace(name, "");
				return this;
			},
			"hasClass": function(name) {
				var match = new Regexp(name, "im");
				return classname.match(match);
			},
			"remove": function() {
				this.parentNode.removeChild(this);
				return this;
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
			}
		},
		nodeListWrap = function(me, funcName, args) {
			var i;
			for(i = 0; i < me.length; i++) {
				me[i][funcName].apply(me[i], args);
			}
		},
		addHelpers = function() {
			Object.keys(helpers).map(function(key) {
				Element.prototype[key] = helpers[key];
				NodeList.prototype[key] = function() {
					nodeListWrap(this, key, arguments);
				}
			});
			/*Element.prototype.addClass = addClass;
			Element.prototype.removeClass = removeClass;
			Element.prototype.hasClass = hasClass;

			NodeList.prototype.addClass = function(name) {
				mapFunc(addClass, name)
			};
			NodeList.prototype.removeClass = removeClass;
			NodeList.prototype.hasClass = hasClass;*/
		};

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
	 * TODO: Provide REST access to public webservices.
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
		var context = document;
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
				context = arguments[1][0];
			}
		}
		return context.querySelectorAll(selector);
	};

	GT.template = function(name) {
		if(_templates.hasOwnProperty(name)) {
			return _templates[name].cloneNode(true);
		}
		throw new GT.error("Invalid template item", arguments);
	};

	/**
	 * TODO: Load and use named tool, providing a wrapper.
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
					console.log(xhr);
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
						callback(response, xhr);
					}
				}
			};

			xhr.send();
			return xhr;
		};
		this.get = function(url, callback) {
			return req(url, callback, "get");
		};
		this.post = function(url, callback) {
			return req(url, callback, "post");
		};
	};
	window.GT = GT;

	// Perform automatic template collection.
	// The template elements are provided by PHP.Gt just before DOM flushing.
	GT(templateScrape);
	GT(addHelpers);
}());