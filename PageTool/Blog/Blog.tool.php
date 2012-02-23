<?php
/**
 * TODO: Docs.
 * You know ... a web log!
 */
class Blog_PageTool extends PageTool {
	public function go($api, $dom, $template, $tool) {
		// No automatic method...
	}

	public function getUrl($blogObj) {
		$dtPublish = new DateTime($blogObj["Dt_Publish"]);
		$url = "/Blog/";
		$url .= $dtPublish->format("Y/M/j");
		$url .= "/" . $blogObj["Id"] . "-";
		$url .= urlencode($blogObj["Title"]);
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
}
?>