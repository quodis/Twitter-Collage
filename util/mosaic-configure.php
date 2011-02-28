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
	include dirname(__FILE__) . '/../bootstrap.php';

	$originalFile = $config['App']['path'] . '/' . $config['Mosaic']['logoFile'];
	$reducedFile  = $config['App']['path'] . '/' . $config['Mosaic']['reducedFile'];

	Debug::logMsg('analysing original file:' . $originalFile);
	Debug::logMsg('and reduced color file:' . $reducedFile);

	if (!file_exists($originalFile)) Dispatch::now(0, 'FAIL - invalid original file:' . $originalFile);
	if (!file_exists($reducedFile)) Dispatch::now(0, 'FAIL - invalid reduced colors file:' . $reducedFile);

	$imageOriginal = new Imagick($originalFile);
	$imageReduced = new Imagick($reducedFile);

	if ($imageOriginal->getImageWidth() != $config['Mosaic']['cols']) Dispatch::now(0, 'FAIL - invalid width is:' . $image->getImageWidth() . ' should be:' . $config['Mosaic']['cols']);
	if ($imageOriginal->getImageHeight() != $config['Mosaic']['rows']) Dispatch::now(0, 'FAIL - invalid height is:' . $image->getImageHeight() . ' should be:' . $config['Mosaic']['rows']);

	// set configuration
	Mosaic::setConfigFromImages($imageOriginal, $imageReduced);

	// store php config file
	$configFileName = Mosaic::saveConfig();

	Debug::logMsg('PHP config stored: ' . $configFileName);

	$jsFileName = Mosaic::saveJsConfig();

	Debug::logMsg('JS config stored: ' . $jsFileName);

	Dispatch::now(1, 'OK');

} // main()

try
{
	main();
}
catch(Exception $e) {
	Debug::logError($e, 'EXCEPTION ' . $e->getMessage());
	Dispatch::now(0, 'EXCEPTION ' . $e->getMessage());
}

?>