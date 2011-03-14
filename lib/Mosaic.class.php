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
		$fileData = array(
			'tiles' => array(),
			'last_id' => null,
			'total_tiles' => Tweet::getCount(TRUE),
			'newest_tiles' => array(),
		);

		$pageSize = Mosaic::getPageSize();
		// all slots
		for ($i = 0; $i < $pageSize; $i++) $freeSlots[$i] = $i;

		// get latest mosaic (if possible)
		$result = Tweet::getLatestMosaic($pageSize);

		if (!$result->count()) return;

		$tiles = array();
		while ($tweet = $result->row())
		{
			if (isset($tiles[$tweet['p']])) continue;
			// remove slot
			unset($freeSlots[$tweet['p']]);
			$tiles[$tweet['p']] = $tweet;
		}

		// some slots missing
		if (count($freeSlots))
		{
			Debug::logMsg('some slots empty: ' . count($freeSlots));
			// get
			$result = Tweet::getFallbackTiles($freeSlots);
			while ($tweet = $result->row())
			{
				if (isset($tiles[$tweet['p']])) continue;
				// remove slot
				unset($freeSlots[$tweet['p']]);
				$tiles[$tweet['p']] = $tweet;
			}
		}

		// still missing
		if (count($freeSlots))
		{
			Debug::logMsg('still some slots empty:' . count($freeSlots) . ' (give up)');
			return;
		}

		if (count($tiles))
		{
			$fileData['tiles'] = $tiles;
			// find max id
			foreach ($tiles as $tile) if ($tile['i'] > $fileData['last_id']) $fileData['last_id'] = $tile['i'];
		}

		// mosaic.json contents (jsonp)
		$contents = 'party.processMosaic(' . json_encode($fileData) . ');';

		// save jpeg file
		$fileName = self::getImageFileName();
		$image = Image::makeMosaic(self::$_config['Mosaic']['cols'], self::$_config['Mosaic']['rows'], self::$_pageConfig['index'], $tiles);
		if ($image->writeImage($fileName))
		{
			if (isset(self::$_config['Store']['filePermissions'])) chmod($fileName, octdec(self::$_config['Store']['filePermissions']));
			if (isset(self::$_config['Store']['group'])) chgrp($fileName, self::$_config['Store']['group']);

			// save js file
			$fileName = self::getDataFileName();
			file_put_contents($fileName, $contents);
			if (isset(self::$_config['Store']['filePermissions'])) chmod($fileName, octdec(self::$_config['Store']['filePermissions']));
			if (isset(self::$_config['Store']['group'])) chgrp($fileName, self::$_config['Store']['group']);
		}

		return count($fileData['tiles']);
	}


	/**
	 * try to keep one page complete
	 * only insert into current page if the previous one has no free slots
	 *
	 * @param boolean $unprocessed (optional, defaults to FALSE)
	 *
	 * @return integer
	 */
	public static function getPriorityPageNo($unprocessed = FALSE)
	{
		$result = Tweet::getLastCoupleOfPages($unprocessed);

		$pageSize = self::getPageSize();

		$pages = array();
		while ($pages[] = $result->row());

		// the previous set is still incomplete
		if (isset($pages[1]) && $pages[1]['cnt'] < $pageSize)
		{
			return $pages[1]['page'];
		}
		// the current set is not complete?
		if (isset($pages[0]) && $pages[0]['cnt'] < $pageSize)
		{
			return $pages[0]['page'];
		}
		// advance to new page
		if (isset($pages[0]))
		{
			return $pages[0]['page'] + 1;
		}
		// is first page
		else return 1;
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
	}



	/**
	 * @param array $lastTweet (by reference)
	 */
	public static function setLastTweetWithImage(array & $lastTweetWithImage)
	{
	}


	/**
	 * last captured tweet (twitter id)
	 *
	 * @return integer
	 */
	public static function getLastTweet()
	{
		// load from db
		if (!self::$_lastTweet)
		{
			if ($row = Tweet::getLast()) self::$_lastTweet = $row;
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
			if ($row = Tweet::getLast(TRUE)) self::$_lastTweetWithImage = $row;
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