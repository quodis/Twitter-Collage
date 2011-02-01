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
	$lastTweet = Twitter::getLastTweet();

	// fetch results using twitter API
	$results = Twitter::search($config['Collage']['terms'], $lastTweet['id']);

	// add results (will clear some cache values)
	foreach ($results as $row) Twitter::addTweet($row);

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