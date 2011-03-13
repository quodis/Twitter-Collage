<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.4
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * forces making one API call without the lastId
 * may result in fetching a big amount of tweets (1500 max)
 * may result in "trying" to insert same tweets (will fail silently)
 */

/**
 * escape from global scope
 */
function main()
{
	DEFINE('CLIENT', 'script');
	DEFINE('CONTEXT', __FILE__);
	include dirname(__FILE__) .  '/../bootstrap.php';

	$tests = array(
		array('getCount', array()),
		array('getCount', array(TRUE)),
		array('getById', array(554)),
		array('getLastCoupleOfPages', array()),
		array('getLastCoupleOfPages', array(TRUE)),
		array('getLatestMosaic', array(1804)),
		array('getLatestMosaic', array(1,2,3)),
		array('getLastPage', array()),
		array('getLast', array()),
		array('getLast', array(TRUE)),
		array('getLastByUserName', array("fareskunk")),
		array('getLastByUserName', array("fooo")),
		array('getUnprocessed', array(1)),
		array('getByPage', array(1, 1)),
		array('getByPage', array(1, 1, TRUE)),
		array('getSinceLastId', array(1, 10)),
		array('getUserCount', array()),
		array('getUsersByTerms', array('a', 10)),
		array('getUsersByTerms', array('a', 10)),
		array('getByUserName', array('fareskunk', 10)),
		array('getByUserName', array('fareskunk', 10, TRUE)),
		array('getByTerms', array('a', 10)),
		array('getByTerms', array('a', 10, TRUE)),
		array('getAverageDelay', array()),
	);


	while (list($func, $args) = array_shift($tests))
	{
		$response = call_user_func_array(array('Tweet', $func), $args);

		if (is_array($response)) {
			dd($func . '(' . implode(', ', $args) . ') [ ' . substr(implode(', ' , $response), 0, 80));
		}
		elseif ($response instanceof mysqli_stmt_wrap || $response instanceof mysqli_stmt_empty) {
			$row = ($row = $response->row()) ? substr(implode(', ', $row), 0, 80) : '----';
			dd($func . '(' . implode(', ', $args) . ') -> ' . $response->count() . '/' . $response->total() . ' -> ' . $row);
		}
		else dd($func . '(' . implode(', ', $args) . ') = ' . $response);


	}





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