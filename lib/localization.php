<?

/**
 * Small localization lib
 *
 * @author		Luis Abreu
 * @version		0.1
 * @copyright	Quodis, 28 February, 2011
 * @package		Firefox 4 Twitter Party
 * @subpackage	server
 **/

// parse the accept language header
preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $accept_language);
// if there's no language code in the request_uri
if ($_SERVER['REQUEST_URI'] == '/')
	// redirect the user to his preferred language as define in the accept-language browser header
	header('Location: /' . $accept_language[1][0]);

// define where the translation files are stored in disk
bindtextdomain("all", dirname(__FILE__) . "/../locale");

// find the language code in the REQUEST_URI
preg_match_all("/.*?([a-z]{2}-[A-Z]{2})/is", $_SERVER['REQUEST_URI'], $request_uri);
// set the locale
$locale = str_replace('-', '_', $request_uri[1][0]) . '.utf8';
putenv("LC_ALL=$locale");
setlocale(LC_ALL, $locale);
textdomain("all");
bind_textdomain_codeset("all", 'UTF-8');

?>