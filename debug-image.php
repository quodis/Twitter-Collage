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

	$tweet = Tweet::getById('4012');

	$url = $tweet['imageUrl'];

	$pathinfo = pathinfo($url);
	// define a sufix based on the extension key from the path info
	$sufix = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
	// define the cache file filename
	$cacheFile = Image::fileName('original', md5($url), $sufix);

	dk($cacheFile);

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