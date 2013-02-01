go("Tool.Blog", function() {

console.log("Blog tool loaded!");

});

/////

namespace("Tool.Blog");
Tool.Blog.edit = function() {
	var tools = tool("Blog").createEditTools();
	document.body.appendChild(tools);
};

Tool.Blog.createEditTools = function() {
	var container = document.createElement("div");
	container.id = "tool_blog_edit";
	return container;
};