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
	include dirname(__FILE__) . '/bootstrap.php';

	// will return nothing on first call
	$lastTweet = Collage::getLastTweet();

	// fetch results using twitter API
	$newTweets = Twitter::search($config['Twitter']['terms'], $config['Twitter']['rpp'], $lastTweet['twitterId']);

	// start adding to this page
	$pageNo   = Collage::getCurrentWorkingPageNo();
	$pageSize = Collage::getPageSize();
	// all slots
	$freeSlots = array();
	for ($i = 1; $i <= $pageSize; $i++) $freeSlots[$i] = $i;
	// remove used slots
	$result = Tweet::getByPage($pageNo, $pageSize);
	while ($row = $result->row()) unset($freeSlots[$row['position']]);

	// shuffle slots
	shuffle($freeSlots);

	// add new tweets
	foreach ($newTweets as $tweet)
	{
		$position = array_pop($freeSlots);

		$tweet['page'] = $pageNo;
		$tweet['position'] = $position;

		Collage::addTweet($tweet);

		// no positions left in this page
		if (!count($freeSlots))
		{
			// new page
			$pageNo++;
			// all slots
			$freeSlots = array();
			for ($i = 1; $i <= $pageSize; $i++) $freeSlots[] = $i;
			// shuffle slots
			shuffle($freeSlots);
		}
	}

	Dispatch::now(1, 'TWITTER SEARCH OK', $data);

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