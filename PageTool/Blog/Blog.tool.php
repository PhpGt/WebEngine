<?php class Blog_PageTool extends PageTool {
/**
 * TODO: Docs.
 * You know ... a web log!
 */
private $_name = "Blog";
/**
 * Called to output a single blog file, according to current URL.
 * URLs have to be generated by the getUrl function - in this style:
 * /Blog/2012/Feb/20/123-Blog+title.html (where 123 is the ID).
 */
public function go($api, $dom, $template, $tool) {
	$blogId = substr(FILE, 0, strpos(FILE, "-"));
	if(!is_numeric($blogId)) {
		throw new HttpError(400);
	}

	$blog = $api["Blog"]->getById(array("Id" => $blogId));
	$blogUrl = $this->getUrl($blog);
	if($blogUrl !== $_SERVER["REQUEST_URI"]) {
		var_dump($blogUrl, $_SERVER["REQUEST_URI"]);die();
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $blogUrl");
		exit;
	}

	// Attempt to find the container for the blog.
	$container = $dom["body > section#st_article"];
	if($container->length == 0) {
		$container = $dom["body > section"];
	}
	if($container->length == 0) {
		$container = $dom["body"];
	}
	$this->output($blog, $container);
}

public function output($blog, $domEl) {
	$blogApi = $this->_api["Blog"];
	$dom = $this->_dom;
	$template = $this->_template;

	$article = null;

	if($domEl->hasClass("preview") || $domEl->hasClass("previewList")) {
		if($blog["Is_Featured"]) {
			$article = $template["ArticleFeatured"];
		}
	}
	if(is_null($article)) {
		$article = $template["Article"];
	}

	// Check to see if there is a link within the h1.
	$anchor = $article["header h1 a"];
	if($anchor->length > 0) {
		$anchor->text = $blog["Title"];
		$article["header h1 a, footer p a"]->href = 
			$this->getUrl($blog);
	}
	else {
		$article["header h1"]->text = $blog["Title"];
	}

	$article["header time"]->setAttribute("datetime", date(
		"Y-m-d", strtotime($blog["Dt_Publish"])));
	$article["header time"]->text = date(
		"jS F Y", strtotime($blog["Dt_Publish"]));
	
	$content = $article["div.preview, div.content"];
	$content->html = $blog["Content"];
	//$section->append($article); // wtfisthis?

	// Get all the tags for the article.
	$ulTags = $article["header ul.tags"];
	$ulTags->removeChildren();

	$tagList = $blogApi->getTags(["Id" => $blog["Id"]]);
	foreach ($tagList as $tag) {
		$li = $dom->create("li", ["data-tag-id" => $tag["Id"]]);
		$a = $dom->create("a", [
			"href" => $this->getTagUrl($tag)],
			$tag["Name"]);
		$li->append($a);
		$ulTags->append($li);
	}

	$domEl->append($article);

	if($article["div.preview"]->length <= 0) {
		return;
	}

	$foundPreview = false;
	$node = $article["div.preview"]->firstChild;

	while(!is_null($node)) {
		if($foundPreview) {
			$removeNode = $node;
		}
		if($node->hasClass("previewBreak")) {
			$foundPreview = true;
		}

		$node = $node->nextSibling;
		if(!empty($removeNode)) {
			$removeNode->remove();
		}
	}
}

public function setName($name) {
	$this->_name = $name;
}

public function getUrl($blogObj) {
	$dtPublish = new DateTime($blogObj["Dt_Publish"]);
	$url = "/{$this->_name}/";
	$url .= $dtPublish->format("Y/M/d");
	$url .= "/" . $blogObj["Id"] . "-";
	$url .= urlencode($blogObj["Title"]);
	// TODO: Temp. remove periods as to not break URL regex.
	$url = str_replace(".", "", $url);
	$url .= ".html";
	return $url;
}

public function getTagUrl($tagObj) {
	$url = "/{$this->_name}/Tagged/";
	$url .= urlencode($tagObj["Name"]);
	$url .= ".html";
	return $url;
}

/**
 * Simply returns the blog's preview field and ensures a word isn't
 * sliced in two.
 */
public function getPreview($blogObj) {
	$preview = strip_tags($blogObj["Content"]);

	$trimmed = (strlen($preview) > $blogObj["PreviewLength"]);

	$preview = substr($preview, 0, $blogObj["PreviewLength"]);
	$preview = substr($preview, 0, strrpos($preview, " "));

	if($trimmed) {
		$preview .= " ...";
	}
	return $preview;
}

public function outputLatest($output) {
	$blog = $this->_api["Blog"]->getLatest();

	$output["h1"]->text = $blog["Title"];
	$output["p.date"]->text = date("jS F Y",
		strtotime($blog["Dt_Publish"]));
	$output["p.blogPreview"]->text = $this->getPreview($blog);
	$output["a"]->href = $this->getUrl($blog);
}

}?>