<?php
/**
 * @package    TwitterCollage
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */


/**
 * Collage
 */
class Collage
{

	/**
	 * @var array
	 */
	private static $_config = null;

	/**
	 * @var integer
	 */
	private static $_pageSize = null;
	/**
	 * index[0] = '0x0'
	 * grid['0x0'] - array of pixels, each one containing
	 *  - color
	 *    - r
	 *    - g
	 *    - b
	 *  - row (int, base 0)
	 *  - column (int, base 0)
	 *
	 * @var array
	 */
	private static $_pageConfig = null;

	/**
	 * static class, nothing to see here, move along
	 */
	private function __constructor() {}


	/**
	 * @param array $config
	 */
	public static function configure($config)
	{
		self::$_config = $config;
	}


	// ---- page configuration


	/**
	 * page size is captured from data
	 *
	 * @integer
	 */
	public static function getPageSize()
	{
		if (!isset(self::$_pageSize))
		{
			// force load data
			if (!isset(self::$_pageConfig)) self::getPageConfig();

			self::$_pageSize = count(self::$_pageConfig['index']);
		}
		return self::$_pageSize;
	}


	/**
	 * page configuration
	 */
	public static function & getPageConfig()
	{
		if (!isset(self::$_pageConfig))
		{
			// declares $config
			$json = file_get_contents(self::_getPageConfigFileName());

			self::$_pageConfig = json_decode($json, TRUE);
		}
		return self::$_pageConfig;
	}


	/**
	 * set (and index) page data
	 *
	 * @param $grid array
	 */
	public static function setPageGrid($grid)
	{
		$index = 0;

		foreach ($grid as $rowIx => $rowPixels)
		{
			foreach ($rowPixels as $columnIx => $color)
			{
				self::$_pageConfig['grid'][$rowIx][$columnIx] = array(
					'c' => $color,
					'x' => $columnIx,
					'y' => $rowIx,
					'i' => $index,
				);

				self::$_pageConfig['index'][$index] = array(
					'x' => $columnIx,
					'y' => $rowIx,
				);

				$index++;
			}
		}

		file_put_contents(self::_getPageConfigFileName(), json_encode(self::$_pageConfig));
	}


	// ---- build


	/**
	 *
	 * @param $pageNo
	 */
	public static function updatePage($pageNo)
	{
		if (!self::pageExists($pageNo))
		{
			self::_makePage($pageNo);
		}
		else self::_updatePage($pageNo);
	}


	/**
	 *
	 * @param $pageNo
	 */
	public static function pageExists($pageNo)
	{
		return file_exists(self::_getPageDataFileName($pageNo));
	}

	/**
	 * only takes into account tweets with image already processed
	 *
	 * will page number according to last tweet
	 *
	 * @return integer
	 */
	public static function getCurrentViewingPageNo()
	{
		// force loading tweet no
		$lastTweetWithImage = Twitter::getLastTweetWithImage();

		// page number
		return ceil($lastTweetWithImage['id'] / self::getPageSize());
	}


	/**
	 * only takes into account tweets with image already processed
	 * if a page is complete it will return the next page
	 *
	 * @return integer
	 */
	public static function getCurrentWorkingPageNo()
	{
		// force loading
		$lastTweetWithImage = Twitter::getLastTweetWithImage();

		$pageFloat = $lastTweetWithImage['id'] / self::getPageSize();

		// if is complete page returns +1 else returns current
		$pageNo = (ceil($pageFloat) == $pageFloat) ? $pageFloat + 1 : ceil($pageFloat);

		return $pageNo;
	}


	/**
	 * returns the page number of a certain tweet
	 *
	 * @return integer
	 */
	public static function getTweetPageNo($id)
	{
		// page number
		return ceil($id / self::getPageSize());
	}


	/**
	 * returns the index number of a certain tweet in it's page
	 *
	 * @return integer
	 */
	public static function getTweetIndexInPage($id)
	{
		$pageNo = self::getTweetPageNo($id);

		return $id - ($pageNo - 1) * self::getPageSize();
	}


	// ---- private

	/**
	 * @return string
	 */
	private static function _getPageConfigFileName()
	{
		return self::$_config['App']['path'] . '/' . self::$_config['Collage']['configFile'];
	}


	/**
	 * @param integer $pageNo
	 *
	 * @return string
	 */
	private static function _getPageDataFileName($pageNo)
	{
		return self::$_config['App']['path'] . '/' . self::$_config['Collage']['pageDir'] . '/page' . (int)$pageNo . '.php';
	}

	/**
	 *
	 * @param $pageNo
	 */
	private static function _makePage($pageNo)
	{
		$filename = self::_getPageDataFileName($pageNo);

		$tweets = Tweet::getByPage($pageNo - 1, self::getPageSize());

		$fileData = array();

		while ($tweet = $tweets->row())
		{
			$tweetNo = self::getTweetIndexInPage($tweet['id']);

			$fileData[$tweetNo] = $tweet;
		}

		file_put_contents($filename, json_encode($fileData));
	}

	/**
	 * @param $pageNumber
	 */
	private static function _updatePage($pageNo)
	{
		$filename = self::_getPageDataFileName($pageNo);

		$fileData = json_decode(file_get_contents($filename), TRUE);

		$last = end($fileData);

		$lastId = isset($last['id']) ? $last['id'] : null;

		$tweets = Tweet::getByPage($pageNo - 1, self::getPageSize(), $lastId);

		while ($tweet = $tweets->row())
		{
			$tweetNo = self::getTweetIndexInPage($tweet['id']);

			$fileData[$tweetNo] = $tweet;
		}

		file_put_contents($filename, json_encode($fileData));
	}




}

?>