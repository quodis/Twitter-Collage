<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.4
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * NOTE purging a page delete's it from public storage
 * if it's one of the last two pages clients will bork a 404
 * the right way to do it is using page-update.php
 */

/**
 * escape from global scope
 */
function main()
{
	global $argv;

	$usage = 'USAGE: page-purge 1 OR page-purge 1-5 OR page-purge 2,3,7,8';

	DEFINE('CLIENT', 'script');
	DEFINE('CONTEXT', __FILE__);
	include dirname(__FILE__) . '/../bootstrap.php';

	// fetch/validate list of pages
	if (!isset($argv[1])) Dispatch::now(0, $usage);
	$pageList = Req::getIntegerListFromArg($argv[1]);
	if (!count($pageList)) Dispatch::now(0, $usage);

	// purge pages
	foreach ($pageList as $pageNo)
	{
		if (!Mosaic::pageExists($pageNo))
		{
			Debug::logError('page not found:' . $pageNo);
		}
		else
		{
			Debug::logMsg('purge page:' . $pageNo . ' from file:' . Mosaic::getPageDataFileName($pageNo));
			Mosaic::purgePage($pageNo);
		}
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