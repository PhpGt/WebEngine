/**
 * GT core JavaScript file. Contains the most essential utility functions.
 * TODO: Docs.
 * [jQuery's Dom Ready functionality]
 * [DomAssistant required to use query selectors in < IE9 properly 
 * (also adds nice DOM manipulation features)].
 */

(function() {
	/**
	 * TODO: Docs.
	 */
	var GT = function() {
		console.log(typeof arguments[0]);
		if(typeof arguments[0] === "function") {
			// Execute it on DomContentLoaded event.
			return GT.ready(arguments[0]);
		}
		if(typeof arguments[0] === "string") {
			// Return matching DomNodes from CSS selector.
			return GT.querySelector(arguments[0], arguments[1]);
		}
	};
	
	/**
	 * TODO: Docs.
	 */
	GT.ready = function(callback) {
		// Attack the event listener in real browsers.
		if(document.addEventListener) {
			document.addEventListener("DOMContentLoaded", function() {
				document.removeEventListener(
					"DOMContentLoaded",
					arguments.callee,
					false
				);
				return callback();
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
					return callback();
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

					c_callback();
				})(callback);
			}
		}
	};

	/**
	 * This function on its own is called from the GT main function as a
	 * shorthand method for the browser's querySelectorAll function - but is
	 * extended by including DomAssistant.js for advanced functionality.
	 * NOTE: DomAssistant.js must be included for querySelector functionality
	 * in Internet Explorer 8 and lower.
	 * @param selector The CSS selector to find.
	 * @return NodeList An array like object containing the matching elements.
	 */
	GT.querySelector = function(selector) {
		/*
		PSEUDO CODE:

		if(DomAssistant is loaded) {
			return DomAssistant.querySelector(selector);
		}
		else if(document.querySelectorAll) {
			return document.querySelectorAll(selector);
		}
		else {
			throw an error to the console, stop script from executing.
		}
		*/
	};

	window.GT = GT;
}());