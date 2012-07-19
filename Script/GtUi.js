GT(function() {
	var scrollHorizontal = function(e) {
		this.scrollLeft -= e.wheelDelta / 2;
	};

	GT(".horizontalScroll").addEvent(
		["mousewheel", "DOMMouseScroll"],
		scrollHorizontal
	);
});