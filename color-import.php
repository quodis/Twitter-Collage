<?php
/**
 * @package    TwitterCollage
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
	include dirname(__FILE__) . '/bootstrap.php';

	$logo = $config['App']['path'] . '/' . $config['Collage']['logoFile'];

	Debug::logMsg('analysing file: ' . $logo);

	if (!file_exists($logo)) Dispatch::now(0, 'FAIL - invalid file:' . $logo);

	$image = new Imagick($logo);

	if ($image->getImageWidth() != $config['Collage']['cols']) Dispatch::now(0, 'FAIL - invalid width is:' . $image->getImageWidth() . ' should be:' . $config['Collage']['cols']);
	if ($image->getImageHeight() != $config['Collage']['rows']) Dispatch::now(0, 'FAIL - invalid height is:' . $image->getImageHeight() . ' should be:' . $config['Collage']['rows']);

	$iterator = $image->getPixelIterator();

	foreach($iterator as $rowIx => $rowPixels)
	{
		foreach ($rowPixels as $columnIx => $pixel)
		{
			$color = $pixel->getColor();

			if (implode($color) == '2552552551') continue;

			$data[$rowIx][$columnIx] = array($color['r'], $color['g'], $color['b']);
		}
	}

	// store the configuration
	$config = Collage::setPageGrid($data);

	// get config (meanwhile indexed)
	foreach ($config['index'] as $position => $foo)
	{
		$file = Image::makeTileOverlay($position);

		if (!$file) Dispatch::now(0, 'FAIL pos:' . $position);

		Debug::logMsg('generated: ' . $position . ' > ' . $file);
	}

	Dispatch::now(1, 'COLOR IMPORT OK');

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