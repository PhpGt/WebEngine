/**
 * GT core JavaScript file. Allows accessing applications' APIs, DOM elements,
 * templated elements and PageTools' JavaScript functions using simple syntax.
 *
 * GT is developed by Greg Bowler / PHP.Gt team.
 * Documentation: http://php.gt/Docs/ClientSide/GtJs.html
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
 * compatible with Google Chrom[e|ium] 10+, Mozilla Firefox 8+, Opera 11+,
 * Internet Explorer 9+. Note that if old browser support is required, the
 * helper functions should not be relied upon, and a larger library should be
 * used instead. To test your browser, visit the PHP.Gt test application in
 * the required browser. http://test.php.gt 
 */
(function() {
		// All callbacks are queued ready for the DOM Ready event.
	var loadQueue = [],
		// An object hash used to store all templated HTML elements.
		_templates = {},
		_eventListeners = [],
		/**
		 * GT is the global function used throughout the library. It can be used
		 * shorthand by passing a parameter in, or can be used as a module-based
		 * object for exposing all Gt functionality.
		 *
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
		 * Internal function. PHP.Gt provides all templated elements in a
		 * special hidden div. This function picks up the contents of the div
		 * and removes it from the page once processed. Templated elements can
		 * then be accessed via GT.template().
		 */
		templateScrape = function() {
			var tmplDiv = document.getElementById("PhpGt_Template_Elements"),
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
				// Remove the template div from the DOM.
				tmplDiv.parentNode.removeChild(tmplDiv);
			}
		},

		/**
		 * Internal function. Creates an event listener on the DOM Ready event,
		 * and executes the load queue when it fires.
		 */
		attachLoadQueue = function() {
			// Attack the event listener in real browsers.
			if(document.addEventListener) {
				document.addEventListener("DOMContentLoaded", function() {
					document.removeEventListener(
						"DOMContentLoaded",
						arguments.callee,
						false
					);
					executeLoadQueue();
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
						executeLoadQueue();
						return;
					}
				});

				if(document.documentElement.doScroll && window == window.top) {
					(function(c_callback) {
						try {
							document.documentElement.doScroll("left");
						}
						catch(error) {
							setTimeout(arguments.callee, 0);
							return;
						}

						executeLoadQueue();
					})(callback);
				}
			}
		},
		/**
		 * Called when DOM Ready event fires. Executes all callbacks in the load
		 * queue.
		 */
		executeLoadQueue = function() {
			var i, len = loadQueue.length;
			for(i = 0; i < len; i++) {
				loadQueue[i]();
			}
		},
		/**
		 * Logs the deployment of the database to the JavaScript console, then
		 * removes the session cookie of where the details are stored.
		 */
		dbDeploy = function() {
			if (document.cookie.indexOf("PhpGt_DbDeploy") < 0) {
				return;
			}
			var dbDeployObj = JSON.parse(
				decodeURIComponent(GT.getCookie("PhpGt_DbDeploy")) );
			GT.removeCookie("PhpGt_DbDeploy");
			console.log(dbDeployObj);
		},
		/**
		 * Defines the helper functions to be added to native elements. Helpers
		 * are inspired from typical client-side frameworks, but Gt.js only
		 * targets modern browsers, so doesn't aim to provide cross-old-browser
		 * support.
		 */
		helpers = {
			"addEventListener": function(name, callback, useCapture) {
				var useCapture = useCapture || false,
					i;
				if(name instanceof Array) {
					for(i = 0; i < name.length; i++) {
						this.addEventListener(name[i], callback, useCapture);
					}
				}
				else {
					this.addEventListener(name, callback, useCapture);
				}
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
			"switchClass": function(remove, add, duration, callback) {
				this.removeClass(remove);
				this.addClass(add);
				if(duration && callback) {
					setTimeout(callback, duration);
				}
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
			"next": function() {
				var n = this;
				while(n.nextSibling) {
					n = n.nextSibling;
					if(n instanceof HTMLElement) {
						return n;
					}
				}
				return false;
			},
			"prev": function() {
				var p = this;
				while(p.previousSibling) {
					p = p.previousSibling;
					if(p instanceof HTMLElement) {
						return p;
					}
				}
				return false;
			},
			"replace": function(element) {
				var prev = this.prev(),
					next = this.next(),
					parent = this.parentNode;
				if(prev) {
					prev.after(element);
				}
				else if(next) {
					next.before(element);
				}
				else {
					parent.append(element);
				}
				this.remove();
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
			},
			"forEach": function(callback) {
				var elArray = [],
					el,
					i;
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
					callback.apply(el);
				}
			}
		},
		/**
		 * Used to apply a given function to every element witin a nodeList.
		 * Only used internally by helper functions.
		 */
		nodeListWrap = function(me, funcName, args) {
			var i;
			for(i = 0; i < me.length; i++) {
				me[i][funcName].apply(me[i], args);
			}
		},
		/**
		 * Adds the previously defined helper functions to the prototypes of
		 * native objects, to be able to use shortcut functions without any
		 * additional wrapper object. This allows for simple code such as 
		 * document.getElementById("test").remove();
		 */
		addHelpers = function() {
			Object.keys(helpers).map(function(key) {
				if(!Element.prototype[key]) {
					Element.prototype[key] = helpers[key];
				}
				if(!NodeList.prototype[key]) {
					NodeList.prototype[key] = function() {
						nodeListWrap(this, key, arguments);
					};
				}
				if(!Array.prototype[key]) {
					Array.prototype[key] = function() {
						nodeListWrap(this, key, arguments);
					}
				}
			});
		};

	/**
	 * Emits an event to the GT object itself. Useful for allowing modular 
	 * JavaScript files that are properly enclosed in anonymous functions to
	 * exchange information together.
	 * 
	 * Events that are emitted from GT object must have an event listener prior
	 * to the event being emitted, see GT.addEventListener.
	 */
	GT.event = function(eventName, e) {
		var i, listenerLength = _eventListeners.length;
		for(i = 0; i < listenerLength; i++) {
			if(_eventListeners[i].Name === eventName) {
				_eventListeners[i]["Callback"](e);
			}
		}
	};
	
	/**
	 * Attaches a callback function to the GT object and executes it when the
	 * given event is emitted. Events can be emitted by the GT object by calling
	 * GT.event() - this is useful for exchanging information between modular
	 * scripts.
	 */
	GT.addEventListener = function(eventName, callback) {
		_eventListeners.push({
			"Name": eventName,
			"Callback": callback
		});
	};

	/**
	 * Simply removes existing event listeners of the given name from the GT
	 * object. Note that removing an event listener in one script may cause 
	 * problems in other scripts that rely on the listener to function.
	 */
	GT.removeEventListener = function(eventName) {
		var i, listenerLength = _eventListeners.length;
		for(i = 0; i < listenerLength; i++) {
			if(_eventListeners[i].Name === eventName) {
				_eventListeners.splice(i, 1);
			}
		}
	};

	/**
	 * Displays an error message to the console.
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
	 * Adds a given callback to the loadQueue of a particular page. All
	 * callbacks in the loadQueue are triggered on the DOMReady event.
	 *
	 * Callbacks will only be added to the loadQueue if the given page matches
	 * the current URL. The page match can be either a string or Regular
	 * Expression.
	 */
	GT.ready = function(callback, page) {
		var pathname = window.location.pathname;
		
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

		loadQueue.push(callback);
	};

	/**
	 * TODO:
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
	 * Obtains a cloned reference to a named template element. Template elements
	 * are HTML elements with the data-template attribute, and are extracted
	 * from the DOM in PHP.Gt, but are re-attached in a hidden div just before
	 * the page renders. Gt.js removes these templated items and stores them in
	 * an associative array for retrieval here.
	 */
	GT.template = function(name) {
		if(_templates.hasOwnProperty(name)) {
			return _templates[name].cloneNode(true);
		}
		throw new GT.error("Invalid template item", arguments);
	};

	/**
	 * TODO:
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
				xhr.setRequestHeader(
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
		 * Executes a HTTP GET request on the given URL and passes the
		 * response to the given callback function.
		 */
		this.get = function(url, callback) {
			return req(url, callback, "get");
		};
		/**
		 * Executes a HTTP POST request on the given URL and passes the
		 * response to the given callback function.
		 */
		this.post = function(url, callback) {
			return req(url, callback, "post");
		};
	};

	/**
	 * Used to set a cookie from a client side script. Defaults to a session
	 * cookie if no days are given.
	 */
	GT.setCookie = function(name, value, days, domain) {
		var date, expires = "",
			domain = domain 
				? "; domain=" + domain
				: "";

		if(days) {
			date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = "; expires=" + date.toGMTString();
		}
		document.cookie = name + "=" + value + expires + "; path=/" + domain;
	};

	/**
	 * Retrieves a named cookie as a string, or null if the cookie does not
	 * exist or has expired.
	 */
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

	/**
	 * Removes a cookie from the cookie jar. Will not be sent with headers on
	 * the next request.
	 */
	GT.removeCookie = function(name) {
		GT.setCookie(name, "", -1);
	};
	// Export the GT variable to the global context.
	window.GT = GT;

	// Attach all helper functions to native JavaScript objects.
	addHelpers();
	dbDeploy();
	// Perform automatic template collection.
	// The template elements are provided by PHP.Gt just before DOM flushing.
	GT(templateScrape);
	// Add all callbacks to the DOMReady event, ready for execution in order.
	attachLoadQueue();
}());

