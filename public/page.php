<?php
/**
 * @package    TwitterCollage
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
	if (isset($_REQUEST['page']))
	{
		$pageNo = (int)$_REQUEST['page'];
		if ($pageNo == 0) $pageNo = 1;
	}
	// last (complete) page
	else
	{
		// current page (minus one)
		$pageNo = Collage::getCurrentWorkingPageNo() - 1;
		// minus optional Z parameter
		$z = (isset($_REQUEST['z'])) ? (int)$_REQUEST['z'] : 0;
		$pageNo = $pageNo - $z;
	}

	// init response

	$data = array(
		'pageNo' => $pageNo,
		'tweets' => array(),
		'lastId' => null,
		'msg' => null
	);

	// valid request?

	if ($pageNo)
	{
		$lastId = 0;
		$pageTweets = Collage::getPageData($pageNo);

		$tweets = array();
		foreach ($pageTweets as $tweet)
		{
			$tweets[] = $tweet;
			if ($tweet['id'] > $lastId) $lastId = $tweet['id'];
		}

		if (count($tweets))
		{
			$data['tweets'] = $tweets;
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