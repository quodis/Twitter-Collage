<?php
/**
 * @package    TwitterCollage
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */


/**
 * Twitter
 */
class Twitter
{

	/**
	 * @const string cache key
	 */
	const CACHE_KEY_LAST_TWEET = 'TWITTER-COLLAGE::lastTweet::';
	const CACHE_KEY_LAST_TWEET_WITH_IMAGE = 'TWITTER-COLLAGE::lastTweetWithImage::';

	/**
	 * @var array
	 */
	private static $_config = null;

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


	// ---- api


	/**
	 * post data to this url
	 *
	 * @param string $terms
	 * @param string $lastId
	 *
	 * @return array
	 */
	public static function search($terms, $lastId)
	{

		$data = array();

		$url = self::$_config['Twitter']['urlSearch'];

		$params = array('q' => self::$_config['Collage']['terms']);

		$lastId = (int)$lastId;
		$params ['since_id'] = $lastId;

		$url = $url . '?' . http_build_query($params);

		do
		{
			// override url with "page" url from previous response
			if (isset($response['next_page'])) $url = self::$_config['Twitter']['urlSearch'] . $response['next_page'];

			$response = self::_apiCall($url);

			// index results by id
			if (isset($response['results']) && is_array($response['results']))
			{
				foreach ($response['results'] as $row) $data[$row['id_str']] = $row;
			}
		}
		while (isset($response['next_page']));

		// make sure results are sorted
		ksort($data);

		return $data;
	}


	// ---- tweet data set


	/**
	 * @param array & $row
	 */
	public static function addTweet(array & $row)
	{
		Tweet::insert($row);

		Cache::delete(self::CACHE_KEY_LAST_TWEET);
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


	// ---- tweet data get


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
			// load from cache;
			if (!(self::$_lastTweet = Cache::get(self::CACHE_KEY_LAST_TWEET)))
			{
				// load from db
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
			// load from cache;
			if (!(self::$_lastTweetWithImage = Cache::get(self::CACHE_KEY_LAST_TWEET_WITH_IMAGE)))
			{
				// load from db
				if ($row = Tweet::getLastWithImage()) self::setLastTweetWithImage($row);
			}
		}
		return self::$_lastTweetWithImage;
	}


	// ---- private


	/**
	 * request this url
	 *
	 * @param string $url
	 * @param array $getData
	 *
	 * @return array
	 */
	private static function _apiCall($url)
	{
		Debug::logMsg($url);

		return json_decode(Curl::get($url), TRUE);
	}
}

?>