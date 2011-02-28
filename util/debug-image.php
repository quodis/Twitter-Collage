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
	global $argv;

	DEFINE('CLIENT', 'script');
	DEFINE('CONTEXT', __FILE__);
	include dirname(__FILE__) .  '/../bootstrap.php';

	if (!isset($argv[1])) Dispatch::now(0, 'usage: debug-image 3121');

	$id = (int)$argv[1];

	$tweet = Tweet::getById($id);

	if (!$tweet) Dispatch::now(0, 'not found');

	$url = $tweet['imageUrl'];

	$pathinfo = pathinfo($url);
	// define a sufix based on the extension key from the path info
	$sufix = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
	// define the cache file filename
	$original = Image::fileName('original', md5($url), $sufix);

	$processed = Image::fileName('processed', md5($id), 'gif');

	dd('original:' . $original);

	dd('processed:' . $processed);

	Dispatch::now(1, ';-)');

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