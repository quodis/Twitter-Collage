<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * WARNING this file defines serveral classes:
 *  - Tweet
 */

/**
 * interface to table `tweet`
 */
/**
 * @author andrezero
 *
 */
final class Tweet
{

	// because _hit happens
	const HARDCODED_LIMIT = 1000;

	/**
	 * @var array
	 */
	public static $_fieldMap = array(
		'page'              => 'page',
		'position'          => 'position',
		'id_str'            => 'twitterId',
		'from_user_id_str'  => 'userId',
		'from_user'         => 'userName',
		'profile_image_url' => 'imageUrl',
		'created_at'        => 'createdDate',
		'created_ts'        => 'createdTs',
		'text'              => 'contents',
		'iso_language_code' => 'isoLanguage',
	);


	/**
	 * insert tweet
	 *
	 * @param array data (by reference)
	 * @param string $insertId (returned by reference)
	 *
	 * @return array
	 */
	public static function insert(array & $data, & $insertId = null)
	{
		// add timestamp
		$data['created_ts'] = strtotime($data['created_at']);

		foreach (self::$_fieldMap as $from => $to)
		{
			// fail silently
			if (!isset($data[$from])) $data[$from] = '';

			$values[$to] = Db::escape($data[$from]);
		}

		// add payload
		$values['payload'] = Db::escape(json_encode($data));

		// insert tweet
		$sql = "INSERT INTO `tweet` (`" . implode("`, `", array_keys($values)) . "`)";
		$sql.= "  VALUES ('" . implode("', '", $values) . "')";

		$result = Db::execute($sql);

		// ERROR
		if (!$result->success()) throw new Exception('could not insert tweet: ' . $result->error());

		$insertId = Db::lastInsertId();

		$values['id'] = $insertId;

		return $result->success() ? $values : null;
	}


	/**
	 * update tweet image
	 *
	 * @param integer $id
	 * @param string $imageData (by reference)
	 *
	 * @return boolean
	 */
	public static function updateImage($id, & $imageData)
	{
		$id = Db::escape($id);

		$processedTs = time();

		// update tweet
		$sql = "UPDATE `tweet` SET ";
		$sql.= "  `imageData` = '$imageData', ";
		$sql.= "  `processedTs` = '$processedTs'";
		$sql.= "  WHERE id = '$id'";
		$result = Db::execute($sql);

		// ERROR
		if (!$result->success()) throw new Exception('could not update tweet: ' . $result->error());

		return $result->success();
	}


	/**
	 * delete tweet
	 *
	 * @param integer $id
	 * @param string $imageData (by reference)
	 *
	 * @return DataResult
	 */
	public static function delete($id)
	{
		$id = Db::escape($id);

		// update tweet
		$sql = "DELETE FROM `tweet` WHERE id = '$id'";
		$result = Db::execute($sql);

		return $result;
	}


	/**
	 * delete tweets of user
	 *
	 * @param integer $userId
	 *
	 * @return DataResult
	 */
	public static function deleteUser($userId)
	{
		$userId = Db::escape($userId);

		// update tweet
		$sql = "DELETE FROM `tweet` WHERE `userid` = '$userId'";
		$result = Db::execute($sql);

		return $result;
	}


	/**
	 * count tweets
	 *
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return array
	 */
	public static function getCount($withImage = FALSE)
	{
		$withImage = !!$withImage;

		// or from db
		$sql = "SELECT count(1) AS cnt FROM `tweet`";

		if ($withImage) $sql.= " WHERE processedTs";

		$row = Db::queryValue($sql, 'cnt');

		return $row;
	}


	/**
	 * fetch the tweet by id
	 *
	 * @param string $hash
	 *
	 * @return array
	 */
	public static function getById($id)
	{
		$id = Db::escape($id);
		$sql = "SELECT * FROM `tweet` WHERE `id` = '$id'";
		$row = Db::queryRow($sql);

		return $row;
	}


	/**
	 * first incomplete page
	 *
	 * @param integer $pageSize
	 *
	 * @return integer
	 */
	public static function getFirstIncompletePage($pageSize)
	{
		$pageSize = Db::escape($pageSize);

		$sql = "SELECT page, cnt FROM ";
		$sql.="  (SELECT page, COUNT(1) AS cnt FROM tweet GROUP BY page) AS pages";
		$sql.=" WHERE cnt < $pageSize ORDER BY page LIMIT 1";

		return Db::queryValue($sql, 'page');
	}


