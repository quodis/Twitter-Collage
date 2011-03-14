<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.4
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */


/**
 * escape from global scope
 */
function main()
{
	DEFINE('CLIENT', 'script');
	DEFINE('CONTEXT', __FILE__);
	DEFINE('NO_SESSION', 'TRUE');
	include dirname(__FILE__) .  '/../bootstrap.php';

	if ($config['UI']['minified']) {

		$cssPath = APP_PATH . '/public/assets/css';
		$jsPath = APP_PATH . '/public/assets/js';

		$ftp    = file_get_contents($cssPath . '/ftp.css');
		$mosaic = file_get_contents($cssPath . '/mosaic.css');
		file_put_contents('/tmp/party.css', $ftp . $mosaic);

		shell_exec('/usr/bin/java -jar ' . APP_PATH . '/src/yuicompressor-2.4.2.jar  /tmp/party.css -o ' . $cssPath . '/party-min.css');

		$global  = file_get_contents($jsPath . '/global.js');
		$general = file_get_contents($jsPath . '/general.js');
		file_put_contents('/tmp/party.js', $global . $general);

		shell_exec('/usr/bin/java -jar ' . APP_PATH . '/src/compiler.jar  --js=/tmp/party.js --compilation_level ADVANCED_OPTIMIZATIONS -js_output_file=' . $jsPath . '/party-min.js');
	}

	Dispatch::now(1, 'OK');
}

try
{
	main();
}
catch(Exception $e) {
	Debug::logError($e, 'EXCEPTION ' . $e->getMessage());
	Dispatch::now(0, 'EXCEPTION ' . $e->getMessage());
}

?>
