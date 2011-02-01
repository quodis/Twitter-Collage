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
	global $argv;

	DEFINE('CLIENT', 'script');
	DEFINE('CONTEXT', __FILE__);
	include dirname(__FILE__) .  '/bootstrap.php';

	$count = isset($argv[1]) ? $argv[1] : 10;

	$tweets = Tweet::getByPage(0, $count);

	$i = 0;
	while ($i < $count)
	{
		$i++;
		$tweet = $tweets->row();
		$tweet = json_decode($tweet['payload'], TRUE);
		Tweet::insert($tweet);
	}

	Dispatch::now(1, 'ADD FAKES OK', $data);

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
