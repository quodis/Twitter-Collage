<?php
/**
 * @package    TwitterCollage
 * @subpackage server
 * @version    v.0.8
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Import
{
	/**
	 * configuration
	 *
	 * @var array
	 */
	private static $_config;
	/**
	 * tab structure
	 *
	 * @var array
	 */
	private static $_tabs = array();
	/**
	 * sticker index
	 *
	 * @var array
	 */
	private static $_stickers = array();


	/**
	 * @param array $config (by reference)
	 */
	public static function configure(array & $config)
	{
		self::$_config = $config;
	}

	/**
	 * reads config file and stores tabs/stickers raw info
	 */
	public static function readConfig()
	{
		$lines = file(self::$_config['Import']['pathRaw'] . '/_stickers.txt');

		$type = null;

		foreach ($lines as $ix => $line)
		{
			$line = trim($line);

			if (empty($line)) continue;

			if ("#tabs" == $line)
			{
				$type = 'tab';
				continue;
			}
			if ("#stickers" == $line)
			{
				$type = 'sticker';
				continue;
			}

			if ($line[0] === "#") continue;

			switch ($type)
			{
				case 'tab':
					self::_readConfigTab($line);
					break;
				case 'sticker':
					self::_readConfigSticker($line);
			}
		}
	}

	/**
	 * creates pages for tabs
	 */
	public static function preparePages()
	{
		foreach (self::$_tabs as $tabId => $tab)
		{
			$page = 0;
			$index = 0;
			foreach ($tab['stickers'] as $sticker)
			{

				if ($index >= self::$_config['Import']['pageSize'])
				{
					$page++;
					$index = 0;
				}

				self::$_tabs[$tabId]['pages'][$page][$index] = array(
                    'name' => $sticker['name'],
                    'id'   => $sticker['id'],
				);

				$index++;
			}
		}
	}


	/**
	 * creates javascript data file with tabs and paths
	 */
	public static function makeScript()
	{
		$contents = '/**
 * FF4
 * by Quodis © 2011
 * http://www.quodis.com
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 *
 * FF4 data file
 * generated: ' . date('Y-m-d H:i:s') . '
 */

/** FF4 Tabs */

' . self::_makeTabsScript() . '

/** FF4 Stickers */

' . self::_makeStickersScript() . '

';

		// save
		file_put_contents(self::$_config['Import']['dataFile'], $contents);
	}


	/**
	 * creates css file with sticker sprites and sprite offsets
	 */
	public static function makeStyle()
	{
		$contents = '/**
 * FF4
 * by Quodis © 2011
 * http://www.quodis.com
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 *
 * FF4 data file
 * generated: ' . date('Y-m-d H:i:s') . '
 */

/** FF4 Pages */

' . self::_makeTabsStyle() . '

/** FF4 Stickers */

' . self::_makeStickersStyle() . '

';

		// save
		file_put_contents(self::$_config['Import']['cssFile'], $contents);
	}



	/**
	 * creates the image sprites
	 */
	public static function makeSprites()
	{
		$offset = self::$_config['Import']['spriteOffset'];

		$tabIx = 0;
		foreach (self::$_tabs as $tabId => $tab)
		{

			if (isset($tab['pages']))
			{
				foreach ($tab['pages'] as $pageIx => $page)
				{
					$offset = self::$_config['Import']['spriteOffset'];

					// Make a transparent canvas.
					$target = new Imagick();
					$target->newImage($offset, $offset * count($page), 'none');

					foreach ($page as $stickerIx => $sticker)
					{
						// fetch sticker info
						$sticker = self::$_stickers[$sticker['id']];

						$imageFileName = self::$_config['Import']['pathRaw'] . '/' . $sticker['pngFile'];
						$image = new Imagick($imageFileName);

						$target->compositeImage($image, imagick::COMPOSITE_COPY, 0, $offset * $stickerIx++);
					}

					$spriteFileName = self::$_config['Import']['spritePath'] . '/sprite-page-' . $tabIx . '-' . $pageIx . '.png';

					$target->writeImage($spriteFileName);
				}
			}

			$tabIx++;
		}
	}


	// ---- private


	/**
	 * add a tab from config line
	 *
	 * @param string $tab
	 */
	private static function _readConfigTab($tab)
	{
		Debug::logMsg($tab, 'TAB ');

		if (preg_match('/([a-z]+) (.*)/', $tab, $matches))
		{
			self::$_tabs[$matches[1]] = array(
                'name'     => $matches[2],
                'ix'       => 0,
                'stickers' => array()
			);
		}
	}


	/**
	 * add a sticker from config file line
	 *
	 * @param string $sticker
	 */
	private static function _readConfigSticker($sticker)
	{
		Debug::logMsg($sticker, 'STICKER ');

		if (preg_match('/(([a-z]+)_([0-9]+))( ([0-9\.]+))?( .*)?/', $sticker, $matches))
		{
			$fileId = $matches[1];
			$tabId  = $matches[2];
			$scale  = isset($matches[5]) ? trim($matches[5]) : 1;
			$name   = isset($matches[6]) ? trim($matches[6]) : null;

			if (!isset(self::$_tabs[$tabId]))
			{
				Debug::logError('invalid tabId: "' . $tabId . '" in sticker: "' . $sticker . '"');
				continue;
			}

			// published with id
			$id = $tabId . '_' . self::$_tabs[$tabId]['ix']++;

			// default name "Eyes 3"
			if (!$name) $name = self::$_tabs[$tabId]['name'] . ' ' . self::$_tabs[$tabId]['ix'];

			// sticker structure
			$sticker = array(
                'id'      => $id,
                'scale'   => $scale,
                'svgFile' => $fileId . '.svg',
                'pngFile' => $fileId . '.png',
                'tabId'   => $tabId,
                'name'    => $name
			);

			// store and index
			self::$_tabs[$tabId]['stickers'][$id] = $sticker;
			self::$_stickers[$id] = self::$_tabs[$tabId]['stickers'][$id];
		}
	}


	/**
	 * creates tabs part of data file
	 *
	 * @return string
	 */
	public static function _makeTabsScript()
	{

		$tabs = array();

		foreach (self::$_tabs as $tabId => $tab)
		{
			if (isset($tab['pages']))
			{

				$tabs[] = "
tabs.push( {
    id    : '" . $tabId . "',
    name  : '" . stripslashes($tab['name']) . "',
    pages : " . json_encode($tab['pages']) . "
} );
";
			}
		}

		$contents  = "if ('undefined' == typeof tabs) tabs = [];" . NL;
		$contents .= implode(NL, $tabs) . NL;

		return $contents;
	}


	/**
	 * creates sticker paths part of data file
	 *
	 * @return string
	 */
	public static function _makeStickersScript()
	{

		$paths = array();

		foreach (self::$_stickers as $sticker)
		{
			$svgFile = self::$_config['Import']['pathRaw'] . '/' . $sticker['svgFile'];

			$svgRaw = file($svgFile);

			$keep = 0;
			$svgData = '';
			foreach ($svgRaw as $svgLine) {
				$isPath = preg_match('/^\s*<path/', $svgLine);
				// keep from first path on
				if (!$keep) $keep = $isPath;
				// parse
				if ($keep)
				{
					// make black
					if ($isPath && !strpos($svgLine, 'fill="')) $svgLine = str_replace('<path ', '<path fill="#000000" ', $svgLine);
					// line endings
					$svgLine = preg_replace('/\s+$/', '\\' . NL, $svgLine);

					$svgData .= $svgLine;
				}
			}

			$paths[] = "
assets.push( {
    id    : '" . $sticker['id'] . "',
    scale : " . (float)$sticker['scale'] . ",
    name  : '" . htmlentities($sticker['name'], ENT_QUOTES) . "',
    svg   : '<svg width=\"50\" height =\"50\">\
" . $svgData . "'
} );
";
		}

		$contents  = "if ('undefined' == typeof assets) assets = [];" . NL;
		$contents .= implode(NL, $paths) . NL;

		return $contents;
	}


	/**
	 * creates tabs (one sprite per page) part of css file
	 *
	 * @return string
	 */
	public static function _makeTabsStyle()
	{
		$pages = array();

		$tabIx = 0;
		foreach (self::$_tabs as $tabId => $tab)
		{
			if (isset($tab['pages'])) foreach ($tab['pages'] as $pageIx => $foo)
			{
				$id = 'page-' . $tabIx . '-' . $pageIx;

				$css = '.'  . $id . '.gallery-image { background-image: url(sprite-' . $id . '.png); }';

				$pages[] = $css;
			}
			$tabIx++;
		}

		return implode(NL, $pages) . NL;
	}


	/**
	 * creates stickers (sprite offsets) part of css file
	 *
	 * @return string
	 */
	public static function _makeStickersStyle()
	{
		$stickers = array();

		foreach (self::$_tabs as $tabId => $tab)
		{
			$offset = self::$_config['Import']['spriteOffset'];

			if (isset($tab['pages'])) foreach ($tab['pages'] as $page)
			{
				foreach ($page as $ix => $sticker)
				{
					$css = '#' . $sticker['id'] . ' { background-position: 0px -' . ($offset * $ix) . 'px; }';

					$stickers[] = $css;
				}
			}
		}

		return implode(NL, $stickers) . NL;
	}


}

?>