<?php final class ClientSideCompiler {
/**
 * The ClientSideCompiler pre-processes necessary files, converting them into
 * their processed equivalents, removing the originals. If App_Config has the
 * client-side compilation enabled, all resources will be minified and compiled
 * into a single resource.
 *
 * The DOM is updated to only include the necessary files, so the HTML should
 * reference all JS and CSS/SCSS files without the worry of multiple requests
 * or unminified scripts being exposed publicly.
 */
public function __construct() {}

}#