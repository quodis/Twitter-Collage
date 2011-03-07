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
	include '../bootstrap.php';

	Debug::setLogMsgFile($config['App']['pathLog'] .'/www.msg.log');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/www.error.log');

	$lastId = (isset($_REQUEST['last_id'])) ? (int)$_REQUEST['last_id'] : null;

	$limit = $config['UI']['pollLimit'] - 5 + rand(0, 10);

	$result = Tweet::getSinceLastId($lastId, $limit, TRUE);

	// init response

	$data = array(
		'tiles' => array(),
		'last_id' => null,
		'msg' => null
	);

	$lastId = null;
	$tiles = array();
	while ($tweet = $result->row())
	{
		$tiles[] = $tweet;
		if ($tweet['i'] > $lastId) $lastId = $tweet['i'];
	}

	if (count($tiles))
	{
		$data['tiles'] = $tiles;
		$data['last_id'] = $lastId;
	}

	Debug::logMsg('lastId:' .  $lastId . ' count:' . count($data['tiles']) . ' lastId:' . $data['last_id']);

	Dispatch::now(1, 'POLL OK', $data);

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