	/**
	 * last complete page
	 *
	 * @param integer $pageSize
	 *
	 * @return integer
	 */
	public static function getLastCompletePage($pageSize)
	{
		$pageSize = Db::escape($pageSize);

		$sql = "SELECT page, cnt FROM ";
		$sql.="  (SELECT page, COUNT(1) AS cnt FROM tweet GROUP BY page) AS pages";
		$sql.=" WHERE cnt = $pageSize ORDER BY page DESC LIMIT 1";

		return Db::queryValue($sql, 'page');
	}


	/**
	 * pages modified since
	 *
	 * @param integer $processedTs
	 *
	 * @return integer
	 */
	public static function getProcessedPages($processedTs)
	{
		$processedTs = Db::escape($processedTs);

		if (empty($processedTs)) $processedTs = 0;

		$sql = "SELECT page FROM tweet WHERE processedTs > $processedTs GROUP BY page ORDER BY processedTs DESC";

		return Db::query($sql);
	}


	/**
	 * @return integer
	 */
	public static function getLastPage()
	{
		$sql = "SELECT MAX(page) AS page FROM `tweet`";

		return Db::queryValue($sql, 'page');
	}


	/**
	 * last tweet
	 *
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return array
	 */
	public static function getLast($withImage = FALSE)
	{
		$withImage = !!$withImage;

		$sql = "SELECT * FROM `tweet` ";

		if ($withImage) $sql.= " WHERE processedTs";

		$sql.= " ORDER BY `id` DESC LIMIT 1";

		$row = Db::queryRow($sql);

		return $row;
	}


	/**
	 * last tweet by user id
	 *
	 * @param string $userId
	 *
	 * @return array
	 */
	public static function getLastByUserId($userId)
	{
		$userId = Db::escape($userId);
		$sql = "SELECT id, page, position, twitterId, userId, userName, imageUrl, createdDate, createdTs, contents, isoLanguage, imageData, processedTs FROM `tweet` ";
		$sql.= " WHERE `userid` = '$userId' ORDER BY `id` DESC LIMIT 1";

		$row = Db::queryRow($sql);

		return $row;
	}


	/**
	 * last tweet by user name
	 *
	 * @param string $userName
	 *
	 * @return array
	 */
	public static function getLastByUserName($userName)
	{
		$userName = Db::escape($userName);
		$sql = "SELECT id, page, position, twitterId, userId, userName, imageUrl, createdDate, createdTs, contents, isoLanguage, imageData, processedTs FROM `tweet` ";
		$sql.= " WHERE `userName` = '$userName' ORDER BY `id` DESC LIMIT 1";

		$row = Db::queryRow($sql);

		return $row;
	}


	/**
	 * tweets without processed image
	 *
	 * @param integer $limit (optional)
	 *
	 * @return array
	 */
	public static function getUnprocessed($limit = null)
	{
		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$sql = "SELECT id, page, position, twitterId, userId, userName, imageUrl, createdDate, createdTs, contents, isoLanguage, imageData, processedTs FROM `tweet` ";
		$sql.= " WHERE `imageData` IS NULL LIMIT $limit";

		$result = Db::query($sql);

		return $result;
	}


	/**
	 * tweets of this page
	 *
	 * @param integer $pageNo
	 * @param integer $lastId
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return array
	 */
	public static function getByPage($pageNo, $lastId = null, $withImage = FALSE)
	{
		$pageNo = (int)$pageNo;
		$lastId = (int)$lastId;
		$withImage = !!$withImage;

		$sql = "SELECT id, page, position, twitterId, userId, userName, imageUrl, createdDate, createdTs, contents, isoLanguage, imageData FROM `tweet` ";
		$sql.= " WHERE page = $pageNo ";

		if ($withImage) $sql.= "  AND processedTs";

		if ($lastId) $sql.= "  AND id > $lastId";

		$sql.= " ORDER BY `id` ASC";

		$result = Db::query($sql);

		return $result;
	}


	/**
	 *
	 * @param integer $lastId
	 * @para ingeger $limit (optional)
	 *
	 * @return array
	 */
	public static function getSinceLastId($lastId, $limit = null)
	{
		$lastId = Db::escape($lastId);

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		//$sql = "SELECT id, page, position, twitterId, userId, userName, imageUrl, createdDate, createdTs, contents, isoLanguage, imageData FROM `tweet` ";
		$sql = "SELECT id, position, twitterId, userName, imageUrl, createdTs, contents, imageData FROM `tweet` ";
		$sql.= " WHERE `imageData` IS NOT NULL";

		if ($lastId)
		{
			$sql.= " AND id > $lastId";
			$sql.= " ORDER BY `id` ASC";
		}
		else
		{
			$sql.= " ORDER BY `id` DESC";
		}

		$sql.= " LIMIT $limit";

		$result = Db::query($sql);

		return $result;
	}


