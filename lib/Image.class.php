<?php
/**
 * @package    TwitterCollage
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * File manipulation class
 */
class Image
{

	const FILE_ORIGINAL = 'o';
	const FILE_PUBLISH  = 'p';

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


	/**
	 * @param string $url
	 * @param string $id
	 */
	public static function download($url, $id)
	{
		$pathinfo = pathinfo($url);
		$sufix = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
		$cacheFile = self::fileName('original', md5($url), $sufix);

		$fileData = Curl::get($url, $cacheFile, self::$_config['App']['cacheDirPermissions'], self::$_config['App']['cacheFilePermissions']);

		return !!$fileData;
	}


	/**
	 * @param string $url
	 * @param string $id
	 */
	public static function makeTile($url, $id, $position)
	{
		$pathinfo = pathinfo($url);
		$sufix = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
		$cacheFile = self::fileName('original', md5($url), $sufix);

		$config = & Collage::getPageConfig();

		$index = $config['index'][$position];
		Debug::logMsg($id . ' -> ' . $index['x'] . ',' . $index['y']);
		$tile  = $config['grid'][$index['y']][$index['x']];
		$color = $tile['c'];
		$rgbColor = str_pad(dechex($color[0]), 2, '0', STR_PAD_LEFT);
		$rgbColor.= str_pad(dechex($color[1]), 2, '0', STR_PAD_LEFT);
		$rgbColor.= str_pad(dechex($color[2]), 2, '0', STR_PAD_LEFT);

		try
		{
			$image = new Imagick($cacheFile);
		}
		catch (Exception $e)
		{
			$default = self::$_config['App']['path'] .'/'. self::$_config['Collage']['defaultPic'];
			$image = new Imagick($default);
		}
		$image->modulateImage(100, 0, 100);
		$image->colorizeImage('#' . $rgbColor, 1.0);
		$image->thumbnailImage(self::$_config['Collage']['tileSize'], 0);
		$image->contrastImage(1);

		$fileId = str_pad($index['x'], 2, '0', STR_PAD_LEFT) . str_pad($index['y'], 2, '0', STR_PAD_LEFT) . $id;
		$destination = self::fileName('processed', $fileId, 'jpg');

		if (!is_dir(dirname($destination))) mkdir(dirname($destination), octdec(self::$_config['App']['cacheDirPermissions']), TRUE);

		// store
		$image->writeImage($destination);
		// set permissions
		chmod($destination, octdec(self::$_config['App']['cacheFilePermissions']));

		$rgbColorFile = self::fileName('processed', $fileId, $rgbColor . '.' .  'txt');
		file_put_contents($rgbColorFile, '');

		$encoded = base64_encode(file_get_contents($destination));

		Tweet::updateImage($id, $encoded);
	}


	// --- file path/url


	/**
	 * path on storage
	 *
	 * @param string $prefix
	 * @param string $id
	 * @param string sufix
	 *
	 * @return boolean
	 */
	public static function fileName($dir, $id, $sufix)
	{
		// make filename
		//$fileName = substr($id, 0, 2) . '/' . substr($id, 2, 2) . '/' . substr($id, 4) . '.' . $sufix;
		$fileName = $id . '.' . $sufix;

		return self::$_config['App']['pathCache'] . '/' . $dir . '/' . $fileName;
	}
}

?>