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

	$period     = $config['Jobs']['make-images']['period'];
	$limit      = $config['Jobs']['make-images']['limit'];
	$iterations = $config['Jobs']['make-images']['iterations'];

	$i = 0;

	while (TRUE && $i < $iterations)
	{
		$i++;

		// start time
		$start = time();
		$processed = 0;

		// fetch tweets
		$tweetsWithoutImage = Tweet::getUnprocessed($limit);

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

		Debug::logMsg('OK! ... iteartion:' . $i . '/' . $iterations .' images processed: ' . $processed);
		sleep($sleep);
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