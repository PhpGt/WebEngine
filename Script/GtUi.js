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

	GT(".horizontalScroll").addEvent(
		["mousewheel", "DOMMouseScroll"],
		scrollHorizontal
	);
});