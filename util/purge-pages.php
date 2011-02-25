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
	include dirname(__FILE__) . '/../bootstrap.php';

	$sql = 'UPDATE tweet SET imageData = NULL';

	Debug::logMsg($sql);

	Db::execute($sql);

	shell_exec('rm -R ' . $config['Store']['path'] . '/processed/*');
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