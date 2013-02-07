go("Tool.Blog", function() {

dom("#editArticle").addEventListener("click", Tool.Blog.edit);

});

/////

namespace("Tool.Blog");
Tool.Blog.edit = function() {
	var tools = tool("Blog").createEditTools(),
		editText = this.textContent,
		editCallback = arguments.callee;

	dom("article header h1 a").setAttribute("contenteditable", true);
	dom(".content").setAttribute("contenteditable", true);

	this.textContent = "Save changes";
	// Change edit button to save button.
	this.addEventListener("click", function(e) {
		e.preventDefault();
		tools.remove();
		this.textContent = editText;
		this.removeEventListener("click", arguments.callee);
		this.addEventListener("click", editCallback);
		alert("Saved!");
	});

	this.removeEventListener("click", editCallback);
	document.body.appendChild(tools);
};

Tool.Blog.createEditTools = function() {
	var container = document.createElement("div"),
		buttons = [
			{"title": "Save",
				"action": "save", "icon": "icon-ok"}, 
			{"title": "Cancel",
				"action": "cancel", "icon": "icon-remove"}, 
			{"spacer": true},
			{"title": "Bold",
				"cmd": "bold",	"icon": "icon-bold"},
			{"title": "Italic",
				"cmd": "italic", "icon": "icon-italic"},
			{"title": "Underline",
				"cmd": "underline", "icon": "icon-underline"},
			{"title": "Strikethrough",
				"cmd": "strikeThrough", "icon": "icon-strikethrough"},
			{"spacer": true},
			{"title": "Heading 1",
				"cmd": "formatBlock", "value": "H1"},
			{"title": "Heading 2",
				"cmd": "formatBlock", "value": "H2"},
			{"title": "Heading 3",
				"cmd": "formatBlock", "value": "H3"},
			{"title": "Paragraph",
				"cmd": "formatBlock", "value": "P"},
			{"spacer": true},
			{"title": "Bullet list",
				"cmd": "insertUnorderedList", "icon": "icon-list-ul"},
			{"title": "Numeric list",
				"cmd": "insertOrderedList", "icon": "icon-list-ol"},
			{"title": "Link", "prompt": "Link URL?",
				"cmd": "createLink", "icon": "icon-link"},
			{"title": "Image", "prompt": "Image URL?",
				"cmd": "insertImage","icon":"icon-picture"},
			{"spacer": true},
			{"title": "Preview break",
				"cmd": "insertHorizontalRule", "icon":"icon-minus"},
			{"title": "Remove all formatting",
				"cmd": "removeFormat", "icon":"icon-remove-sign"},

		], i, btn, icon;
	container.id = "tool_blog_edit";

	for(i = 0; i < buttons.length; i++) {
		if(buttons[i].spacer) {
			container.appendChild(document.createElement("hr"));
			continue;
		}
		btn = document.createElement("button");
		icon = document.createElement("i");
		if(buttons[i].icon) {
			icon.className = buttons[i].icon;			
		}
		else {
			icon.textContent = buttons[i].value;
		}
		(function(c_button, c_btn) {
			c_btn.addEventListener("click", function(e) {
				var cmd = c_button.cmd || null,
					arg = c_button.value || null;
				if(c_button.prompt) {
					arg = prompt(c_button.prompt);
				}
				if(cmd) {
					document.execCommand(cmd, false, arg);					
				}
				else {
					Tool.Blog.editAction[c_button.action]();
				}
			});
		})(buttons[i], btn);

		btn.setAttribute("title", buttons[i].title);
		btn.appendChild(icon);
		container.appendChild(btn);
	}

	return container;
};

Tool.Blog.editAction = {
	"save": function() {
		api("Blog", "save", {
			"title": dom("article > header h1 a").textContent,
			"content": dom("article > div.content").innerHTML,
		}, function() {
			alert("Save complete.");
		});
	},
	"cancel": function(e) {
		alert("Cancelled");
	},
};