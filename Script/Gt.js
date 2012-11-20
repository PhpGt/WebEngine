/**
 * GT core JavaScript file. Allows accessing applications' APIs, DOM elements,
 * templated elements and PageTools' JavaScript functions using simple syntax.
 *
 * GT is developed by Greg Bowler / PHP.Gt team.
 * Documentation: http://php.gt/GtJs.html
 *
 * Provided as standard:
 * GT.api("Name") - manipulate the application's API.
 * GT.dom("selector") - obtain a reference to GTDOM element, with helpers.
 * GT.template("Name") - obtain a cloned GTDOM element, taken from the template.
 * GT.tool("Name") - obtain an object wrapper to the JavaScript extensions of 
 * certain PHP.Gt PageTools.
 *
 * Note that the global GT object acts as a shorthand too:
 * GT(callback) - execute the callback function on the DOM ready event.
 * GT("selector") - shorthand to selecting a GTDOM element.
 *
 * Gt.js provides helper functions on the DOM elements by wrapping all elements
 * within the GT.DOM namespace.
 * Compatible with Google Chrom[e|ium] 10+, Mozilla Firefox 8+, Opera 11+,
 * Internet Explorer 8+, Safari 5+.
 */
;(function() {
var _readyQueue = [],		// Stores a list of callbacks to invoke on DOMReady.
	_templates = {},		// KVP of ttemplated DOM Elements.
	_activeXhr = 0,
/**
 * This function represents the function that is attached to the window as 
 * window.GT, and also acts as a shorthand function for many of the features
 * of this library.
 *
 * Pass a string, and it will be treated as a CSS selector, returning a
 * GT.domElCollection containing all matching GT.domElement objects. The context
 * of the selection can be passed into the second argument as any representation
 * of DOM Elements, native or GT.
 *
 * Pass a function, and the function will be added to the loadQueue and invoked
 * on the browser's DOM Ready event. A second argument can be passed in as a 
 * string or regular expression to match on the current window.location.href.
 * If there is no match, the function will be ignored.
 */
_GT = function() {
	if(GT.typeOf(arguments[0]) === "function") {
		return readyAdd(arguments[0], arguments[1]);
	}
	if(GT.typeOf(arguments[0]) === "string") {
		return dom(arguments[0], arguments[1]);
	}
	if(GT.instanceOf(arguments[0], GT.baseType("NodeList"))
	|| GT.instanceOf(arguments[0], GT.baseType("Node")) ) {
		console.log("Returning a GT.dom.element");
		return;
	}

	throw new GT.error("Invalid GT parameters.", arguments);
},

/**
 * As browser support for HTMLElement and Node varies across the mainstream
 * browsers, DomElement wraps all DOM interaction and provides a normalised 
 * API across all browsers.
 *
 * DomElement is exposed on GT.dom.element and can be constructed as
 * new GT.dom.element, and is returned by using any of the DOM manipulation
 * functions within GT. 
 */
DomElement = function(el, attrObj, value) {
	var that = this,
		node = null,
		prop,
		elementObject;

	if(GT.instanceOf(el, "Node")) {
		node = el;
	}
	else if(GT.typeOf(el) === "string") {
		node = document.createElement(el);
	}
	else {
		throw new GT.error(
			"DomElement constructor passed invalid element parameter", el);
	}

	if(GT.typeOf(attrObj) === "object") {
		for(prop in attrObj) {
			if(!attrObj.hasOwnProperty(prop)) {
				continue;
			}
			node.setAttribute(prop, attrObj[prop]);
		}
	}
	else if(GT.typeOf(attrObj) === "string") {
		// This allows a new element to be created by ignoring the attrArray
		// parameter: GT.dom.create("p", "This is a test");
		value = attrObj;
	}

	if(value) {
		node.textContent = value;
	}

	// Attach all property listeners.
	for(prop in _domPropHandlers) {
		if(!_domPropHandlers.hasOwnProperty(prop)) {
			continue;
		}
		if(!_domPropHandlers[prop]) {
			continue;
		}
		node.__defineGetter__(prop, _domPropHandlers[prop].get);
		//node.__defineSetter__(prop, _domPropHandlers[prop].set);
		(function(c_prop) {
			node.__defineSetter__(c_prop, function(val) {
				var getter = this.__lookupGetter__(c_prop),
					setter = this.__lookupSetter__(c_prop),
					oldVal = this[c_prop];
				delete this[c_prop];
				this[c_prop] = oldVal;
				_domPropHandlers[c_prop].set.call(this, val);
				this.__defineGetter__(c_prop, getter);
				this.__defineSetter__(c_prop, setter);
			});
		})(prop);
	}

	// Add DomElement functions:
	for(prop in _domElementFunctions) {
		if(!_domElementFunctions.hasOwnProperty(prop)) {
			continue;
		}

		if(!node.constructor.prototype[prop]) {
			node.constructor.prototype[prop] = _domElementFunctions[prop];
		}
	}

	return node;
},

/**
 * Represents a colelction of GT.dom.element objects, which can be iterated
 * as an array. Can be built from a single element, a NodeList, or an array of
 * native Dom elements.
 */
DomElementCollection = function(elementList) {
	var elementListLen = elementList.length,
		i = 0,
		domElement,
		domElementArray = [];
	for(; i < elementListLen; i++) {
		// Ensure the current node is wrapped in a GT DomElement.
		domElement = elementList[i];
		if(GT.typeOf(elementList[i]) !== "domelement") {
			domElement = new DomElement(domElement);
		}
		domElementArray.push(new DomElement(elementList[i]));
	}

	return domElementArray;
},

/**
 * A list of getter and setter functions to be applied on all matching
 * properties, enforcing W3C standards across non-compliant browsers.
 */
_domPropHandlers = {
	"textContent": { "get": function() {
		var clone = this.cloneNode(true);
		return clone.textContent || clone.innerText;
	}, "set": function(val) {
		this.innerText = val;
		return this.textContent = val;
	}},
	"children": { "get": function() {
		var clone = this.cloneNode(true);
		return new DomElementCollection(clone.children);
	}, "set": function(val) {
		console.log("setting children");
	}},

	"classList": { "get": function() {
		var that = this;
		return function() {
			var _classList = that.className.split(" ");

			_classList.contains = function(className) {
				return _classList.indexOf(className) >= 0;
			};
			_classList.add = function(className) {
				var classRegExp = new RegExp("(^| )" + className + "( |$)");
				if(!classRegExp.test(that.className)) {
					that.className = (
						that.className + " " + className).trim();
				}
				return className;
			};
			_classList.remove = function(className) {
				var classRegExp = new RegExp("(^| )" + className + "( |$)");
				if(classRegExp.test(that.className)) {
					that.className = that.className.replace(
						classRegExp, " ").trim();
				}
				return className;
			};
			_classList.toggle = function(className) {
				if(_classList.contains(className)) {
					_classList.remove(className);
				}
				else {
					_classList.add(className);
				}
				return className;
			};
			_classList.item = function(index) {
				return _classList[index];
			};
			
			return _classList;
		}();
	}, "set": function(val) {
		return;
	}},

	// Test:
	"madeUpProperty": { "get": function() {
		return "this is made up";
	}, "set": function(val) {
		return;
	}},
},

/**
 * List of functions to be added on the GT.dom object, adding useful
 * capabilities to the DOM.
 */
_domFunctions = {
	"create": function(el, attrArray, value) {
		return "CREATED ELEMENT";
	},
},

/**
 * List of functions to be added on GT.dom.element objects.
 */
_domElementFunctions = {
	// Legacy browser functions (will not fire on compliant browsers):
	"addEventListener": function(event, callback) {
		var that = this;
		if(!this.attachEvent) {
			throw new GT.error("Cannot add event listener or attach event");
		}
		this.attachEvent("on" + event.toLowerCase(), function(e) {
			var e = e || window.event;
			// Build up event functionality for legacy browsers.
			if(!e.preventDefault) {
				e.preventDefault = function() {
					e.returnValue = false;
					e.cancelBubble = true;
				}
			}
			callback.call(that, e);
		});
	},

	"appendChild": function(child) {
	},
	"appendChildAfter": function(child) {
	},
	"appendChildBefore": function(child) {
	},

	"getAttribute": function(name) {
	},
	"setAttribute": function(name, value) {
	},
	"hasAttribute": function(name) {
	},
},
/**
 * Element map functions listed here are mapped directly to functions in the
 * _domElementFunctions object of the same name. Each element within the
 * collection is iterated over. The returned result is the combination of all
 * elements within the collection. For example, .hasClass will return true if
 * any of the elements have the specified class name.
 */
_domElementCollectionFunctions = {
	"fnMapAll": [
		"addClass",
		"removeClass",
		"toggleClass",
		"hasClass",
	],
	"fnMapFirst": [
		"appendChild",
		"appendChildAfter",
		"appendChildBefore",
		"getAttribute",
		"setAttribute",
		"hasAttribute",
	],
},

_domElementAccessorES3 = function() {
	var
	defineProp = Object.defineProperty,
	getProp    = Object.getOwnPropertyDescriptor,

	// methods being implemented
	methods    = [
		"__defineGetter__", "__defineSetter__", "__lookupGetter__", "__lookupSetter__"
	],

	// objects to implement legacy methods onto their prototypes
	// Object.prototype[method] doesn't work on everything for IE
	extend     = [Object, String, Array, Function, Boolean, Number,
	             RegExp, Date, Error, Element, Window, HTMLDocument],
	len        = extend.length,
	proto      = "prototype",
	extendMethod = function (method, fun) {
		var i = len;
		if (!(method in {})) {
			while (i--) {
				extend[i][proto][method] = fun;
			}
		}
	};

	if (defineProp) {
		extendMethod(methods[0], function (prop, fun) { // __defineGetter__
			defineProp(this, prop, { get: fun });
		});

		extendMethod(methods[1], function (prop, fun) { // __defineSetter__
			defineProp(this, prop, { set: fun });
		});
	}

	if (getProp) {
		extendMethod(methods[2], function (prop) { // __lookupGetter__
			return getProp(this, prop).get ||
				getProp(this.constructor[proto], prop).get; // look in prototype too
		});
		extendMethod(methods[3], function (prop) { // __lookupSetter__
			return getProp(this, prop).set ||
				getProp(this.constructor[proto], prop).set; // look in prototype too
		});
	}
},
_domElementAccessorES5 = function() {
	var ObjectProto = Object.prototype,
	defineGetter = ObjectProto.__defineGetter__,
	defineSetter = ObjectProto.__defineSetter__,
	lookupGetter = ObjectProto.__lookupGetter__,
	lookupSetter = ObjectProto.__lookupSetter__,
	hasOwnProp = ObjectProto.hasOwnProperty;

	if (defineGetter && defineSetter && lookupGetter && lookupSetter) {

		if (!Object.defineProperty) {
			Object.defineProperty = function (obj, prop, descriptor) {
				if (arguments.length < 3) { // all arguments required
					throw new TypeError("Arguments not optional");
				}

				prop += ""; // convert prop to string

				if (hasOwnProp.call(descriptor, "value")) {
					if (!lookupGetter.call(obj, prop) && !lookupSetter.call(obj, prop)) {
						// data property defined and no pre-existing accessors
						obj[prop] = descriptor.value;
					}

					if ((hasOwnProp.call(descriptor, "get") ||
					     hasOwnProp.call(descriptor, "set"))) 
					{
						// descriptor has a value prop but accessor already exists
						throw new TypeError("Cannot specify an accessor and a value");
					}
				}

				// can't switch off these features in ECMAScript 3
				// so throw a TypeError if any are false
				if (!(descriptor.writable && descriptor.enumerable && descriptor.configurable))
				{
					throw new TypeError(
						"This implementation of Object.defineProperty does not support" +
						" false for configurable, enumerable, or writable."
					);
				}

				if (descriptor.get) {
					defineGetter.call(obj, prop, descriptor.get);
				}
				if (descriptor.set) {
					defineSetter.call(obj, prop, descriptor.set);
				}

				return obj;
			};
		}

		if (!Object.getOwnPropertyDescriptor) {
			Object.getOwnPropertyDescriptor = function (obj, prop) {
				if (arguments.length < 2) { // all arguments required
					throw new TypeError("Arguments not optional.");
				}

				prop += ""; // convert prop to string

				var descriptor = {
					configurable: true,
					enumerable  : true,
					writable    : true
				},
				getter = lookupGetter.call(obj, prop),
				setter = lookupSetter.call(obj, prop);

				if (!hasOwnProp.call(obj, prop)) {
					// property doesn't exist or is inherited
					return descriptor;
				}
				if (!getter && !setter) { // not an accessor so return prop
					descriptor.value = obj[prop];
					return descriptor;
				}

				// there is an accessor, remove descriptor.writable;
				// populate descriptor.get and descriptor.set (IE's behavior)
				delete descriptor.writable;
				descriptor.get = descriptor.set = undefined;

				if (getter) {
					descriptor.get = getter;
				}
				if (setter) {
					descriptor.set = setter;
				}

				return descriptor;
			};
		}

		if (!Object.defineProperties) {
			Object.defineProperties = function (obj, props) {
				for (var prop in props) {
					if (hasOwnProp.call(props, prop)) {
						Object.defineProperty(obj, prop, props[prop]);
					}
				}
			};
		}
	}
},



/**
 * To allow certain JavaScript features to be usable across all mainstream
 * browsers, shims are functions that are attached to particular objects' 
 * prototypes. Each key in the _shims object represents the object to
 * extend, and the properties within each object's keys are the named functions
 * to assign. This is done in the _addShims function.
 */
_shims = {
	"Object": {
		
	},
	"Array": {
		/**
		 * Returns the first index at which a given element can be found in 
		 * the array, or -1 if it is not present.
		 */
		"indexOf": function(searchElement /*, fromIndex */ ) {
	        "use strict";
	        if (this == null) {
	            throw new TypeError();
	        }
	        var t = Object(this);
	        var len = t.length >>> 0;
	        if (len === 0) {
	            return -1;
	        }
	        var n = 0;
	        if (arguments.length > 1) {
	            n = Number(arguments[1]);
	            if (n != n) { // shortcut for verifying if it's NaN
	                n = 0;
	            } else if (n != 0 && n != Infinity && n != -Infinity) {
	                n = (n > 0 || -1) * Math.floor(Math.abs(n));
	            }
	        }
	        if (n >= len) {
	            return -1;
	        }
	        var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);
	        for (; k < len; k++) {
	            if (k in t && t[k] === searchElement) {
	                return k;
	            }
	        }
	        return -1;
	    },
	},
	"String": {
		"trim": function() {
			return this.replace(/^\s+|\s+$/g, '');
		},
	},
},

/**
 * Called internally, attaches JavaScript versions of native functionality to
 * older / non-supportive browsers, or attaches special helper functions to all
 * browsers.
 *
 * Iterates over all functions in the _shims object, checks if the function
 * already exists and if not, attaches it to the relevant prototype.
 */
_addShims = function() {
	var obj, 	// Name of the object to extend.
		def,	// Name of the function definition to add.
		proto;	// Reference to the object prototype's function. 
	for(obj in _shims) {
		if(!_shims.hasOwnProperty(obj)) {
			continue;
		}

		for(def in _shims[obj]) {
			if(!_shims[obj].hasOwnProperty(def)) {
				continue;
			}

			proto = window[obj].prototype[def];
			if(proto) {
				continue;
			}

			// W3C compliant browsers:
			try {
				Object.defineProperty(window[obj].prototype, def, {
					"enumerable": false,
					"configurable": true,
					"writable": false,
					"value": _shims[obj][def]
				});
			}
			// Legacy browsers:
			catch(e) {
				window[obj].prototype[def] = _shims[obj][def];
			}
		}
	}
},

/**
 * Adds a callback function to the DOM ready queue, stored as an array in
 * _readyQueue. Callbacks will only be added to the readyQueue if the given page
 * matches the current URL. The page match can either be a string or a RegExp.
 * @param {function} callback The function to invoke when DOMReady event fires.
 * @param {string|RegExp} [page] The URL to match in order to add to the queue.
 * @return {bool} True if the callback was added.
 */
readyAdd = function(callback, page) {
	var pathname = window.location.pathname;

	if(page) {
		if(GT.typeOf(page) === "regexp") {
			if(!page.test(pathname)) {
				return false;
			}
		}
		else {
			if(page !== pathname) {
				return false;
			}
		}
	}

	_readyQueue.push(callback);
	return true;
},

/**
 * Invokes all callbacks stored in the ready queue.
 * @return {int} The number of callbacks invoked.
 */
_readyInvoke = function() {
	var readyQueueLen = _readyQueue.length,
		i = 0;
	for(; i < readyQueueLen; i++) {
		_readyQueue[i]();
	}

	return readyQueueLen;
},

/**
 * Internal function.
 * Creates an event listener on the DOM Ready event, and inbokes the load queue
 * when it fires.
 */
_readyListen = function() {
	// W3C compliant browsers:
	if(document.addEventListener) {
		document.addEventListener("DOMContentLoaded", function() {
			document.removeEventListener(
				"DOMContentLoaded", 
				arguments.callee,
				false
			);
			_readyInvoke();

		}, false);
	}
	// Legacy browsers:
	else if(document.attachEvent) {
		document.attachEvent("onreadystatechange", function() {
			if(document.readyState === "complete") {
				document.detachEvent(
					"onreadystatechange",
					arguments.callee
				);
				_readyInvoke();
				return;
			}
		});
	}
	else {
		throw new GT.error("Cannot add DOM Ready event listener.");
	}

	return;
},

/**
 * Internal function.
 * Once PHP.Gt has processed any template elements, they will be placed in a 
 * special hidden DIV in the document.body, retrieved by this function.
 * In projects that do not use PHP.Gt, the templating functionality can still
 * be used because of this function.
 *
 * Called internally, it adds all template elements within the special PHP.Gt
 * DIV to the internal template object, then removes any existing elements 
 * with a data-template attribute, storing them in the internal object.
 *
 * The template list object is exposed by the GT.template function.
 */
_templateScrape = function() {
	var toScrape, toScrapeLength,
		tmplDiv = document.getElementById("PhpGt_Template_Elements"),
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
			tmpl.removeAttribute("data-template");
			_templates[name] = tmpl;
		}
		// Remove the template div from the DOM.
		tmplDiv.parentNode.removeChild(tmplDiv);
	}

	// If Gt.js is being used without PHP.Gt, the original template
	// elements will still be present in the DOM - scrape them here.
	toScrape = GT("[data-template]");
	toScrapeLength = toScrape.length;

	if(toScrape && toScrapeLength > 0) {
		for(i = 0; i < toScrapeLength; i++) {
			tmpl = toScrape[i];
			name = tmpl.getAttribute("data-template");
			tmpl.removeAttribute("data-template");
			_templates[name] = tmpl.cloneNode(true);
			tmpl.parentNode.removeChild(tmpl);
		}

	}
},

