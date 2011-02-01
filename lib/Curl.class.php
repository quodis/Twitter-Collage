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
	 * @param string $cacheFile (optional)
	 *
	 * @return string
	 */
	public static function get($url, $cacheFile = null, $cacheDirPermissions = null, $cacheFilePermissions = null)
	{
		if (file_exists($cacheFile) && filesize($cacheFile))
		{
			$doc = file_get_contents($cacheFile);

			Debug::logMsg('CURL (cached): ' . $url . ' /' . $cacheFile . ' (' . strlen($doc) .')');
			return $doc;
		}
		else
		{
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

			$response = curl_exec($ch);

			curl_close($ch);

			Debug::logMsg('CURL: ' . $url . ' (' . strlen($response) .')');

			if (!strlen($response)) return '';

			if ($cacheFile)
			{
				if (!is_dir(dirname($cacheFile))) @mkdir(dirname($cacheFile), octdec($cacheDirPermissions), TRUE);
				file_put_contents($cacheFile, $response);
				chmod($cacheFile, octdec($cacheFilePermissions));
			}
		}

		return $response;
	}
}

?>