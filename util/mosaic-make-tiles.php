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

	/**
	 * TODO generate config.js
	 * 'party.grid = <?=json_encode(Mosaic::getPageConfig())?>';
	 **/

	$config = Mosaic::getPageConfig();

	// -- MAKE TILE OVERLAYS

	// get config (meanwhile indexed)
	foreach ($config['index'] as $position => $foo)
	{
		$file = Image::makeTileOverlay($position);

		if (!$file) Dispatch::now(0, 'FAIL pos:' . $position);

		Debug::logMsg('generated: ' . $position . ' > ' . $file);
	}

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