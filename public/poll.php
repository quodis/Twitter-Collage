<?php
/**
 * @pacjage    Firefox 4 Twitter Party
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

	$lastId = (isset($_REQUEST['lastId'])) ? (int)$_REQUEST['lastId'] : null;

	$result = Tweet::getSinceLastIdWithImage($lastId, $config['UI']['pollLimit']);

	// init response

	$data = array(
		'pageNo' => 0,
		'tiles' => array(),
		'lastId' => null,
		'msg' => null
	);

	$lastId = null;
	$tiles = array();
	while ($tweet = $result->row())
	{
		$tiles[] = $tweet;
		if ($tweet['id'] > $lastId) $lastId = $tweet['id'];
	}

	if (count($tiles))
	{
		$data['tiles'] = $tiles;
		$data['lastId'] = $lastId;
	}

	dd('lastId:' .  $lastId . ' count:' . count($data['tiles']) . ' lastId:' . $data['lastId']);

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