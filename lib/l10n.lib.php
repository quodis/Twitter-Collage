<?php
/**
 * Localization classes (mozilla) and funcs
 *
 * @subpackage front-end
 * @version    v.0.1
 * @author     Luis Abreu, André Torgal, Pascal Chevrel, Wil Clouser, Mozilla
 **/


/* ChooseLocale
 *
 * Licence: MPL 2/GPL 2.0/LGPL 2.1
 * Author: Pascal Chevrel, Mozilla
 * Date : 2010-07-17
 *
 * Description:
 * Class to choose the locale which locale we will show to the visitor
 * based on http acceptlang headers and our list of supported locales.
*/
class ChooseLocale
{
	public    $HTTPAcceptLang;
	public    $supportedLocales;
	protected $detectedLocale;
	protected $defaultLocale;
	public    $mapLonglocales;

	public function __construct($list=array('en-US'))
	{
		$this->HTTPAcceptLang   = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		$this->supportedLocales = array_unique($list);
		$this->setDefaultLocale('en-US');
		$this->setCompatibleLocale();
		$this->mapLonglocales = true;
	}

	public function getAcceptLangArray()
	{
		if (empty($this->HTTPAcceptLang)) return null;

		return explode(',', $this->HTTPAcceptLang);
	}

	public function getCompatibleLocale()
	{
		$l       = $this->defaultLocale;
		$acclang = $this->getAcceptLangArray();

		if(!is_array($acclang)) {
			return $this->defaultLocale;
		}

		foreach ($acclang as $var) {
			$locale	  = $this->_cleanHTTPlocaleCode($var);
			$shortLocale = array_shift((explode('-', $locale)));

			// CHANGED ORDER (quodis) because Mozilla wants to give proirity to 'pt' over 'pt-PT'
			if (in_array($shortLocale, $this->supportedLocales)) {
				$l = $shortLocale;
				break;
			}

			if (in_array($locale, $this->supportedLocales)) {
				$l = $locale;
				break;
			}

			// check if we map visitors short locales to site long locales
			// like en->en-GB
			if ($this->mapLonglocales == true) {
				foreach ($this->supportedLocales as $var) {
					$shortSupportedLocale = array_shift((explode('-', $var)));
					if ($shortLocale == $shortSupportedLocale) {
						$l = $var;
						break;
					}
				}
			}

		}

		return $l;
	}

	public function getDefaultLocale() {
		return $this->defaultLocale;
	}

	public function setCompatibleLocale() {
		$this->detectedLocale  = $this->getCompatibleLocale();
	}

	public function setDefaultLocale($locale) {

		// the default locale should always be among the site locales
		// if not, the first locale in the supportedLocales array is default
		if (!in_array($locale, $this->supportedLocales)) {
			$this->defaultLocale = $this->supportedLocales[0];

		} else {
			$this->defaultLocale = $locale;
		}
		return;
	}

	private function _cleanHTTPlocaleCode($str)
	{
		$locale = explode(';', $str);
		$locale = trim($locale[0]);

		return $locale;
	}

}


/**
 * Holds locale data for all the locales we currently support
 * If we switch to php5, all of these functions can become static with some tweaking.
 *
 * @author Wil Clouser <clouserw@mozilla.com>
 */
