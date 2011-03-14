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
	DEFINE('NO_DB', TRUE);
	DEFINE('CLIENT', 'ajax');
	DEFINE('CONTEXT', __FILE__);
	include '../bootstrap.php';

	Debug::setLogMsgFile($config['App']['pathLog'] .'/www.msg.log');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/www.error.log');

	$lastId = (isset($_REQUEST['last_id'])) ? (int)$_REQUEST['last_id'] : null;

	$cacheKey = 'TWITTER-PARTY::index::poll=' . $lastId;
	$cacheTTL = $config['Cache']['TTL']['poll'];

	header("Expires: " . gmdate("D, d M Y H:i:s", time() + $cacheTTL) . " GMT");
	header("Cache-Control: max-age=$cacheTTL, s-maxage=$cacheTTL, public, must-revalidate");
	ini_set('zlib.output_compression', 1);

	// check cache
	if ($output = Cache::get($cacheKey))
	{
		echo $output;
		exit();
	}

	initDb($config);

	$limit = $config['UI']['pollLimit'] - 5 + rand(0, 10);

	$result = Tweet::getSinceLastId($lastId, $limit, TRUE);

	// init response

	$data = array(
		'tiles' => array(),
		'last_id' => null,
		'total_tiles' => Tweet::getCount(TRUE),
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

	Debug::logMsg('requested lastId:' .  rawurlencode($lastId) . ' count:' . count($data['tiles']) . ' lastId:' . $data['last_id']);

	header('Content-type: application/text-json');

	$var = array(
		'code' => 1,
		'msg' => 'POLL_OK',
		'payload' => $data
	);

	$output = json_encode($var);
	Cache::set($cacheKey, $output, $cacheTTL);
	echo $output;
	exit();

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
