<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * WARNING this file defines serveral classes:
 *  - Tweet, implements all db operations
 *  - mysqli_stmt_wrap + mysqli_stmt_empty, a sweet pair of anti-patterns due to php's mysqli poor api and some annoying bug in php 5.2.6 that won't let you subclass mysqli and mysqli_stmt
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
		global $mysqli;

		// add timestamp
		$data['created_ts'] = strtotime($data['created_at']);

		foreach (self::$_fieldMap as $from => $to)
		{
			// fail silently
			if (!isset($data[$from])) $data[$from] = '';
			$values[$to] = $data[$from];
		}

		// add payload
		$values['payload'] = json_encode($data);

		$sql = "INSERT INTO `tweet` (`page`, `position`, `twitterId`, `userId`, `userName`, `imageUrl`, `createdDate`, `createdTs`, `contents`, `isoLanguage`, `payload`) ";
		$sql.= " VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

		// insert tweet
		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('sisssssssss', $values['page'], $values['position'], $values['twitterId'], $values['userId'], $values['userName'], $values['imageUrl'], $values['createdDate'], $values['createdTs'], $values['contents'], $values['isoLanguage'], $values['payload']);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception('could not insert tweet: ' . $stmt->error);

		$insertId = $mysqli->insert_id;

		$values['id'] = $insertId;

		return $ok ? $values : null;
	}


	/**
	 * update tweet image
	 *
	 * @param integer $id
	 * @param string $imageData (by reference)
	 * @param string $imageUrl (optional)
	 *
	 * @throws Exception
	 */
	public static function updateImage($id, & $imageData, $imageUrl = null)
	{
		global $mysqli;

		$processedTs = time();

		// update tweet
		$sql = "UPDATE `tweet` SET";
		$sql.= "  `imageData` = ?,";
		if ($imageUrl) $sql.= "  `imageUrl` = ?,";
		$sql.= "  `processedTs` = ?";
		$sql.= "  WHERE id = ?";

		$stmt = $mysqli->prepare($sql);
		if ($imageUrl)
		{
			$stmt->bind_param('ssss', $imageData, $imageUrl, $processedTs, $id);
		}
		else $stmt->bind_param('sss', $imageData, $processedTs, $id);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception('could not update tweet: ' . $stmt->error);
	}


	/**
	 * delete tweet
	 *
	 * @param integer $id
	 * @param string $imageData (by reference)
	 *
	 * @return boolean
	 */
	public static function delete($id)
	{
		global $mysqli;

		// delete tweet
		$sql = "DELETE FROM `tweet` WHERE id = ?";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('s', $id);

		$ok = $stmt->execute();
		$stmt->close();
		return $ok;
	}


	/**
	 * delete tweets of user
	 *
	 * @param string $userName
	 *
	 * @return boolean
	 */
	public static function deleteUser($userName)
	{
		global $mysqli;

		// delete all tweets of user
		$sql = "DELETE FROM `tweet` WHERE `userName` = ?";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('s', $userName);

		$ok = $stmt->execute();
		$stmt->close();
		return $ok;
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
		global $mysqli;

		$withImage = !!$withImage;

		// or from db
		$sql = "SELECT count(1) AS cnt FROM `tweet`";

		if ($withImage) $sql.= " WHERE processedTs";

		$stmt = $mysqli->prepare($sql);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		if ($row = $result->row())
		{
			return $row['cnt'];
		}
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
		global $mysqli;

		$sql = "SELECT * FROM `tweet` WHERE `id` = ?";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('s', $id);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		return $result->row();
	}


	/**
	 * determine the "page to be completed" (changed: gives priority to p-1
	 * this means preserving a "fresh complete page", even if deletes occur
	 *
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return mysqli_stmt_wrap (columns: page, cnt)
	 */
	public static function getLastCoupleOfPages($withImage = FALSE)
	{
		global $mysqli;

		$withImage = !!$withImage;

		$sql = "SELECT page, COUNT(1) AS cnt FROM tweet ";
		if ($withImage) $sql.= " WHERE processedTs";
		$sql.= "  GROUP BY page ORDER BY page DESC LIMIT 2";

		$stmt = $mysqli->prepare($sql);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		return $result;
	}


	/**
	 * last complete page^H^H^H^Hmosaic (changed: complete mosaic, regardless of page, this means freshness++)
	 *
	 * @param integer $pageSize
	 *
	 * @return mysqli_stmt_wrap
	 */
	public static function getLatestMosaic($pageSize)
	{
		global $mysqli;

		$sql = "SELECT id AS i, position AS p, twitterId AS w, userName AS u, imageUrl AS m , createdTs AS c, contents AS n FROM ";
		$sql.="  (SELECT id, page, position, twitterId, userName, imageUrl, createdTs, contents FROM tweet WHERE processedTs ORDER BY page DESC LIMIT ?) AS latest";
		$sql.=" GROUP BY position ORDER BY createdTs DESC";

		$stmt = $mysqli->prepare($sql);
		$limit = $pageSize * 3;
		$stmt->bind_param('i', $limit);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		return $result;
	}


	/**
	 * last fallback tiles for certain positions (deja-vu)
	 *
	 * @param integer $positions
	 *
	 * @return mysqli_stmt_wrap
	 */
	public static function getFallbackTiles(array $positions)
	{
		global $mysqli;

		foreach ($positions as $foo) $placeholders[] = '?';

		$sql = "SELECT id AS i, position AS p, twitterId AS w, userName AS u, imageUrl AS m , createdTs AS c, contents AS n, imageData AS d FROM tweet ";
		$sql.=" WHERE processedTs AND id in (" . implode(', ', $placeholders) . ")";
		$sql.=" ORDER BY createdTs DESC";

		$stmt = $mysqli->prepare($sql);

		$types = str_repeat('i', count($positions));
		$args = array_merge(array($types), $positions);

		call_user_func_array(array($stmt, 'bind_param'), refValues($args));

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		return $result;
	}

	/**
	 * @return integer
	 */
	public static function getLastPage()
	{
		global $mysqli;

		$sql = "SELECT MAX(page) AS page FROM `tweet`";

		$stmt = $mysqli->prepare($sql);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		if ($row = $result->row())
		{
			return $row['page'];
		}
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
		global $mysqli;

		$withImage = !!$withImage;

		$sql = "SELECT * FROM `tweet` ";

		if ($withImage) $sql.= " WHERE processedTs";

		$sql.= " ORDER BY `id` DESC LIMIT 1";

		$stmt = $mysqli->prepare($sql);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		return $result->row();
	}


	/**
	 * latest tweet from twitter
	 *
	 * @return integer
	 */
	public static function getLastTwitterId()
	{
		global $mysqli;

		$sql = "SELECT twitterId FROM `tweet` ";
		$sql.= " ORDER BY `createdTs` DESC LIMIT 1";

		$stmt = $mysqli->prepare($sql);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		if ($row = $result->row())
		{
			return $row['twitterId'];
		}
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
		global $mysqli;

		$sql = "SELECT id, page, position, twitterId, userId, userName, imageUrl, createdDate, createdTs, contents, isoLanguage, imageData, processedTs FROM `tweet` ";
		$sql.= " WHERE `userName` = ? ORDER BY `id` DESC LIMIT 1";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('s', $userName);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		return $result->row();
	}


	/**
	 * tweets without processed image (changed: give priority to newest tweets)
	 *
	 * @param integer $pageNo (optional)
	 * @param integer $limit (optional)
	 *
	 * @return mysqli_stmt_wrap
	 */
	public static function getUnprocessed($pageNo = null, $limit = null)
	{
		global $mysqli;

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$sql = "SELECT id, page, position, imageUrl, processedTs FROM `tweet` ";
		$sql.= " WHERE `imageData` IS NULL ";
		if ($pageNo) $sql.= " AND `page` = ? ";
		$sql.= " ORDER BY createdTs ASC LIMIT ?";

		$stmt = $mysqli->prepare($sql);
		if ($pageNo)
		{
			$stmt->bind_param('ii', $pageNo, $limit);
		}
		else $stmt->bind_param('i', $limit);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		return $result;
	}


	/**
	 * tweets of this page
	 *
	 * @param integer $pageNo
	 * @param integer $lastId
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return mysqli_stmt_wrap
	 */
	public static function getByPage($pageNo, $lastId = null, $withImage = FALSE)
	{
		global $mysqli;

		$pageNo = (int)$pageNo;
		$lastId = (int)$lastId;
		$withImage = !!$withImage;

		$sql = "SELECT id AS i, position AS p, twitterId AS w, userName AS u, imageUrl AS m , createdTs AS c, contents AS n, imageData AS d FROM `tweet` ";
		$sql.= " WHERE page = ? ";

		if ($withImage) $sql.= "  AND processedTs";

		if ($lastId) $sql.= "  AND id > ?";

		$sql.= " ORDER BY `id` ASC";

		$stmt = $mysqli->prepare($sql);
		if ($lastId)
		{
			$stmt->bind_param('is', $pageNo, $lastId);
		}
		else $stmt->bind_param('i', $pageNo);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		return $result;
	}


	/**
	 *
	 * @param integer $lastId
	 * @param ingeger $limit (optional)
	 *
	 * @return mysqli_stmt_wrap
	 */
	public static function getSinceLastId($lastId, $limit = null)
	{
		global $mysqli;

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$sql = "SELECT id AS i, position AS p, twitterId AS w, userName AS u, imageUrl AS m , createdTs AS c, contents AS n, imageData AS d FROM `tweet` ";
		$sql.= " WHERE `imageData` IS NOT NULL";

		if ($lastId)
		{
			$sql.= " AND id > ?";
			$sql.= " ORDER BY `id` ASC";
		}
		else
		{
			$sql.= " ORDER BY `id` DESC";
		}

		$sql.= " LIMIT ?";

		$stmt = $mysqli->prepare($sql);
		if ($lastId)
		{
			$stmt->bind_param('si', $lastId, $limit);
		}
		else $stmt->bind_param('i', $limit);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		return $result;
	}


	/**
	 * users
	 *
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return integer
	 */
	public static function getUserCount($withImage = null)
	{
		global $mysqli;

		$withImage = !!$withImage;

		$sql = "SELECT COUNT(DISTINCT userId) AS cnt FROM `tweet` ";

		if ($withImage) $sql.= " WHERE processedTs";

		$stmt = $mysqli->prepare($sql);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		if ($row = $result->row())
		{
			return $row['cnt'];
		}
	}


	/**
	 * users by terms
	 *
	 * @param string $terms
	 * @param integer $limit (optional)
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return mysqli_stmt_wrap
	 */
	public static function getUsersByTerms($terms, $limit = null, $withImage = null)
	{
		global $mysqli;

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$withImage = !!$withImage;

		$terms = '%' . str_replace(array('%', '_'), array('\%', '\_'), $terms) . '%';

		// count query
		$sql= "SELECT count(distinct userName) AS cnt FROM `tweet`  WHERE `userName` LIKE ? AND processedTs" ;
		if ($withImage) $sql.= "  AND processedTs";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('s', $terms);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		$total = ($row = $result->row()) ? $row['cnt'] : 0;

		// no results, bail out
		if (!$total) return new mysqli_stmt_empty();

		// get results
		$sql = "SELECT id AS i, position AS p, twitterId AS w, userName AS u, imageUrl AS m , createdTs AS c, contents AS n, imageData AS d FROM `tweet` ";
		$sql.= " WHERE `userName` LIKE ?";

		if ($withImage) $sql.= " AND processedTs";

		$sql.= " GROUP BY `userName`";
		$sql.= " ORDER BY `userName` ASC";
		$sql.= " LIMIT ?";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('si', $terms, $limit);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		$result->setTotal($total);

		return $result;
	}


	/**
	 * tweets by user (compacted version)
	 *
	 * @param string $userName
	 * @param integer $limit (optional)
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return mysqli_stmt_wrap
	 */
	public static function getByUserName($userName, $limit = null, $withImage = null)
	{
		global $mysqli;

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$withImage = !!$withImage;

		// count query
		$sql = "SELECT count(1) AS cnt FROM `tweet` ";
		$sql.= " WHERE `userName` = ?";
		if ($withImage) $sql.= "  AND processedTs";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('s', $userName);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		$total = ($row = $result->row()) ? $row['cnt'] : 0;

		// no results, bail out
		if (!$total) return new mysqli_stmt_empty();

		// get results
		$sql = "SELECT id AS i, position AS p, twitterId AS w, userName AS u, imageUrl AS m , createdTs AS c, contents AS n, imageData AS d FROM `tweet` ";
		$sql.= " WHERE `userName` = ?";

		if ($withImage) $sql.= "  AND processedTs";

		$sql.= " ORDER BY `id` DESC";
		$sql.= " LIMIT ?";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('si', $userName, $limit);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		$result->setTotal($total);

		return $result;
	}


	/**
	 * tweets by terms
	 *
	 * @param string $terms
	 * @param integer $limit (optional)
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return mysqli_stmt_wrap
	 */
	public static function getByTerms($terms, $limit = null, $withImage = null)
	{
		global $mysqli;

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$withImage = !!$withImage;

		$terms = '%' . str_replace(array('%', '_'), array('\%', '\_'), $terms) . '%';

		// count query
		$sql = "SELECT count(1) AS cnt FROM `tweet` ";
		$sql.= " WHERE `contents` LIKE ?";
		if ($withImage) $sql.= "  AND processedTs";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('s', $terms);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		$total = ($row = $result->row()) ? $row['cnt'] : 0;

		// no results, bail out
		if (!$total) return new mysqli_stmt_empty();

		// get results
		$sql = "SELECT id AS i, position AS p, twitterId AS w, userName AS u, imageUrl AS m , createdTs AS c, contents AS n, imageData AS d FROM `tweet` ";
		$sql.= " WHERE `contents` LIKE ?";

		if ($withImage) $sql.= "  AND processedTs";

		$sql.= " ORDER BY `id` DESC";
		$sql.= " LIMIT ?";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('si', $terms, $limit);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		$result->setTotal($total);

		return $result;
	}

	/**
	 * tweets by terms
	 *
	 * @param integer $limit (optional)
	 *
	 * @return integer
	 */
	public static function getAverageDelay($limit = null)
	{
		global $mysqli;

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$sql = "SELECT createdTs, processedTs, (processedTs - createdTs) AS elapsed FROM `tweet` ";
		$sql.= " WHERE `processedTs` AND `processedTs` >= `processedTs`";
		$sql.=" ORDER BY id DESC ";
		$sql.= " LIMIT 2";

		$stmt = $mysqli->prepare($sql);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		$elapsed = 0;
		while ($row = $result->row())
		{
			$elapsed += $row['elapsed'];
		}

		return $elapsed && $result->count() ? floor($elapsed / $result->count()) : 0;
	}


	/**
	 * number of unprocessed tweets
	 *
	 * @return integer
	 */
	public static function getCountUnprocessed()
	{
		global $mysqli;

		$sql = "SELECT COUNT(id) AS cnt FROM `tweet` ";
		$sql.= " WHERE `imageData` IS NULL ";

		$stmt = $mysqli->prepare($sql);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();
		$result = new mysqli_stmt_wrap($stmt);

		if ($row = $result->row())
		{
			return $row['cnt'];
		}
	}

}