class localeDetails {
	 /**
	 * An array of English and native names of all the locales we support.
	 *
	 * @var array
	 */
	public static $_languages = array(
		'af'			=> array( 'English' => 'Afrikaans',				'native' => 'Afrikaans'),
		'ak'			=> array( 'English' => 'Akan',					'native' => 'Akan'), // unverified native name
		'ast'			=> array( 'English' => 'Asturian',				'native' => 'Asturianu'),
		'ar'			=> array( 'English' => 'Arabic',				'native' => 'عربي'),
		'as'			=> array( 'English' => 'Assamese',				'native' => 'অসমীয়া'),
		'be'			=> array( 'English' => 'Belarusian',			'native' => 'Беларуская'),
		'bg'			=> array( 'English' => 'Bulgarian',				'native' => 'Български'),
		'bn-BD'			=> array( 'English' => 'Bengali (Bangladesh)',	'native' => 'বাংলা (বাংলাদেশ)'),
		'bn-IN'			=> array( 'English' => 'Bengali (India)',		'native' => 'বাংলা (ভারত)'),
		'br'			=> array( 'English' => 'Breton',				'native' => 'Brezhoneg'),
		'bs'			=> array( 'English' => 'Bosnian',				'native' => 'Bosanski'),
		'ca'			=> array( 'English' => 'Catalan',				'native' => 'català'),
		'ca-valencia'	=> array( 'English' => 'Catalan (Valencian)',	'native' => 'català (valencià)'), // not iso-639-1. a=l10n-drivers
		'cs'			=> array( 'English' => 'Czech',					'native' => 'Čeština'),
		'cy'			=> array( 'English' => 'Welsh',					'native' => 'Cymraeg'),
		'da'			=> array( 'English' => 'Danish',				'native' => 'Dansk'),
		'de'			=> array( 'English' => 'German',				'native' => 'Deutsch'),
		'de-AT'			=> array( 'English' => 'German (Austria)',		'native' => 'Deutsch (Österreich)'),
		'de-CH'			=> array( 'English' => 'German (Switzerland)',	'native' => 'Deutsch (Schweiz)'),
		'de-DE'			=> array( 'English' => 'German (Germany)',		'native' => 'Deutsch (Deutschland)'),
		'dsb'			=> array( 'English' => 'Lower Sorbian',			'native' => 'Dolnoserbšćina'), // iso-639-2
		'el'			=> array( 'English' => 'Greek',					'native' => 'Ελληνικά'),
		'en-AU'			=> array( 'English' => 'English (Australian)',	'native' => 'English (Australian)'),
		'en-CA'			=> array( 'English' => 'English (Canadian)',	'native' => 'English (Canadian)'),
		'en-GB'			=> array( 'English' => 'English (British)',		'native' => 'English (British)'),
		'en-NZ'			=> array( 'English' => 'English (New Zealand)',	'native' => 'English (New Zealand)'),
		'en-US'			=> array( 'English' => 'English (US)',			'native' => 'English (US)'),
		'en-ZA'			=> array( 'English' => 'English (South African)','native' => 'English (South African)'),
		'eo'			=> array( 'English' => 'Esperanto',				'native' => 'Esperanto'),
		'es'			=> array( 'English' => 'Spanish',				'native' => 'Español'),
		'es-AR'			=> array( 'English' => 'Spanish (Argentina)',	'native' => 'Español (de Argentina)'),
		'es-CL'			=> array( 'English' => 'Spanish (Chile)',		'native' => 'Español (de Chile)'),
		'es-ES'			=> array( 'English' => 'Spanish (Spain)',		'native' => 'Español (de España)'),
		'es-MX'			=> array( 'English' => 'Spanish (Mexico)',		'native' => 'Español (de México)'),
		'et'			=> array( 'English' => 'Estonian',				'native' => 'Eesti keel'),
		'eu'			=> array( 'English' => 'Basque',				'native' => 'Euskara'),
		'fa'			=> array( 'English' => 'Persian',				'native' => 'فارسی'),
		'fi'			=> array( 'English' => 'Finnish',				'native' => 'suomi'),
		'fj-FJ'			=> array( 'English' => 'Fijian',				'native' => 'Vosa vaka-Viti'),
		'fr'			=> array( 'English' => 'French',				'native' => 'Français'),
		'fur-IT'		=> array( 'English' => 'Friulian',				'native' => 'Furlan'),
		'fy-NL'	 		=> array( 'English' => 'Frisian',				'native' => 'Frysk'),
		'ga'			=> array( 'English' => 'Irish',					'native' => 'Gaeilge'),
		'ga-IE'			=> array( 'English' => 'Irish (Ireland)',		'native' => 'Gaeilge (Éire)'),
		'gd'			=> array( 'English' => 'Gaelic (Scotland)',		'native' => 'Gàidhlig'),
		'gl'			=> array( 'English' => 'Galician',				'native' => 'Galego'),
		'gu-IN'			=> array( 'English' => 'Gujarati',				'native' => 'ગુજરાતી'),
		'he'			=> array( 'English' => 'Hebrew',				'native' => 'עברית'),
		'hi'			=> array( 'English' => 'Hindi',					'native' => 'हिन्दी'),
		'hi-IN'			=> array( 'English' => 'Hindi (India)',			'native' => 'हिन्दी (भारत)'),
		'hr'			=> array( 'English' => 'Croatian',				'native' => 'Hrvatski'),
		'hsb'			=> array( 'English' => 'Upper Sorbian',			'native' => 'Hornjoserbsce'),
		'hu'			=> array( 'English' => 'Hungarian',				'native' => 'Magyar'),
		'hy-AM'			=> array( 'English' => 'Armenian',				'native' => 'Հայերեն'),
		'id'			=> array( 'English' => 'Indonesian',			'native' => 'Bahasa Indonesia'),
		'is'			=> array( 'English' => 'Icelandic',				'native' => 'íslenska'),
		'it'			=> array( 'English' => 'Italian',				'native' => 'Italiano'),
		'ja'			=> array( 'English' => 'Japanese',				'native' => '日本語'),
		'ja-JP-mac'		=> array( 'English' => 'Japanese',				'native' => '日本語'), // not iso-639-1
		'ka'			=> array( 'English' => 'Georgian',				'native' => 'ქართული'),
		'kk'			=> array( 'English' => 'Kazakh',				'native' => 'Қазақ'),
		'kn'			=> array( 'English' => 'Kannada',				'native' => 'ಕನ್ನಡ'),
		'ko'			=> array( 'English' => 'Korean',				'native' => '한국어'),
		'ku'			=> array( 'English' => 'Kurdish',				'native' => 'Kurdî'),
		'la'			=> array( 'English' => 'Latin',					'native' => 'Latina'),
		'lg'			=> array( 'English' => 'Luganda',				'native' => 'Luganda'),
		'lt'			=> array( 'English' => 'Lithuanian',			'native' => 'lietuvių kalba'),
		'lv'			=> array( 'English' => 'Latvian',				'native' => 'Latviešu'),
		'mai'			=> array( 'English' => 'Maithili',				'native' => 'मैथिली মৈথিলী'),
		'mg'			=> array( 'English' => 'Malagasy',				'native' => 'Malagasy'),
		'mi'			=> array( 'English' => 'Maori (Aotearoa)',		'native' => 'Māori (Aotearoa)'),
		'mk'			=> array( 'English' => 'Macedonian',			'native' => 'Македонски'),
		'ml'			=> array( 'English' => 'Malayalam',				'native' => 'മലയാളം'),
		'mn'			=> array( 'English' => 'Mongolian',				'native' => 'Монгол'),
		'mr'			=> array( 'English' => 'Marathi',				'native' => 'मराठी'),
		'nb-NO'	 		=> array( 'English' => 'Norwegian (Bokmål)',	'native' => 'Norsk bokmål'),
		'ne-NP'			=> array( 'English' => 'Nepali',				'native' => 'नेपाली'),
		'nn-NO'			=> array( 'English' => 'Norwegian (Nynorsk)',	'native' => 'Norsk nynorsk'),
		'nl'			=> array( 'English' => 'Dutch',					'native' => 'Nederlands'),
		'nr'			=> array( 'English' => 'Ndebele, South',		'native' => 'isiNdebele'),
		'nso'			=> array( 'English' => 'Northern Sotho',		'native' => 'Sepedi'),
		'oc'			=> array( 'English' => 'Occitan (Lengadocian)',	'native' => 'occitan (lengadocian)'),
		'or'			=> array( 'English' => 'Oriya',					'native' => 'ଓଡ଼ିଆ'),
		'pa-IN'			=> array( 'English' => 'Punjabi',				'native' => 'ਪੰਜਾਬੀ'),
		'pl'			=> array( 'English' => 'Polish',				'native' => 'Polski'),
		'pt-BR'			=> array( 'English' => 'Portuguese (Brazilian)','native' => 'Português (do Brasil)'),
		'pt-PT'			=> array( 'English' => 'Portuguese (Portugal)',	'native' => 'Português (Europeu)'),
		'ro'			=> array( 'English' => 'Romanian',				'native' => 'română'),
		'rm'			=> array( 'English' => 'Romansh',				'native' => 'rumantsch'),
		'ru'			=> array( 'English' => 'Russian',				'native' => 'Русский'),
		'rw'			=> array( 'English' => 'Kinyarwanda',			'native' => 'Ikinyarwanda'),
		'si'			=> array( 'English' => 'Sinhala',				'native' => 'සිංහල'),
		'sk'			=> array( 'English' => 'Slovak',				'native' => 'slovenčina'),
		'sl'			=> array( 'English' => 'Slovenian',				'native' => 'slovensko'),
		'son'			=> array( 'English' => 'Songhai',				'native' => 'Soŋay'),
		'sq'			=> array( 'English' => 'Albanian',				'native' => 'Shqip'),
		'sr'			=> array( 'English' => 'Serbian',				'native' => 'Српски'),
		'sr-Latn'		=> array( 'English' => 'Serbian',				'native' => 'Srpski'), // follows RFC 4646
		'ss'			=> array( 'English' => 'Siswati',				'native' => 'siSwati'),
		'st'			=> array( 'English' => 'Southern Sotho',		'native' => 'Sesotho'),
		'sv-SE'			=> array( 'English' => 'Swedish',				'native' => 'Svenska'),
		'ta'			=> array( 'English' => 'Tamil',					'native' => 'தமிழ்'),
		'ta-IN'			=> array( 'English' => 'Tamil (India)',			'native' => 'தமிழ் (இந்தியா)'),
		'ta-LK'			=> array( 'English' => 'Tamil (Sri Lanka)',		'native' => 'தமிழ் (இலங்கை)'),
		'te'			=> array( 'English' => 'Telugu',				'native' => 'తెలుగు'),
		'th'			=> array( 'English' => 'Thai',					'native' => 'ไทย'),
		'tn'			=> array( 'English' => 'Tswana',				'native' => 'Setswana'),
		'tr'			=> array( 'English' => 'Turkish',				'native' => 'Türkçe'),
		'ts'			=> array( 'English' => 'Tsonga',				'native' => 'Xitsonga'),
		'tt-RU'			=> array( 'English' => 'Tatar',					'native' => 'Tatarça'),
		'uk'			=> array( 'English' => 'Ukrainian',				'native' => 'Українська'),
		'ur'			=> array( 'English' => 'Urdu',					'native' => 'اُردو'),
		've'			=> array( 'English' => 'Venda',					'native' => 'Tshivenḓa'),
		'vi'			=> array( 'English' => 'Vietnamese',			'native' => 'Tiếng Việt'),
		'wo'			=> array( 'English' => 'Wolof',					'native' => 'Wolof'),
		'xh'			=> array( 'English' => 'Xhosa',					'native' => 'isiXhosa'),
		'zh-CN'			=> array( 'English' => 'Chinese (Simplified)',	'native' => '中文 (简体)'),
		'zh-TW'			=> array( 'English' => 'Chinese (Traditional)',	'native' => '正體中文 (繁體)'),
		'zu'			=> array( 'English' => 'Zulu',					'native' => 'isiZulu')
	);