	/**
	 * users
	 *
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return array
	 */
	public static function getUserCount($withImage = null)
	{
		$withImage = !!$withImage;

		$sql = "SELECT COUNT(DISTINCT userId) AS cnt FROM `tweet` ";

		if ($withImage) $sql.= " WHERE processedTs";

		return Db::queryValue($sql, 'cnt');
	}

	/**
	 * users by terms
	 *
	 * @param string $terms
	 * @param integer $limit (optional)
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return array
	 */
	public static function getUsersByTerms($terms, $limit = null, $withImage = null)
	{
		$terms = Db::escape($terms);

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$withImage = !!$withImage;

		//$sql = "SELECT id, page, position, twitterId, userId, userName, imageUrl, createdDate, createdTs, contents, isoLanguage, imageData FROM `tweet` ";
		$sql = "SELECT id, position, twitterId, userId, userName, imageUrl, createdTs, contents, imageData FROM `tweet` ";
		$sql.= " WHERE `userName` LIKE '%$terms%'";

		if ($withImage) $sql.= "  AND processedTs";

		$sql.= " GROUP BY `userName`";
		$sql.= " ORDER BY `userName` ASC";
		$sql.= " LIMIT $limit";

		$result = Db::query($sql);

		if ($result->count() == $limit)
		{
			$sql = "SELECT count(distinct userName) AS cnt FROM `tweet` ";
			$sql.= " WHERE `userName` LIKE '%$terms%'";
			if ($withImage) $sql.= "  AND processedTs";
			$total = Db::queryValue($sql, 'cnt');
			if ($total) $result->setTotal($total);
		}

		return $result;
	}


	/**
	 * tweets by user
	 *
	 * @param string $userName
	 * @param integer $limit (optional)
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return array
	 */
	public static function getByUsername($userName, $limit = null, $withImage = null)
	{
		$userName = Db::escape($userName);

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$withImage = !!$withImage;

		//$sql = "SELECT id, page, position, twitterId, userId, userName, imageUrl, createdDate, createdTs, contents, isoLanguage, imageData FROM `tweet` ";
		$sql = "SELECT id, position, twitterId, userName, imageUrl, createdTs, contents, imageData FROM `tweet` ";
		$sql.= " WHERE`userName` = '$userName'";

		if ($withImage) $sql.= "  AND processedTs";

		$sql.= " ORDER BY `id` DESC";
		$sql.= " LIMIT $limit";

		$result = Db::query($sql);

		if ($result->count() == $limit)
		{
			$sql = "SELECT count(1) AS cnt FROM `tweet` ";
			$sql.= " WHERE `userName` = '$userName'";
			if ($withImage) $sql.= "  AND processedTs";
			$total = Db::queryValue($sql, 'cnt');
			if ($total) $result->setTotal($total);
		}

		return $result;
	}


	/**
	 * tweets by terms
	 *
	 * @param string $terms
	 * @param integer $limit (optional)
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return array
	 */
	public static function getByTerms($terms, $limit = null, $withImage = null)
	{
		$terms = Db::escape($terms);

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$withImage = !!$withImage;

		$sql = "SELECT id, page, position, twitterId, userId, userName, imageUrl, createdDate, createdTs, contents, isoLanguage, imageData FROM `tweet` ";
		$sql.= " WHERE `contents` LIKE '%$terms%'";

		if ($withImage) $sql.= "  AND processedTs";

		$sql.= " ORDER BY `id` DESC";
		$sql.= " LIMIT $limit";

		$result = Db::query($sql);

		if ($result->count() == $limit)
		{
			$sql = "SELECT count(1) AS cnt FROM `tweet` ";
			$sql.= " WHERE `contents` LIKE '%$terms%'";
			if ($withImage) $sql.= "  AND processedTs";
			$total = Db::queryValue($sql, 'cnt');
			if ($total) $result->setTotal($total);
		}

		return $result;
	}

	/**
	 * tweets by terms
	 *
	 * @param integer $limit (optional)
	 *
	 * @return array
	 */
	public static function getAverageDelay($limit = null)
	{
		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$sql = "SELECT createdTs, processedTs, (processedTs - createdTs) AS elapsed FROM `tweet` ";
		$sql.= " WHERE `processedTs` AND `processedTs` >= `processedTs`";
		$sql.=" ORDER BY id DESC ";
		$sql.= " LIMIT 2";

		$result = Db::query($sql);

		$elapsed = 0;
		while ($row = $result->row())
		{
			$elapsed += $row['elapsed'];
		}

		return $elapsed ? floor($elapsed / $result->count()) : 0;
	}

}

?>
