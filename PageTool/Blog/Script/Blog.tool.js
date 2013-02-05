go("Tool.Blog", function() {

dom("#editArticle").addEventListener("click", Tool.Blog.edit);

});

/////

namespace("Tool.Blog");
Tool.Blog.edit = function() {
	var tools = tool("Blog").createEditTools(),
		editText = this.textContent,
		editCallback = arguments.callee;

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
			{"action": "save", "icon": "icon-ok"}, 
			{"action": "cancel", "icon": "icon-remove"}, 
			{"spacer": true},
			{"cmd": "bold",	"icon": "icon-bold"},
			{"cmd": "italic", "icon": "icon-italic"},
			{"cmd": "underline", "icon": "icon-underline"},
			{"cmd": "strikeThrough", "icon": "icon-strikethrough"},
			{"spacer": true},
			{"cmd": "formatBlock", "value": "H1"},
			{"cmd": "formatBlock", "value": "H2"},
			{"cmd": "formatBlock", "value": "H3"},
			{"cmd": "formatBlock", "value": "P"},
			{"spacer": true},
			{"cmd": "insertUnorderedList", "icon": "icon-list-ul"},
			{"cmd": "insertOrderedList", "icon": "icon-list-ol"},
			{"cmd": "createLink", "icon": "icon-link", "prompt": "Link URL?"},
			{"cmd": "insertImage","icon":"icon-picture","prompt": "Image URL?"},
			{"spacer": true},
			{"cmd": "insertHorizontalRule", "icon":"icon-minus"},
			{"cmd": "removeFormat", "icon":"icon-remove-sign"},

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
				document.execCommand(cmd, false, arg);
			});
		})(buttons[i], btn);
		btn.appendChild(icon);
		container.appendChild(btn);
	}

	return container;
};