/**
 * Internal function.
 * Creates a listener on the DOM ready event and invokes the load queue when it
 * fires. Compatible with old IE browsers too.
 */
_attachLoadQueue = function() {

},

/**
 * Internal function.
 * Called when the DOM ready event fires. Executes all callbacks in the load 
 * queue.
 */
_invokeLoadQueue = function() {

},

querySelector = function() {
	return document.querySelector.apply(document, arguments);
},
querySelectorAll = function() {
	return document.querySelectorAll.apply(document, arguments);
},

/**
 * Creates an exception object with an optional object to report to the console.
 *
 * @param {string} message The message to be logged.
 * @param {object} [obj] The associated object, or array of objects.
 * @return {object} The exception object, to be thrown.  
 */
error = function(message, obj) {
	var that = this;
	this.name = "GtErrorException";
	this.message = this.name + ": " + message;
	this.arguments = Array.prototype.pop.apply(arguments);
	this.toString = function() {
		return that.message;
	}
},

/**
 * Returns a string representation of the type of the passed in object.
 */
typeOf = function(obj) {
	if(typeof obj === "undefined") { 
		return "undefined";
	}
	return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase();
},
/**
 * Checks the prototype tree of the passed in object. The test subject could be
 * a string representation of the type to check for.
 */
instanceOf = function(obj, test) {
	var ref = obj,
		test = test.toLowerCase(),
		newRef,
		count = 0;
	
	// Check parameters are correctly typed.
	if(GT.typeOf(test) !== "string") {
		throw new GT.error(
			"GT.instanceOf method parameters incorrect types.",
			arguments
		);
	}


	// Because of certain popular browsers not supporting W3C DOM2 standard,
	// HTMLElement and Node tests have to be handled differently.
	if(test === "node") {
		return !!(
			typeof obj == "object" 
			&& "nodeType" in obj
			&& obj.nodeType >= 1
			&& obj.nodeType <= 12 
			&& obj.cloneNode
		);
	}
	if(test === "htmlelement") {
		return !!(
			typeof obj == "object" 
			&& "nodeType" in obj
			&& obj.nodeType === 1
			&& obj.cloneNode
		);
	}

	// Recursive loop on reference.constructor, checking the type.
	while(ref && count < 100) {
		newRef = new Object(ref.constructor);
		if(newRef === ref) {
			break;
		}
		ref = newRef;
		
		if(GT.typeOf(ref) === test) {
			return true;
		}
		count++;
	}

	return false;
},