	 /**
	 * This is a function for getting a language's native name from a
	 * locale.  If the name is not available, a blank string is returned.
	 *
	 * @param string locale to lookup
	 * @return string native name for locale
	 */
	public static function getNativeNameForLocale($locale)
	{
		if (array_key_exists($locale, self::$_languages)) {
			return self::$_languages[$locale]['native'];
		}
	}
}

class Locale
{
	public static $_languageMap = null;


	private static function _loadlanguageMap()
	{
		global $config;

		if (!isset(self::$_languageMap))
		{
			require_once LIB_PATH . '/spyc-0.4.5/spyc.php';
			$configFile = $config['App']['path'] . '/config/locale.yaml';
			self::$_languageMap = Spyc::YAMLLoad($configFile);
		}
	}


	/**
	 * @param string $input (optional)
	 */
	public static function validateOrRedirect($input = null)
	{
		// get the admissible locales
		self::_loadlanguageMap();

		// validate if input is given
		if ($input && array_key_exists($input, self::$_languageMap))
		{
			return $input;
		}
		else
		{
			// auto-detect
			$availableLocales = array_keys(self::$_languageMap);
			$chooser = new ChooseLocale($availableLocales);
			$locale = $chooser->getCompatibleLocale();
			// and redirect
			self::redirect($locale);
		}
	}

