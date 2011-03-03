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

	$userId = (isset($_REQUEST['user_id'])) ? $_REQUEST['user_id'] : null;

	$result = Tweet::deleteUser($userId);

	$ok = $result->success();
	$msg = $ok ? 'OK' : 'FAIL';

	Debug::logMsg('delete user:' . $userId . ' msg:' . $msg);

	Dispatch::now($ok, $msg);

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