/**
 * Merges all properties from obj1, obj2, obj3, obj4, ... and returns 
 * the new object.
 * @param {object} target The object who's properties to extend.
 * @param {object} obj1 First object to take the properties from.
 * @param {object} [obj2] Second object to take the properties from.
 * @param {object} [objN] Any number of objects to take the properties from.
 * @return {object} The merged object.
 */
merge = function(target, obj1, obj2, objN) {
	var argLen = arguments.length,
		i = 1,
		prop;
	for(; i < argLen; i++) {
		for(prop in arguments[i]) {
			if(arguments[i].hasOwnProperty(prop)) {
				target[prop] = arguments[i][prop];
			}
		}
	}

	return target;
},

/**
 * Initializes a nested namespace within the GT global for use in modular
 * scripts.
 * 
 * @return {Object} The created namespace object.
 */
namespace = function() {

},

/**
 * Converts a query string to an object mapping keys and values from a given
 * string, or the current window's URI string if none is given.
 * @param {string} [queryString] String to convert.
 * @return {object} KVP of given queryString.
 */
params = function(queryString) {
	var result = {},
		key,
		value,
		arr = [],
		arrLen,
		i = 0;
	if(!queryString) {
		queryString = window.location.search;
	}
	if(queryString[0] === "?") {
		queryString = queryString.substring(1);
	}

	arr = queryString.split("&");
	arrLen = arr.length;
	for(; i < arrLen; i++) {
		key = arr[i];
		value = "";
		if(arr[i].indexOf("=") > 0) {
			key = arr[i].substring(0, arr[i].indexOf("="));
			value = arr[i].substring(arr[i].indexOf("=") + 1);
		}
		result[key] = value;
	}

	return result;
},

