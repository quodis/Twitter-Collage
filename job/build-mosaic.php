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
	include dirname(__FILE__) . '/../bootstrap.php';

	Debug::setLogMsgFile($config['App']['pathLog'] .'/build-mosaic.msg.php');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/build-mosaic.error.php');
	Debug::setForceLogToFile(TRUE);

	$period = $config['Jobs']['build-mosaic']['period'];

	while (TRUE)
	{
		// start time
		$start = time();

		// get page no
		$pageNo = Collage::getCurrentWorkingPageNo();

		// update page
		$tweetCount = Collage::updatePage($pageNo);

		// sleep?
		$elapsed = time() - $start;
		$sleep = $period - $elapsed;
		if ($sleep < 1) $sleep = 1;

		Debug::logMsg('OK! ... updated page:' . $pageNo . ' ... page has ' . $tweetCount . ' tweets ... sleeping for ' . $sleep . ' seconds ...');
		sleep($sleep);
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