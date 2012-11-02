(function() {
GT.ui = function() {
};

GT.ui.dropdownMenu = function(button, name, contents, e) {
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
		if(button.children[i].hasClass("menu")) {
			menu = button.children[i];
		}
	}

	if(!menu) {
		menu = document.createElement("div");
		menu.className = "menu " + name;
		if(contents) {
			menu.appendChild(contents);
		}
		button.appendChild(menu);
		button.addClass("active");

		// Ensure the psedo-element gets the correct width of the clicked button
		if(button.className.indexOf(helperClass) < 0) {
			helperRand = ("-" + (Math.random() * 1000)).replace(".", "_");
			button.addClass(helperClass + helperRand);
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
			button.removeClass("active");
			window.removeEventListener("mousedown", arguments.callee);
		};

		// Add listener to click off the dropdown menu.
		window.addEventListener("mousedown", cancelClick);
	}
	else {
		menu.remove();
		button.removeClass("active");
	}
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