	public static function redirect($locale)
	{
		global $config;
		$url = $config['App']['url'] . '/' . $locale;
		// redirect the user to his preferred language as defined in the accept-language request header
		header('Date: '.gmdate('D, d M Y H:i:s \G\M\T', time()));
		header('Expires: Fri, 01 Jan 1990 00:00:00 GMT');
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, private');
		header('Vary: *');
		header('Location: '. $url);
		exit();
	}

	public static function setUp($locale)
	{
		self::_loadlanguageMap();

		$default = reset(self::$_languageMap);

		if (isset(self::$_languageMap[$locale]))
		{
			$l = self::$_languageMap[$locale];
		}
		else list($locale, $l) = each(self::$_languageMap);

		// set the locale
		$locale_dir = dirname(__FILE__) . '/../locale';
		putenv("LC_ALL=" . $l['locale']);
		setlocale(LC_ALL, $l['locale']);
		textdomain("messages");
		bindtextdomain("messages", $locale_dir);
		bind_textdomain_codeset("messages", 'UTF-8');

		return $locale;
	}

	/**
	 * build the html <option> to populate the languages <select>
	 *
	 * @return string
	 */
	public static function langOptions($current_locale)
	{
		self::_loadlanguageMap();

		$output = '';

		foreach (self::$_languageMap as $key => $value) {
			// check for active option
			$active_option = ($key == $current_locale) ? ' selected="selected"' : '';
			// append
			$output .= '<option' . $active_option . ' value="' . $key . '">' . localeDetails::getNativeNameForLocale($value['language']) . '</option>';
		}
		return $output;
	}
}

?>