/**
 * Used to perform asynchronous HTTP requests. Automatically parses the response
 * by converting to JSON wherever possible. 
 * Pass no parameters to obtain the current number of active requests.
 */
http = function(uri, /* method, data, */ callback) {
	var uri = uri,
		method = "GET",
		data = null,
		callback = callback,
		obj = {},
		objStr = "",
		xhr,
		qsCharacter = "?",
		prop;
	if(arguments.length === 0) {
		return _activeXhr;
	}

	if(GT.typeOf(arguments[0]) !== "string") {
		throw new GT.error("Invalid URI specified to xhr.", uri);
	}
	// Allow for lazy parameters:
	if(GT.typeOf(arguments[1]) === "string") {
		method = arguments[1];
	}
	else if(GT.typeOf(arguments[1]) === "function") {
		callback = arguments[1];
	}
	else if(GT.typeOf(arguments[1]) === "object") {
		if("error" in arguments[1]
		|| "progress" in arguments[1]
		|| "load" in arguments[1]) {
			callback = arguments[1];
		}
		else {
			data = arguments[1];
		}
	}
	if(GT.typeOf(arguments[2]) === "function") {
		callback = arguments[2];
	}
	else if(GT.typeOf(arguments[2]) === "object") {
		if("error" in arguments[2]
		|| "progress" in arguments[2]
		|| "load" in arguments[2]) {
			callback = arguments[2];
		}
		else {
			data = arguments[2];
		}
	}
	if(GT.typeOf(arguments[3]) === "function"
	|| GT.typeOf(arguments[3]) === "object") {
		callback = arguments[3];
	}
	else if(arguments[3]) {
		throw new GT.error("Invalid xhr arguments.", arguments);
	}

	if(window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
	}
	else if(window.ActiveXObject) {
		xhr = new ActiveXObject("Microsoft.XMLHTTP");
	}
	else {
		throw new GT.error("XMLHttpRequest cannot be created.");
	}

	method = method.toUpperCase();

	if(uri.indexOf("?") >= 0) {
		objStr = uri.substring(uri.indexOf("?") + 1);
		uri = uri.substring(0, uri.indexOf("?"));
	}
	if(GT.typeOf(data) === "string") {
		obj = GT.params(data);
	}
	else {
		obj = data;
	}

	if(method === "GET"
	|| method === "DELETE") {
		if(GT.typeOf(data) !== "string") {
			for(prop in data) {
				if(!data.hasOwnProperty(prop)) {
					continue;
				}
				if(objStr.length > 0) {
					objStr += "&";
				}
				objStr += encodeURIComponent(prop);
				objStr += "=";
				objStr += encodeURIComponent(data[prop]);
			}
			obj = objStr;
		}
	}

	if(method === "POST"
	|| method === "PUT") {
		xhr.open(method, uri, true);
	}
	else {
		// TODO: This check seems obsolete - tidy.
		if(uri.indexOf("?") >= 0) {
			qsCharacter = "&";
		}
		xhr.open(method, uri + qsCharacter + objStr, true);
	}

	if(method === "POST") {
		xhr.setRequestHeader(
			"Content-Type", "application/x-www-form-urlencoded");
	}

	// Allow multiple callbacks to be passed as an object.
	if(GT.typeOf(callback) === "object") {
		if("progress" in callback) {
			xhr.addEventListener("progress", callback.progress);
		}
		if("error" in callback) {
			xhr.addEventListener("error", callback.error);
		}			
	}

	// Check readyState, for legacy browsers (avoiding early callbacks).
	xhr.onreadystatechange = function() {
		var response;
		if(xhr.readyState === 4) {
			_activeXhr --;
			response = xhr.response || xhr.responseText;

			// Quick and dirty JSON detection (skipping real detection 
			// first for efficiency).
			if(response[0] === "{" || response[0] === "[") {
				// Perform real JSON detection (slower).
				try {
					response = JSON.parse(response);
				}
				catch(e) {}
			}

			if(GT.typeOf(callback) === "function") {
				callback.call(xhr, response);
			}
			else if(GT.typeOf(callback) === "object") {
				if("load" in callback) {
					callback.load.call(xhr, response);
				}
			}
		}
	};

	_activeXhr ++;

	if(method === "POST"
	|| method === "PUT") {
		xhr.send(objStr);
	}
	else {
		xhr.send();
	}

	return xhr;
};

