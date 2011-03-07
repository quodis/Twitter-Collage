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

		if ($row = $stmt->row())
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

		return $stmt->row();
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
		global $mysqli;

		$sql = "SELECT page, cnt FROM ";
		$sql.="  (SELECT page, COUNT(1) AS cnt FROM tweet GROUP BY page) AS pages";
		$sql.=" WHERE cnt < ? ORDER BY page LIMIT 1";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('i', $pageSize);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();

		if ($row = $stmt->row())
		{
			return $row['page'];
		}
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
		global $mysqli;

		$sql = "SELECT page, cnt FROM ";
		$sql.="  (SELECT page, COUNT(1) AS cnt FROM tweet WHERE processedTs GROUP BY page) AS pages";
		$sql.=" WHERE cnt = ? ORDER BY page DESC LIMIT 1";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('i', $pageSize);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();

		if ($row = $stmt->row())
		{
			return $row['page'];
		}
	}


	/**
	 * pages modified since
	 *
	 * @param integer $processedTs
	 *
	 * @return mysqli_stmt
	 */
	public static function getProcessedPages($processedTs)
	{
		global $mysqli;

		if (empty($processedTs)) $processedTs = 0;

		$sql = "SELECT page FROM tweet WHERE processedTs > ? GROUP BY page ORDER BY processedTs DESC";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('s', $processedTs);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();

		return $stmt;
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

		if ($row = $stmt->row())
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

		return $stmt->row();
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

		return $stmt->row();
	}


	/**
	 * tweets without processed image
	 *
	 * @param integer $limit (optional)
	 *
	 * @return stmt_Extended
	 */
	public static function getUnprocessed($limit = null)
	{
		global $mysqli;

		$limit = (int)$limit;
		if (!$limit || $limit > self::HARDCODED_LIMIT) $limit = self::HARDCODED_LIMIT;

		$sql = "SELECT id, page, position, twitterId, userId, userName, imageUrl, createdDate, createdTs, contents, isoLanguage, imageData, processedTs FROM `tweet` ";
		$sql.= " WHERE `imageData` IS NULL LIMIT ?";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('s', $limit);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();

		return $stmt;
	}


	/**
	 * tweets of this page
	 *
	 * @param integer $pageNo
	 * @param integer $lastId
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return stmt_Extended
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

		return $stmt;
	}


	/**
	 *
	 * @param integer $lastId
	 * @para ingeger $limit (optional)
	 *
	 * @return stmt_Extended
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

		return $stmt;
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
		global $mysqli;

		$withImage = !!$withImage;

		$sql = "SELECT COUNT(DISTINCT userId) AS cnt FROM `tweet` ";

		if ($withImage) $sql.= " WHERE processedTs";

		$stmt = $mysqli->prepare($sql);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();

		if ($row = $stmt->row())
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
	 * @return stmt_Extended
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

		$total = ($row = $stmt->row()) ? $row['cnt'] : 0;

		// no results, bail out
		if (!$total) return new stmt_Empty();

		// get results
		$sql = "SELECT id AS i, position AS p, twitterId AS w, userName AS u, imageUrl AS m , createdTs AS c, contents AS n, imageData AS d FROM `tweet` ";
		$sql.= " WHERE `userName` LIKE ?";

		if ($withImage) $sql.= "  AND processedTs";

		$sql.= " GROUP BY `userName`";
		$sql.= " ORDER BY `userName` ASC";
		$sql.= " LIMIT ?";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('si', $terms, $limit);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();

		$stmt->setTotal($total);

		return $stmt;
	}


	/**
	 * tweets by user (compacted version)
	 *
	 * @param string $userName
	 * @param integer $limit (optional)
	 * @param boolean $withImage (optional, defaults to FALSE)
	 *
	 * @return array
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

		$total = ($row = $stmt->row()) ? $row['cnt'] : 0;

		// no results, bail out
		if (!$total) return new stmt_Empty();

		// get results
		$sql = "SELECT id AS i, position AS p, twitterId AS w, userName AS u, imageUrl AS m , createdTs AS c, contents AS n, imageData AS d FROM `tweet` ";
		$sql.= " WHERE`userName` = ?";

		if ($withImage) $sql.= "  AND processedTs";

		$sql.= " ORDER BY `id` DESC";
		$sql.= " LIMIT ?";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('si', $userName, $limit);

		$ok = $stmt->execute();
		if (!$ok) throw new Exception($stmt->error);
		$stmt->store_result();

		$stmt->setTotal($total);

		return $stmt;
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

		$total = ($row = $stmt->row()) ? $row['cnt'] : 0;

		// no results, bail out
		if (!$total) return new stmt_Empty();

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

		$stmt->setTotal($total);

		return $stmt;
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

		$elapsed = 0;
		while ($row = $stmt->row())
		{
			$elapsed += $row['elapsed'];
		}

		return $elapsed && $stmt->count() ? floor($elapsed / $stmt->count()) : 0;
	}

}

/**
 * extended to yeld handier version of mysqli_stmt
 */
class mysqli_Extended extends mysqli
{
	protected $selfReference;

	public function __construct()
	{
		parent::__construct();

	}

	public function prepare($query)
	{
		$stmt = new stmt_Extended($this, $query);

		return $stmt;
	}
}

/**
 * mocks DataResult on client code
 * and gets rid of hard-coded bind calls + references in returned rows
 */
class stmt_Extended extends mysqli_stmt
{
	protected $varsBound = false;
	protected $results;
	protected $total;

	public function __construct($link, $query)
	{
		parent::__construct($link, $query);
	}

	public function row()
	{
		// bind once
		if (!$this->varsBound)
		{
			$meta = $this->result_metadata();
			while ($column = $meta->fetch_field())
			{
				// prevent syntax errors if column names have a space in
				$columnName = str_replace(' ', '_', $column->name);
				$bindVarArray[] = &$this->results[$columnName];
			}
			// using refValues() for compatibility with PHP 5.3 (dev and staging boxes)
			call_user_func_array(array($this, 'bind_result'), refValues($bindVarArray));
			$this->varsBound = true;
		}

		if ($this->fetch() != null)
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
		return $this->num_rows;
	}
}

/**
 * mocks DataResult on client code
 */
class stmt_Empty
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