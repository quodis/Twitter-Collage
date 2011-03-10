<?

/**
 * Small localization lib
 *
 * @author		Luis Abreu
 * @version		0.2
 * @copyright	Quodis, 28 February, 2011
 * @package		Firefox 4 Twitter Party
 * @subpackage	server
 **/

// parse the accept language header
preg_match_all("/([a-z]{2})(-[a-z]{2})/is", $_SERVER['HTTP_ACCEPT_LANGUAGE'], $accept_language);

// if there's no language code in the request_uri
if (empty($_GET['locale']))
	// redirect the user to his preferred language as defined in the accept-language request header
	header('Location: ' . $accept_language[1][0] . strtoupper($accept_language[2][0]));

// load and instantiate localeDetails class
require_once(dirname(__FILE__) . '/localeDetails.class.php');
$locale_details = new localeDetails;

/**
 * change array key to match value
 *
 * @param array $array 
 * @return array
 * @author Luis Abreu
 */
function array_change_key($array) {
	foreach ($array as $item) {
		$new_array[str_ireplace('_', '-', $item)] = $item;
	}
	return $new_array;
}
/**
 * build the html <option> to populate the languages <select>
 *
 * @param array $available_locales 
 * @return string
 * @author Luis Abreu
 */
function populate_language_select($available_locales = array()) {
	$output = '';
	foreach ($available_locales as $key => $value) {
		// check for active option
		$active_option = ($key == $_GET['locale']) ? ' selected' : '';
		// append
		$output .= '<option' . $active_option . ' value="' . $key . '">' . $value['native'] . '</option>' . "\n";
	}
	return $output;
}

// define the directory
$locale_dir = dirname(__FILE__) . '/../locale';
// read directory contents
$dir_contents = scandir($locale_dir);
// remove the current and previous dir items from the array
$dir_contents = array_splice($dir_contents, 2, count($dir_contents));
// change the dir_contents array keys to match their values and allow direct comparison with $languages array;
$dir_contents = array_change_key($dir_contents);
// read total list of languages
$languages = $locale_details->languages;
// 
if (!is_array($dir_contents)) $dir_contents = array();
// intersect total languages and available languages
$available_locales = array_intersect_key($languages, $dir_contents);

if (!$available_locales) $available_locales = array();

// set the locale
$locale = $_GET['locale'] . '.utf8';
putenv("LC_ALL=$locale");
setlocale(LC_ALL, $locale);
textdomain("messages");
bindtextdomain("messages", $locale_dir);
bind_textdomain_codeset("messages", 'UTF-8');
?>
