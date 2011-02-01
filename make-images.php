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

	$tweetsWithoutImage = Tweet::getUnprocessed();

	while ($tweet = $tweetsWithoutImage->row())
	{
		if (Image::download($tweet['imageUrl'], $tweet['id']))
		{
			Image::makeTile($tweet['imageUrl'], $tweet['id'], Collage::getTweetIndexInPage($tweet['id']));
		}
	}

	Dispatch::now(1, 'MAKE IMAGES OK');

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