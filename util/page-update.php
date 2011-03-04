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
	global $argv;

	// FIX update the most recent set of tiles only (no page)

	DEFINE('CLIENT', 'script');
	DEFINE('CONTEXT', __FILE__);
	include dirname(__FILE__) . '/../bootstrap.php';

	// fetch/validate list of pages
	if (!isset($argv[1])) Dispatch::now(0, $usage);
	$pageList = Req::getIntegerListFromArg($argv[1]);
	if (!count($pageList)) Dispatch::now(0, $usage);

	// update pages
	foreach ($pageList as $pageNo)
	{
		$count = Mosaic::updatePage($pageNo);
		if ($count)
		{
			Debug::logMsg('update page:' . $pageNo . ' to file:' . Mosaic::getPageDataFileName($pageNo));
		}
		else Debug::logMsg('skip empty page:' . $pageNo);

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