/**
 * Used as a shorthand function to interact with PHP.Gt's public HTTP APIs.
 */
api = function() {

},

/**
 * Wraps CSS selectors to GT.dom.elementCollection objects.
 * @param {string} selector The CSS selector to search the DOM for.
 * Alternatively, a reference to a native HTML element, array of native HTML
 * elements, or a DOM Node List can be passed as the first argument, which will 
 * be converted into a GT.dom.elementCollection.
 * @param {GT.dom.element} [context] The context to execute the CSS selector in.
 * This defaults to window.document.
 */
// TODO: Context could be a NodeList / DomElementCollection, in which case,
// it should use the first element as the context.
dom = function(selector, context) {
	var context = context || document,
		selection = context.querySelectorAll(selector);

	return new DomElementCollection(selection);
},

/**
 * Obtains a cloned reference to a template element.
 */
template = function(name) {
	return _templates[name].cloneNode(true);
},

tool = function() {

},

ui = function() {

},
ui.dropdownMenu = function(button, name, contents, e) {
	var menu,
		name = name || "",
		contents = contents || null,
		childLen = button.children.length,
		i = 0,
		btnStyle, btnWidth,
		helperClass = "gt-dropdownWidthHelper",
		helperRand,
		newStyle;

	if(e) {
		if(e.target !== button) {
			return;
		}
	}
	for(0; i < childLen; i++) {
		if(button.children[i].classList.contains("menu")) {
			menu = button.children[i];
		}
	}

	if(!menu) {
		menu = document.createElement("div");
		menu.classList.add("menu", name);
		if(contents) {
			menu.appendChild(contents);
		}
		button.appendChild(menu);
		button.classList.add("active");

		// Ensure the psedo-element gets the correct width of the clicked button
		if(button.classList.contains(helperClass)) {
			helperRand = ("-" + (Math.random() * 1000)).replace(".", "_");
			button.classList.add(helperClass + helperRand);
			btnStyle = getComputedStyle(button);
			btnWidth = (parseInt(btnStyle.width, 10) - 2) + "px";
			newStyle = document.createElement("style");
			newStyle.type = "text/css";
			newStyle.innerText = "button." + helperClass + helperRand
				+ " div.menu::after { width: " + btnWidth + " !important; }"
			document.head.appendChild(newStyle);
		}

		var cancelClick = function(e) {
			return;

			// TODO: When GT DOM wrapper is made, this will be a piece of cake.
			if(GT(e.target).hasParent(menu)) {
				return;
			}
			menu.remove();
			button.classList.remove("active");
			window.removeEventListener("mousedown", arguments.callee);
		};

		// Add listener to click off the dropdown menu.
		window.addEventListener("mousedown", cancelClick);
	}
	else {
		menu.parentElement.removeChild(menu);
		button.classList.remove("active");
	}
},
/**
 * Provides linear interpolation between two points with optional smoothing.
 */
