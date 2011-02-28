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

	$usage = 'USAGE: page-update 1 OR page-update 1-5 OR page-update 2,3,7,8';

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
		Debug::logMsg('update page:' . $pageNo . ' to file:' . Mosaic::getPageDataFileName($pageNo));
		Mosaic::updatePage($pageNo);
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