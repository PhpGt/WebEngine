(function() {
	GT.ui = function() {
	};
	/**
	 * Provides linear interpolation between two points with optional smoothing.
	 */
	GT.ui.lerp = function(start, end, scalar, smoothing) {
		var interpolant;
		if(smoothing === true) {
			scalar = GT.ui.smooth(scalar);
		}
		else if(typeof smoothing === "function") {
			scalar = smoothing(scalar);
		}
		interpolant = (end - start) * scalar;

		return interpolant;
	};

	/**
	 * Converts a scalar into a smoothed scalar.
	 */
	GT.ui.smooth = function(scalar) {
		return (-Math.cos(Math.PI * scalar) + 1) / 2;
	};
})();

GT(function() {
	var scrollHorizontal = function(e) {
		this.scrollLeft -= e.wheelDelta / 2;
	};

	var treeClick = function(e) {
		e.preventDefault();
		var parentNav, parentLi, el = this;
		while(el = el.parentNode) {
			if(/li/i.test(el.tagName)) {
				parentLi = el;
			}
			if(/nav/i.test(el.tagName)) {
				parentNav = el;
				break;
			}
		}
		GT("li", parentNav).removeClass("selected");
		parentLi.addClass("selected");
		GT("li", parentNav).removeClass("open");
		if(parentLi.hasClass("tree")) {
			parentLi.addClass("open");
		}
		GT.event("navchange", {
			"callback": parentNav.getAttribute("data-callback"),
			"href": this.href,
			"originalEvent": e,
			"originalThis": this
		});
	};

	GT(".horizontalScroll").addEventListener(
		["mousewheel", "DOMMouseScroll"],
		scrollHorizontal
	);

	// Tree Navigation
	GT("nav.gt-tree a").addEventListener("click", treeClick);
});