<?php
/**
 * @package    Firefox 4 Twitter Party
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
	const CACHE_KEY_LAST_TWEET = 'TWITTERPARTY::Mosaic::lastTweet::';
	const CACHE_KEY_LAST_TWEET_WITH_IMAGE = 'TWITTERPARTY::Mosaic::lastTweetWithImage::';


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
			$json = file_get_contents(self::_getConfigFileName());

			if (!$json) throw new Exception('could not load page config');

			self::$_pageConfig = json_decode($json, TRUE);
		}
		return self::$_pageConfig;
	}


	/**
	 * set (and index) page data
	 *
	 * @param Imagick $imageOriginal
	 * @param Imagick $imageReduced
	 */
	public static function setConfigFromImages($imageOriginal, $imageReduced)
	{
		// analyse original image
		$grid = array();
		$iterator = $imageOriginal->getPixelIterator();
		foreach($iterator as $rowIx => $rowPixels)
		{
			foreach ($rowPixels as $columnIx => $pixel)
			{
				$color = $pixel->getColor();

				if (implode($color) == '2552552551') continue;
				if (implode($color) == '2552552550') continue;

				$grid[$rowIx][$columnIx] = array(
					'c' => array($color['r'], $color['g'], $color['b'])
				);
			}
		}

		// analyse reduced color image
		$reducedColors = array();
		$iterator = $imageReduced->getPixelIterator();
		foreach($iterator as $rowIx => $rowPixels)
		{
			foreach ($rowPixels as $columnIx => $pixel)
			{
				$color = implode($pixel->getColor());

				if ($color == '2552552551') continue;
				if ($color == '2552552550') continue;

				if (!isset($reducedColors[$color])) $reducedColors[$color] = count($reducedColors);

				if (!isset($grid[$rowIx][$columnIx])) throw Exception();

				$grid[$rowIx][$columnIx]['r'] = $reducedColors[$color];
			}
		}

		// make config (grid + index)
		$index = 0;
		foreach ($grid as $rowIx => $rowPixels)
		{
			foreach ($rowPixels as $columnIx => $pos)
			{
				// store grid

				self::$_pageConfig['grid'][$columnIx][$rowIx] = array(
					'c' => $pos['c'],
					'r' => $pos['r'],
					'x' => $columnIx,
					'y' => $rowIx,
					'i' => $index,
				);

				// and index

				self::$_pageConfig['index'][$index] = array(
					'x' => $columnIx,
					'y' => $rowIx,
				);

				$index++;
			}
		}
	}


	/**
	 * saves php configuration
	 */
	public static function saveConfig()
	{
		$fileName = self::_getConfigFileName();

		file_put_contents($fileName, json_encode(self::$_pageConfig));
		chmod($fileName, octdec(self::$_config['Config']['filePermissions']));
		chgrp($fileName, self::$_config['Config']['group']);

		return $fileName;
	}

	/**
	 * saves js configuration
	 *
	 * stripped down
	 */
	public static function saveJsConfig()
	{
		$fileName = self::$_config['Store']['path'] . '/config/grid.js';

		$js = array(
			'grid' => array(),
			'index' => array()
		);

		// make config (grid + index)
		foreach (self::$_pageConfig['grid'] as $columnIx => $columns)
		{
			foreach ($columns as $rowIx => $pos)
			{
				// store grid
				$js['grid'][$columnIx][$rowIx] = array(
					'r' => $pos['r'],
					'i' => $pos['i'],
				);

				// and index
				$js['index'][$pos['i']] = array($columnIx, $rowIx);
			}
		}

		$contents = 'party.mosaic = ' . json_encode($js) . ';';

		file_put_contents($fileName, $contents);
		chmod($fileName, octdec(self::$_config['Store']['filePermissions']));
		chgrp($fileName, self::$_config['Store']['group']);

		return $fileName;
	}


	// ---- pages


	/**
	 * updates the most recent and complete page to a json file
	 *
	 * @return integer page number
	 */
	public static function updateMosaic()
	{
		$pageNo = Tweet::getLastCompletePage(self::getPageSize());
		$result = Tweet::getByPage($pageNo, 0, TRUE);

		$fileData = array(
			'tiles' => array(),
			'last_id' => null,
			'newest_tiles' => array(),
		);

		$i = 0;
		$lastId = 0;

		if (!$result->count()) return;

		$tiles = array();
		while ($tweet = $result->row())
		{
			if (isset($tiles[$tweet['p']])) continue;
			$tiles[$tweet['p']] = $tweet;
			if ($tweet['i'] > $lastId) $lastId = $tweet['i'];
		}

		if (count($tiles))
		{
			$fileData['tiles'] = $tiles;
			$fileData['last_id'] = $lastId;
		}

		// save jpeg file
		$fileName = self::getImageFileName();
		$image = Image::makeMosaic(self::$_config['Mosaic']['cols'], self::$_config['Mosaic']['rows'], self::$_pageConfig['index'], $tiles);
		$image->writeImage($fileName);
		chmod($fileName, octdec(self::$_config['Store']['filePermissions']));
		chgrp($fileName, self::$_config['Store']['group']);

		// save js file
		$fileName = self::getDataFileName();
		file_put_contents($fileName, json_encode($fileData));
		chmod($fileName, octdec(self::$_config['Store']['filePermissions']));
		chgrp($fileName, self::$_config['Store']['group']);

		return count($fileData['tiles']);
	}


	/**
	 * insert into the first free slot
	 *
	 * @return integer
	 */
	public static function getCurrentInsertingPageNo()
	{
		$page = Tweet::getFirstIncompletePage(self::getPageSize());

		if (!$page) $page = Tweet::getLastPage() + 1;

		if (!$page) $page = 1;

		// page number
		return $page;
	}


	/**
	 * based on processed pages
	 *
	 * @return integer
	 */
	public static function getLastCompletePage()
	{
		$page = Tweet::getLastCompletePage(self::getPageSize());

		return $page ? $page : 0;
	}


	/**
	 * @return array
	 */
	public static function getProcessedPages($ts)
	{
		// load
		$result = Tweet::getProcessedPages($ts);


		$pages = array();
		while ($row = $result->row())
		{
			$pages[] = $row['page'];
		}
		// page number
		return $pages;
	}


	// ---- tweets


	/**
	 * @param array & $row
	 */
	public static function addTweet(array & $row)
	{
		try
		{
			$tweet = Tweet::insert($row);

			self::setLastTweet($tweet);

			return $row;
		}
		catch (Exception $e)
		{
			if (strpos('Duplicate entry', $e->getMessage() !== FALSE))
			{
				Debug::logError($e, 'FAIL Mosaic::addTweet()');
			}
		}
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
			if ($row = Tweet::getLast(TRUE)) self::setLastTweetWithImage($row);
		}
		return self::$_lastTweetWithImage;
	}


	// ---- private


	/**
	 * @return string
	 */
	private static function _getConfigFileName()
	{
		return self::$_config['App']['path'] . '/' . self::$_config['Mosaic']['configFile'];
	}


	/**
	 * @return string
	 */
	public static function getDataFileName()
	{
		$filename = self::$_config['Store']['path'] . '/mosaic.json';

		if (!is_dir(dirname($filename)))
		{
			rmkdir(dirname($filename), self::$_config['Store']['dirPermissions'], self::$_config['Store']['group']);
		}

		return $filename;
	}



	/**
	 * @return string
	 */
	public static function getImageFileName()
	{
		$filename = self::$_config['Store']['path'] . '/mosaic.jpg';

		if (!is_dir(dirname($filename)))
		{
			rmkdir(dirname($filename), self::$_config['Store']['dirPermissions'], self::$_config['Store']['group']);
		}

		return $filename;
	}


	// ----

}

?>