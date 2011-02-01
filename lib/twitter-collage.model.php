<?php
/**
 * @package    TwitterCollage
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
final class Tweet
{

	/**
	 * @var array
	 */
	public static $_fieldMap = array(
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
	 * @return boolean
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

		//if (isset($data['id'])) $values['id'] = $data['id'];

		// add payload
		$values['payload'] = Db::escape(json_encode($data));

		// insert tweet
		$sql = "INSERT INTO `tweet` (`" . implode("`, `", array_keys($values)) . "`)";
		$sql.= "  VALUES ('" . implode("', '", $values) . "')";

		$result = Db::execute($sql);

		// ERROR
		if (!$result->success()) throw new Exception('could not insert tweet: ' . $result->error());

		$insertId = Db::lastInsertId();

		return $result->success();
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

		// update tweet
		$sql = "UPDATE `tweet` SET `imageData` = '$imageData'";
		$sql.= "  WHERE id = '$id'";
		$result = Db::execute($sql);

		// ERROR
		if (!$result->success()) throw new Exception('could not update tweet: ' . $result->error());

		return $result->success();
	}


	/**
	 * count tweets
	 *
	 * @return array
	 */
	public static function getCount()
	{
		// or from db
		$sql = "SELECT count(1) FROM `tweet`";
		$row = Db::queryRow($sql);

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
	 * last tweet
	 *
	 * @param string $id
	 *
	 * @return array
	 */
	public static function getLast()
	{
		$sql = "SELECT * FROM `tweet` ORDER BY `id` DESC LIMIT 1";
		$row = Db::queryRow($sql);

		return $row;
	}


	/**
	 * last tweet with image
	 *
	 * @param string $id
	 *
	 * @return array
	 */
	public static function getLastWithImage()
	{
		// or from db
		$sql = "SELECT * FROM `tweet` WHERE `imageData` ORDER BY `id` DESC LIMIT 1";
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
		$sql = "SELECT * FROM `tweet` WHERE `userid` = '$userId' ORDER BY `id` DESC LIMIT 1";
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
		$sql = "SELECT * FROM `tweet` WHERE `userName` = '$userName' ORDER BY `id` DESC LIMIT 1";
		$row = Db::queryRow($sql);

		return $row;
	}


	/**
	 * tweets without processed image
	 *
	 * @return array
	 */
	public static function getUnprocessed()
	{
		$sql = "SELECT * FROM `tweet` WHERE `imageData` IS NULL LIMIT 100";

		$result = Db::query($sql);

		return $result;
	}


	/**
	 * tweets of this page
	 *
	 * @param integer $pageNo
	 * @param integer $pageSize
	 * @param integer $lastId
	 *
	 * @return array
	 */
	public static function getByPage($pageNo, $pageSize, $lastId = null)
	{
		$pageNo = (int)$pageNo;
		$from = $pageNo * $pageSize;
		$through = ($pageNo + 1) * $pageSize;

		if ($lastId > $from) $from = $lastId;

		$sql = "SELECT * FROM `tweet` ";
		$sql.= " WHERE `imageData` IS NOT NULL";
		$sql.= " AND id > $from AND id <= $through";
		$sql.= " ORDER BY `id` ASC";

		$result = Db::query($sql);

		return $result;
	}
}

?>