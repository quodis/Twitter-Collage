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

// define the regex_pattern for langcode matching
$regex_pattern = "/([a-z]{2}-)([a-z]{2})/is";

// parse the accept language header
preg_match_all($regex_pattern, $_SERVER['HTTP_ACCEPT_LANGUAGE'], $accept_language);
// if there's no language code in the request_uri
if ($_SERVER['REQUEST_URI'] == '/')
	// redirect the user to his preferred language as defined in the accept-language request header
	header('Location: /' . $accept_language[1][0] . strtoupper($accept_language[2][0]));

// define where the translation files are stored in disk
bindtextdomain("all", dirname(__FILE__) . "/../locale");

// find the language code in the REQUEST_URI
preg_match_all($regex_pattern, $_SERVER['REQUEST_URI'], $request_uri);
// normalize request URI
// check if the second fragment is lowercase
if ($_SERVER['REQUEST_URI'] != '/' && ctype_lower($request_uri[2][0]))
	// redirect the user to /xx-XX
	header('Location: /' . $request_uri[1][0] . strtoupper($request_uri[2][0]));

// language code
$language_code = str_replace('-', '_', $request_uri[1][0]) . strtoupper($request_uri[2][0]);
// set the locale
$locale = $language_code . '.utf8';
putenv("LC_ALL=$locale");
setlocale(LC_ALL, $locale);
textdomain("all");
bind_textdomain_codeset("all", 'UTF-8');

?>