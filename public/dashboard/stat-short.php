<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * escape from global scope
 */
function main()
{
	DEFINE('CLIENT', 'ajax');
	DEFINE('CONTEXT', __FILE__);
	include '../../bootstrap.php';

	Debug::setLogMsgFile($config['App']['pathLog'] .'/dashboard.msg.log');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/dashboard.error.log');

	$lastTweet = Mosaic::getLastTweet();
	$lastProcessedTweet = Mosaic::getLastTweetWithImage();
	$elapsed = Tweet::getAverageDelay(100);
	$tweets = ($lastTweet['id'] - $lastProcessedTweet['id']);

	// dashboard state
	$data = array(
		'last_page' => Mosaic::getLastCompletePage(),
		'last_id' => $lastProcessedTweet['id'],
		'tweet_count' => Tweet::getCount(),
		'delay' => array(
			'tweets' => $tweets,
			'seconds' => $elapsed
		)
	);

	Debug::logMsg('stat-short, last_page:' . $data['last_page'] . ' tweet_count:' . $data['tweet_count']);

	Dispatch::now(1, 'OK', $data);

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
