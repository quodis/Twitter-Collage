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
	 * post data to this url
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

		$params['since_id'] = $lastId;
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