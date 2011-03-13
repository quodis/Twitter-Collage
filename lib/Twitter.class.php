<?php
/**
 * @package    Firefox 4 Twitter Party
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
	const CACHE_KEY_RESET_FLAG = 'TWITTERPARTY::Twitter::resetFlag';

	/**
	 * @var array
	 */
	private static $_config = null;


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
	 * search
	 *
	 * @param string $terms
	 * @param integer $rpp
	 * @param string $lastId
	 *
	 * @return array
	 */
	public static function search($terms, $rpp, $lastId)
	{
		$data = array();

		$url = self::$_config['Twitter']['urlSearch'];

		$params = array('q' => $terms);

		// use lastId?
		// NOTE: this behaviour controlled by flag (using memcached)
		//   use reset-twitter-api script to trigger once
		//   will probably result in duplicated tweets being fetched (insert fails silently)
		if ($lastId && !Cache::get(self::CACHE_KEY_RESET_FLAG))
		{
			$params['since_id'] = $lastId;
		}
		else Cache::delete(self::CACHE_KEY_RESET_FLAG);

		$params['rpp'] = $rpp;

		$url = $url . '?' . http_build_query($params);

		$iteration = 0;

		do
		{
			$iteration ++;

			// override url with "page" url from previous response
			if (isset($response['next_page'])) $url = self::$_config['Twitter']['urlSearch'] . $response['next_page'];

			$response = self::_apiCall($url);

			// index results by id
			if (isset($response['results']) && is_array($response['results']))
			{
				foreach ($response['results'] as $row) $data[$row['id_str']] = $row;
			}
		}
		while (isset($response['next_page']) && ($iteration  < self::$_config['Twitter']['pageLimit']));

		// make sure results are sorted
		ksort($data);

		return $data;
	}

	/**
	 * sets a flag using memcache, lastId will be ignored in next search call
	 */
	public static function reset()
	{
		$value = 'wtf!?';
		Cache::set(self::CACHE_KEY_RESET_FLAG, $value, 1000000);
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

		$options = array(
			'timeout' => self::$_config['Twitter']['timeout']['apiCall']
		);

		return json_decode(Curl::get($url, $options), TRUE);
	}
}

?>