<?php
/**
 * @package    TwitterCollage
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */


/**
 * calls web-services operations
 */
class Curl
{

	/**
	 * static class, nothing to see here, move along
	 */
	private function __constructor() {}


	// ---- private


	/**
	 * post data to this url
	 *
	 * @param string $url
	 * @param array $postData
	 * @param boolean $sendParamsAsString (optional)
	 *
	 * @return string
	 */
	public static function post($url, array $postData, $sendParamsAsString = TRUE)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$postData = ($sendParamsAsString) ? http_build_query($postData) : $postData;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Expect: '));

		$response = curl_exec($ch);

		curl_close($ch);

		return $response;
	}



	/**
	 * request this url
	 *
	 * @param string $url
	 * @param array $options (optional)
	 *
	 * @return string
	 */
	public static function get($url, array $options = null)
	{

		/*
		'timeout' => self::$_config['Twitter']['timeout']['imgFile'],
			'cache' => array(
				'file' => $cacheFile,
				'dirPermissions' => self::$_config['App']['cacheDirPermissions'],
				'filePermissions' => self::$_config['App']['cacheFilePermissions'],
				'group' => self::$_config['App']['cacheGroup']

		*/


		if (isset($options['cache']) && file_exists($options['cache']['file']) && filesize($options['cache']['file']))
		{
			$doc = file_get_contents($options['cache']['file']);

			Debug::logMsg('CURL (cached): ' . $url . ' /' . $options['cache']['file'] . ' (' . strlen($doc) .')');
			return $doc;
		}
		else
		{
			$ch = curl_init();

			if (!isset($options['timeout'])) $options['timeout'] = 10;

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['timeout']);
			curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 2);

			$response = curl_exec($ch);

			curl_close($ch);

			Debug::logMsg('CURL: ' . $url . ' (' . strlen($response) .')');

			if (!strlen($response)) return '';

			if (isset($options['cache']))
			{
				$cacheFile = $options['cache']['file'];

				$dirName = dirname($cacheFile);

				if (!is_dir($dirName))
				{
					rmkdir($dirName, $options['cache']['dirPermissions'], $options['cache']['group']);
				}
				file_put_contents($cacheFile, $response);
				if (isset($options['cache']['filePermissions'])) chmod($cacheFile, octdec($options['cache']['filePermissions']));
				if (isset($options['cache']['group'])) chgrp($cacheFile, $options['cache']['group']);
			}
		}

		return $response;
	}
}

?>