ui.lerp = function(start, end, scalar, smoothing) {
	var interpolant;
	if(smoothing === true) {
		scalar = GT.ui.smooth(scalar);
	}
	else if(typeof smoothing === "function") {
		scalar = smoothing(scalar);
	}
	interpolant = (end - start) * scalar;

	return interpolant;
},

/**
 * Converts a scalar into a smoothed scalar.
 */
ui.smooth = function(scalar) {
	return (-Math.cos(Math.PI * scalar) + 1) / 2;
};

// Attach the GT object to the window, exposing the namespace as a global.
window.GT = _GT;

// Extend any objects required for full functionality.
_addShims();
_domElementAccessorES3();
_domElementAccessorES5();

// Build the GT object to expose public methods.
GT.error = error;
GT.namespace = namespace;
GT.typeOf = typeOf;
GT.instanceOf = instanceOf;
GT.merge = merge;
GT.params = params;
GT.ready = readyAdd;
GT.template = template;

GT.dom = dom;
GT.dom.element = DomElement;
GT.dom.elementCollection = DomElementCollection;

// Extend GT.dom capabilities.
GT.merge(GT.dom, _domFunctions);

// Export globals:
window.go = readyAdd;
window.api = "TODO";
window.qs = querySelector;
window.qsa = querySelectorAll;
window.template = template;
window.tool = "TODO";
window.http = http;
window.ui = ui;

// GT is now ready, attach the ready listener to the DOM.
_readyListen();

// Attach any required internal functions to the DOM Ready event.
readyAdd(_templateScrape);
return;

})();