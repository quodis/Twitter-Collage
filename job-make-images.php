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
	include dirname(__FILE__) .  '/bootstrap.php';

	Debug::setLogMsgFile($config['App']['pathLog'] .'/make-images.msg.php');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/make-images.error.php');
	Debug::setForceLogToFile(TRUE);

	$period   = $config['Jobs']['make-images']['period'];
	$dbLimit  = $config['Jobs']['make-images']['dbLimit'];
	$imgLimit = $config['Jobs']['make-images']['imgLimit'];

	$processed = 0;

	while (TRUE && $processed < $imgLimit)
	{
		// start time
		$start = time();

		// fetch tweets
		$tweetsWithoutImage = Tweet::getUnprocessed($dbLimit);

		while ($tweet = $tweetsWithoutImage->row())
		{
			$processed++;

			// download
			if (Image::download($tweet['imageUrl'], $tweet['id']))
			{
				Debug::logMsg('updated tweet id: ' . $tweet['id'] . ' page:' . $tweet['page'] . ' position:' . $tweet['position']);

				// process tile (stores on disk and returns image raw data)
				$encoded = Image::makeTile($tweet['imageUrl'], $tweet['id'], $tweet['position']);

				// update db with image data
				Tweet::updateImage($tweet['id'], $encoded);
			}
			else Debug::logError('fail download tweet id:' . $tweet['id'] . ' page:' . $tweet['page'] . ' position:' . $tweet['position'] . ' from url:' . $tweet['imageUrl']);
		}

		// sleep?
		$elapsed = time() - $start;
		$sleep = $period - $elapsed;
		if ($sleep < 1) $sleep = 1;

		Debug::logMsg('OK! ... images processed: ' . $processed . '/' . $imgLimit);
		sleep($sleep);
	}

	Debug::logMsg('...this honoured is now going to hara-kiri...');

	exit();

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