/* 
 * DOMParser HTML extension 
 * 2012-02-02 
 * 
 * By Eli Grey, http://eligrey.com 
 * Public domain. 
 * NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK. 
 */  

/*! @source https://gist.github.com/1129031 */  
/*global document, DOMParser*/  

(function(DOMParser) {  
    "use strict";  
    var DOMParser_proto = DOMParser.prototype  
      , real_parseFromString = DOMParser_proto.parseFromString;

    // Firefox/Opera/IE throw errors on unsupported types  
    try {  
        // WebKit returns null on unsupported types  
        if ((new DOMParser).parseFromString("", "text/html")) {  
            // text/html parsing is natively supported  
            return;  
        }  
    } catch (ex) {}  

    DOMParser_proto.parseFromString = function(markup, type) {  
        if (/^\s*text\/html\s*(?:;|$)/i.test(type)) {  
            var doc = document.implementation.createHTMLDocument("")
              , doc_elt = doc.documentElement
              , first_elt;

            doc_elt.innerHTML = markup;
            first_elt = doc_elt.firstElementChild;

            if (doc_elt.childElementCount === 1
                && first_elt.localName.toLowerCase() === "html") {  
                doc.replaceChild(first_elt, doc_elt);  
            }  

            return doc;  
        } else {  
            return real_parseFromString.apply(this, arguments);  
        }  
    };  
}(DOMParser));