<?php
/**
 * @pacjage    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * return a complete page either counting Z from most recent (default behaviour)
 * or by specifying a page index (note first page is index #1)
 *
 * @param integer z (optional)
 * @param integer page (optional) request a specific page (NOTE: not zero based, first page is #1)
 */

/**
 * escape from global scope
 */
function main()
{
	DEFINE('CLIENT', 'ajax');
	DEFINE('CONTEXT', __FILE__);
	include '../bootstrap.php';

	// specific page
	$pageNo = (isset($_REQUEST['page'])) ? (int)$_REQUEST['page'] : 0;
	// counting from last
	if ($pageNo <= 0) $pageNo = Mosaic::getCurrentWorkingPageNo() - 1 - $pageNo;

	$includeRecent = (isset($_REQUEST['include_recent']) && $_REQUEST['include_recent']);

	$recent = array();

	// init response

	$data = array(
		'pageNo' => $pageNo,
		'tiles' => array(),
		'lastId' => null,
		'msg' => null
	);

	// valid request?

	if ($pageNo)
	{
		$lastId = 0;
		$tweets = Mosaic::getPageData($pageNo);

		$tiles = array();
		foreach ($tweets as $tweet)
		{
			$tiles[] = $tweet;
			if ($tweet['id'] > $lastId) $lastId = $tweet['id'];
		}

		if (count($tiles))
		{
			$data['tiles'] = $tiles;
			$data['lastId'] = $lastId;
		}
	}
	else
	{
		$data['msg'] = 'bad page number';
	}

	dd('page:' .  $pageNo . ' count:' . count($data['tweets']) . ' lastId:' . $data['lastId']);

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