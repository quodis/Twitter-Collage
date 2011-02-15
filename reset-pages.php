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
	include dirname(__FILE__) . '/bootstrap.php';

	$sql = 'UPDATE tweet SET imageUrl = NULL, imageData = NULL';
	dd($sql);

	Db::execute($sql);

	shell_exec('rm -R /servers/cache/twitter-collage/processed/*');
	shell_exec('rm -R /servers/cache/twitter-collage/pages/*');

	Dispatch::now(1, 'RESET ALL OK', $data);

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