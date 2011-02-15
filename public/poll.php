<?php
/**
 * @package    TwitterCollage
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

	if (isset($_REQUEST['pageNo']))
	{
		$pageNo = (int)$_REQUEST['pageNo'];
		if ($pageNo == 0) $pageNo = 1;
	}
	else $pageNo = Collage::getCurrentViewingPageNo();

	dd('pageNo:' . $pageNo);

	$result = Tweet::getByPageWithImage($pageNo, Collage::getPageSize());

	dd('count:' . $result->count());

	$tweets = array();
	while ($row = $result->row())
	{
		unset($row['payload']);
		$tweets[] = $row;
	}

	$data = array(
		'pageNo' => $pageNo,
		'tweets' => $tweets,
	);

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