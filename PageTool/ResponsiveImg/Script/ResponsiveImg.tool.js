;go("Tool.ResponsiveImg", function() {

var updatePageSize = function(e) {
	var cName = "Tool.ResponsiveImg",
		refresh = false;
	if(document.cookie.indexOf(cName + "=") < 0) {
		// There isn't already a cookie set - reload the page to allow 
		// PHP.Gt to serve correct images.
		refresh = true;
	}
	document.cookie = cName + "=" + window.innerWidth + "; path=/";
	if(refresh) {
		window.location.reload();
	}

	updateImages();
},

/**
 * Triggered whenever the size of the window changes. If a low resolution image
 * had been previously served, this function will look for a higher resolution
 * image to replace it with. A smaller image will never replace a larger one
 * because the larger one will have already been downloaded.
 */
updateImages = function() {
	var imgList = dom("img.responsive[data-responsive-src]"),
		imgList_i = 0, imgList_len = imgList.length, img,
		sizes, sizesAttr = "data-responsive-sizes", size_i, size_len,
		sizeToUse = null;
	for(; imgList_i < imgList_len; imgList_i++) {
		img = imgList[imgList_i];
		if(!img.hasAttribute(sizesAttr)) {
			continue;
		}
		sizes = img.getAttribute(sizesAttr).split(",");
		size_len = sizes.length;
		for(size_i = 0; size_i < size_len; size_i++) {
			if(window.innerWidth > sizes[size_i]) {
				sizeToUse = sizes[size_i];
				if(sizes[size_i + 1]) {
					sizeToUse = sizes[size_i + 1];
				}
			}
		}

		if(sizeToUse !== null) {
			if(sizeToUse === sizes[size_len - 1]) {
				img.src = img.getAttribute("data-responsive-src");
				img.removeAttribute("data-responsive-src");
				img.removeAttribute(sizesAttr);
			}
			else {
				img.src = img.src.replace(
					/(.+\.responsive-)([0-9]+)/, 
					"$1" + sizeToUse);
			}
		}
	}
};

window.addEventListener("resize", updatePageSize);
updatePageSize();

});