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

	Debug::setLogMsgFile($config['App']['pathLog'] .'/mosaic-build.msg.log');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/mosaic-build.error.log');
	Debug::setForceLogToFile(TRUE);

	$period = $config['Jobs']['mosaic-build']['period'];

	$pageSize = Mosaic::getPageSize();

	while (TRUE)
	{
		// start time
		$start = time();

		// update page
		$pageNo = null;
		$pageNo = Mosaic::updatePage();

		Debug::logMsg('OK! ... updated page:' . $pageNo);

		// sleep?
		$elapsed = time() - $start;
		$sleep = $period - $elapsed;
		if ($sleep < 1) $sleep = 1;
		sleep($sleep);

		Debug::logMsg('OK! ... sleeping for ' . $sleep . ' seconds ...');
	}

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