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

	Debug::setLogMsgFile($config['App']['pathLog'] .'/twitter-search.msg.log');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/twitter-search.error.log');
	Debug::setForceLogToFile(TRUE);

	$period = $config['Jobs']['twitter-search']['period'];

	while (TRUE)
	{
		// start time
		$start = time();
		$tweets = 0;

		// will return nothing on first call
		$lastTweet = Mosaic::getLastTweet();

		// fetch results using twitter API
		$newTweets = Twitter::search($config['Twitter']['terms'], $config['Twitter']['rpp'], $lastTweet['twitterId']);

		// start adding to this page
		$pageNo   = Mosaic::getCurrentInsertingPageNo();

		// all slots
		$pageSize = Mosaic::getPageSize();
		$freeSlots = array();
		for ($i = 0; $i < $pageSize; $i++) $freeSlots[$i] = $i;
		// remove used slots
		$result = Tweet::getByPage($pageNo);
		while ($row = $result->row())
		{
			// should not happen, but is sane
			if (!isset($freeSlots[$row['position']])) continue;
			// remove slot
			unset($freeSlots[$row['position']]);
		}

		// shuffle slots
		shuffle($freeSlots);

		// add new tweets
		foreach ($newTweets as $tweet)
		{
			$tweets++;

			// no positions left in this page
			if (!count($freeSlots))
			{
				// advance to next page
				$pageNo++;
				// all slots
				$freeSlots = array();
				for ($i = 0; $i < $pageSize; $i++) $freeSlots[$i] = $i;
				// shuffle slots
				shuffle($freeSlots);
			}

			// pop one
			$position = array_pop($freeSlots);

			// insert tweet
			$tweet['page'] = $pageNo;
			$tweet['position'] = $position;
			Mosaic::addTweet($tweet);
		}

		// sleep?
		$elapsed = time() - $start;
		$sleep = $period - $elapsed;
		if ($sleep < 1) $sleep = 1;

		Debug::logMsg('OK! ... new tweets:' . $tweets . ' ... sleeping for ' . $sleep . ' seconds ...');
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