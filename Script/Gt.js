/**
 * GT core JavaScript file. Allows accessing applications' APIs, DOM elements,
 * templated elements and PageTools' JavaScript functions using simple syntax.
 *
 * GT is developed by Greg Bowler / PHP.Gt team.
 * Documentation: http://php.gt/Docs/ClientSide/GtJs.html
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
 * Internet Explorer 8+. 
 */
;(function() {
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
var _GT = function() {
	if(GT.typeOf(arguments[0]) === "function") {
		console.log("Function added.");
		return;
	}
	if(GT.typeOf(arguments[0]) === "string") {
		console.log("CSS selector");
		return;
	}
	if(GT.instanceOf(arguments[0], GT.baseType("NodeList"))
	|| GT.instanceOf(arguments[0], GT.baseType("Node")) ){
		console.log("Returning a GT.dom.element");
		return;
	}

	throw new GT.error("Invalid GT parameters.", arguments);
},

_domFunctions = {
	"create": function(el, attrArray, value) {
		return "CREATED ELEMENT";
	},
},
_domElementFunctions = {
	"addClass": function(className) {
	},
	"removeClass": function(className) {
	},
	"toggleClass": function(className) {
	},
	"hasClass": function(className) {
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
	}
},
/**
 * Element map functions listed here are mapped directly to functions in the
 * _domElementFunctions object of the same name. Each element within the
 * collection is iterated over. The returned result is the combination of all
 * elements within the collection. For example, .hasClass will return true if
 * any of the elements have the specified class name.
 */
_domElementCollectionFunctions = {
	"mappedFunctions": [
		"addClass",
		"removeClass",
		"toggleClass",
		"hasClass",
	]
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
 * Adds a callback to the load queue to be invoked on the DOM ready event.
 * If a page is given, callbacks will only be added to the queue if the current
 * window.location.href matches the given string or regular expression.
 *
 * @param {function} callback The callback to invoke on DOM ready.
 * @param {string|RegExp} [page] The match to make in order to add the callback.
 */
ready = function(callback, page) {

},

/**
 * Used to perform asynchronous HTTP requests. Automatically parses the response
 * by converting to JSON wherever possible. For connection pooling, the GT.http
 * object is instantiated with `new`.
 *
 */
http = function() {
	/**
	 * @param {string} uri The HTTP URI to connect to with or without query 
	 * string parameters. For HTTP GET and DELETE methods, the data parameter
	 * will be converted to a query string as these methods do not allow data
	 * in the body.
	 * @param {string} [method] Defaults to GET, can be either GET, POST, PUT
	 * or DELETE.
	 * @param {object|string} [data] The key-value-pair object to place in the 
	 * request body. Optionally, a querystring style string can be used. When
	 * data is passed in on GET or DELETE methods, the keys are moved to the
	 * querystring, as they do not allow data in the request body. If there is 
	 * already a querystring, an exception is thrown.
	 * @param {function} [callback] The callback to invoke when the HTTP request
	 * gets a response. The callback is passed two parameters: `responseData`,
	 * either a string containing the response or an object when JSON was
	 * returned, and `xhr`, a reference to the XMLHttpRequest object used.
	 */
	var execute = function(uri, method, data, callback) {
	};

	return {
		"execute": execute
	}
},

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
dom = function(selector, context) {
	return "SELECTED ELEMT";
},

/**
 * Obtains a cloned reference to a template element.
 */
template = function(name) {

},

tool = function() {

};

// Attach the GT object to the window, exposing the namespace as a global.
window.GT = _GT;
// Build the GT object to expose public methods.
GT.error = error;
GT.typeOf = typeOf;
GT.instanceOf = instanceOf;
GT.dom = dom;
GT.merge = merge;

// Extend GT.dom capabilities.
GT.merge(GT.dom, _domFunctions);
return;

})();