/**
 * mocks DataResult on client code
 * and gets rid of hard-coded bind calls + references in returned rows
 */
class mysqli_stmt_wrap
{
	protected $stmt = null;
	protected $varsBound = false;
	protected $results;
	protected $total;

	public function __construct(mysqli_stmt $stmt)
	{
		$this->stmt = $stmt;
	}

	public function row()
	{
		// bind once
		if (!$this->varsBound)
		{
			$meta = $this->stmt->result_metadata();
			while ($column = $meta->fetch_field())
			{
				// prevent syntax errors if column names have a space in
				$columnName = str_replace(' ', '_', $column->name);
				$bindVarArray[] = &$this->results[$columnName];
			}
			// using refValues() for compatibility with PHP 5.3 (dev and staging boxes)
			call_user_func_array(array($this->stmt, 'bind_result'), refValues($bindVarArray));
			$this->varsBound = true;
		}

		if ($this->stmt->fetch() != null)
		{
			// copy values (get rid of references)
			foreach ($this->results as $k => $v)
			{
				$results[$k] = $v;
			}
			return $results;
		}
		else
		{
			return null;
		}
	}

	public function setTotal($total)
	{
		$this->total = $total;
	}

	public function total()
	{
		return isset($this->total) ? $this->total : $this->count();
	}

	public function count()
	{
		return $this->stmt->num_rows;
	}
}

/**
 * mocks DataResult on client code
 */
class mysqli_stmt_empty
{
	public function row()
	{
		return false;
	}
	public function total()
	{
		return 0;
	}

	public function count()
	{
		return 0;
	}
}

?>