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
	include dirname(__FILE__) . '/../bootstrap.php';

	Twitter::reset();
	Cache::delete(Mosaic::CACHE_KEY_LAST_TWEET);
	Cache::delete(Mosaic::CACHE_KEY_LAST_TWEET_WITH_IMAGE);

	shell_exec('rm -R ' . $config['Data']['path'] . '/original/*');
	shell_exec('rm -R ' . $config['Data']['path'] . '/processed/*');
	shell_exec('rm -R ' . $config['Store']['path'] . '/pages/*');

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