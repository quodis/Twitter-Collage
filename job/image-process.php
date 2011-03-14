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

	Debug::setLogMsgFile($config['App']['pathLog'] .'/image-process.msg.log');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/image-process.error.log');
	Debug::setForceLogToFile(TRUE);

	$period   = $config['Jobs']['image-process']['period'];
	$hasCronSchedule = $config['Jobs']['twitter-search']['hasCronSchedule'];

	$dbLimit  = $config['Jobs']['image-process']['dbLimit'];
	$imgLimit = $config['Jobs']['image-process']['imgLimit'];

	$processed = 0;

	// NOTE: first loop sleep, avoids sending too many requests to twitter if loop is crashing
	// - if process crashes it is restarted within 1 sec by superivise, and it is likely to crash again, and again...
	$sleep = 3;

	while (TRUE && $processed < $imgLimit)
	{
		// NOTE: sleep at the top of the loop prevents (see above)
		if ($sleep && !$hasCronSchedule) sleep($sleep);

		// start time
		$gStart = time();

		// fetch tiles from page priority page
		$pageNo = Mosaic::getPriorityPageNo(TRUE);
		$unprocessedTiles = Tweet::getUnprocessed($pageNo, $dbLimit);

		// fetch others (oldest first)
		if (!$unprocessedTiles->count())
		{
			$unprocessedTiles = Tweet::getUnprocessed(null, $dbLimit);
		}

		while ($tweet = $unprocessedTiles->row())
		{
			$processed++;

			$start = microtime(TRUE);
			$time = array();

			// download
			if ($fileName = Image::download($tweet['imageUrl'], $tweet['id']))
			{
				$time['dwnld'] = microtime(TRUE);

				$imageUrl = $tweet['imageUrl'];

				try
				{
					// prevent downloading faulty image (response is a 404 html in twitter.com)
					if (strpos($imageUrl, 'default_profile_normal.png'))
					{
						throw new Exception('skip faulty "default_profile_normal.png"');
					}
					// make image with
					$encoded = Image::makeTile($fileName, $tweet['id'], $tweet['position']);
				}
				catch(Exception $e)
				{
					Debug::logError('Fail Image::makeTile(). Defaulting to egg. Details follow.... id:' . $tweet['id'] . ' page:' . $tweet['page'] . ' position: ' . $tweet['position'] . ' from url:' . $tweet['imageUrl'] . ' into:' . Image::fileName('processed', md5($tweet['id']), 'gif') . ' with error: ' . $e->getMessage());

					// make default
					$defaultPic = $config['App']['path'] . '/' . $config['Mosaic']['defaultPic'];
					$encoded = Image::makeTile($defaultPic, $tweet['id'], $tweet['position']);
					$imageUrl = 'http://a3.twimg.com/sticky/default_profile_images/default_profile_' . rand(0, 6) . '_normal.png';
				}

				$time['tile'] = microtime(TRUE);

				// update db with image data
				Tweet::updateImage($tweet['id'], $encoded, $imageUrl);

				$time['db'] = microtime(TRUE);

				// debug
				$log = array();
				$previous = $start;
				$value = $start;
				foreach ($time as $key => $value)
				{
					$log[] = $key . ':' . ceil(($value - $previous) * 1000) / 1000;
					$previous = $value;
				}
				$log = 'TIME:' . (ceil(($value - $start) * 1000) / 1000) . ', ' .implode(', ', $log);
				Debug::logMsg('id:' . $tweet['id'] . ' [' . $tweet['page'] . ',' . $tweet['position'] . '] [' . strlen($encoded) . ' bytes] ' . $log .' > ' .Image::fileName('processed', md5($tweet['id']), 'gif'));
			}
			else Debug::logError('fail download tweet id:' . $tweet['id'] . ' page:' . $tweet['page'] . ' position: ' . $tweet['position'] . ' from url:' . $tweet['imageUrl']);
		}

		// sleep?
		$elapsed = time() - $gStart;
		$sleep = $period - $elapsed;
		if ($sleep < 0) $sleep = 0;

		Debug::logMsg('OK! ... images processed: ' . $processed . '/' . $imgLimit . ' ... took ' . $elapsed . ' seconds');

		if ($hasCronSchedule) break;

		Debug::logMsg('... sleeping for ' . $sleep . ' seconds ...');
	}

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