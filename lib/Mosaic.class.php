<?php
/**
 * @pacjage    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */


/**
 * Mosaic
 */
class Mosaic
{

	/**
	 * @const string cache key
	 */
	const CACHE_KEY_LAST_TWEET = 'TWITTER-MOSAIC::lastTweet::';
	const CACHE_KEY_LAST_TWEET_WITH_IMAGE = 'TWITTER-MOSAIC::lastTweetWithImage::';


	/**
	 * @var array
	 */
	private static $_config = null;

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
	 * @var integer twitter API id
	 */
	private static $_lastTweet = null;
	/**
	 * @var integer twitt serial number
	 */
	private static $_lastTweetWithImage = null;

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
		// force load data
		if (!isset(self::$_pageConfig)) self::getPageConfig();

		return count(self::$_pageConfig['index']);
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

			if (!$json) throw new Exception('could not load page config');

			self::$_pageConfig = json_decode($json, TRUE);
		}
		return self::$_pageConfig;
	}


	/**
	 * set (and index) page data
	 *
	 * @param $grid array (by reference)
	 */
	public static function setPageGrid(array & $grid)
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

		$fileName = self::_getPageConfigFileName();

		file_put_contents($fileName, json_encode(self::$_pageConfig));
		chmod($fileName, octdec(self::$_config['Store']['filePermissions']));
		chgrp($fileName, self::$_config['Store']['group']);
	}


	// ---- pages


	/**
	 * updates this page file, returns number of tweets
	 *
	 * @param $pageNo
	 *
	 * @return $tweets;
	 */
	public static function updatePage($pageNo)
	{
		$tweets = Tweet::getByPageWithImage($pageNo, self::getPageSize());

		$i = 0;

		$fileData = array();
		while ($tweet = $tweets->row())
		{
			if (isset($fileData[$tweet['position']])) continue;
			$i++;
			$fileData[$tweet['position']] = $tweet;
		}

		if ($i < $tweets->count()) Debug::logError('#wtf#');

		$fileName = self::_getPageDataFileName($pageNo);

		file_put_contents($fileName, json_encode($fileData));
		chmod($fileName, octdec(self::$_config['Store']['filePermissions']));
		chgrp($fileName, self::$_config['Store']['group']);

		return count($fileData);
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
	 *
	 * @param $pageNo
	 *
	 * @return array;
	 */
	public static function getPageData($pageNo)
	{
		$filename = self::_getPageDataFileName($pageNo);

		if (file_exists($filename))
		{
			return json_decode(file_get_contents($filename), TRUE);
		}
		else return array();
	}


	/**
	 * only takes into account tweets with image already processed
	 *
	 * @return integer
	 */
	public static function getCurrentInsertingPageNo()
	{
		// force loading tweet no
		$lastTweet = self::getLastTweet();

		// page number
		return floor($lastTweet['id'] / self::getPageSize()) + 1;
	}


	/**
	 * based on processed pages
	 *
	 * @return integer
	 */
	public static function getCurrentWorkingPageNo()
	{
		// TODO CACHE this

		$pageNo = 0;

		do
		{
			$pageNo++;

			$fileName = self::_getPageDataFileName($pageNo);

			if (!file_exists($fileName)) break;

			$fileData = self::getPageData($pageNo);

			if (count($fileData) < self::getPageSize()) break;
		}
		while (TRUE);

		return $pageNo;
	}


	/**
	 * returns the page number of a certain tweet
	 *
	 * @return integer
	 */
	/*
	public static function getTweetPageNo($id)
	{
		// page number
		return ceil($id / self::getPageSize());
	}
	*/


	// ---- tweets


	/**
	 * @param array & $row
	 */
	public static function addTweet(array & $row)
	{
		$tweet = Tweet::insert($row);

		self::setLastTweet($tweet);
	}


	/**
	 * @param integer $id
	 * @param string $imageData (by reference)
	 */
	public static function updateTweetImage($id, & $imageData)
	{
		Tweet::updateImage($id, $imageData);

		Cache::delete(self::CACHE_KEY_LAST_TWEET_WITH_IMAGE);
	}

	/**
	 * @param array $lastTweet (by reference)
	 */
	public static function setLastTweet(array & $lastTweet)
	{
		self::$_lastTweet = $lastTweet;

		Cache::set(self::CACHE_KEY_LAST_TWEET, $lastTweet, self::$_config['Cache']['TTL']['tweetIds']);
	}


	/**
	 * @param array $lastTweet (by reference)
	 */
	public static function setLastTweetWithImage(array & $lastTweetWithImage)
	{
		self::$_lastTweetWithImage = $lastTweetWithImage;

		Cache::set(self::CACHE_KEY_LAST_TWEET_WITH_IMAGE, $lastTweetWithImage, self::$_config['Cache']['TTL']['tweetIds']);
	}


	/**
	 * last captured tweet (twitter id)
	 *
	 * @return integer
	 */
	public static function getLastTweet()
	{
		// already loaded
		if (!isset(self::$_lastTweet))
		{
			self::$_lastTweet = Cache::Get(self::CACHE_KEY_LAST_TWEET);

			// load from db
			if (!self::$_lastTweet)
			{
				if ($row = Tweet::getLast()) self::setLastTweet($row);
			}
		}
		return self::$_lastTweet;
	}


	/**
	 * last captured
	 *
	 * @return integer
	 */
	public static function getLastTweetWithImage()
	{
		// already loaded
		if (!isset(self::$_lastTweetWithImage))
		{
			// load from db
			if ($row = Tweet::getLastWithImage()) self::setLastTweetWithImage($row);
		}
		return self::$_lastTweetWithImage;
	}


	// ---- private


	/**
	 * @return string
	 */
	private static function _getPageConfigFileName()
	{
		return self::$_config['App']['path'] . '/' . self::$_config['Mosaic']['configFile'];
	}


	/**
	 * @param integer $pageNo
	 *
	 * @return string
	 */
	private static function _getPageDataFileName($pageNo)
	{
		$filename = self::$_config['App']['pathStore'] . '/pages/page' . $pageNo . '.php';

		if (!is_dir(dirname($filename)))
		{
			rmkdir(dirname($filename), self::$_config['Store']['dirPermissions'], self::$_config['Store']['group']);
		}

		return $filename;
	}


	// ----

}

?>