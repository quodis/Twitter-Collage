<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage front-end
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>, Leo Xavier <leo@quodis.com>, Leihla Pinho <leihla@quodis.com>, Luis Abreu <luis@quodis.com>, Bruno Abrantes <bruno@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * escape from global scope
 */
function main()
{
	DEFINE('NO_DB', TRUE);
	DEFINE('CLIENT', 'html');
	DEFINE('CONTEXT', __FILE__);
	include '../bootstrap.php';

	Debug::setLogMsgFile($config['App']['pathLog'] .'/www.msg.log');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/www.error.log');

	// requested locale
	$locale = isset($_GET['locale']) ? $_GET['locale'] : null;
	if (!$locale)
	{
		$locale = Locale::choose();
		Locale::redirect($locale);
	}

	// cache
	$cacheKey = 'TWITTER-PARTY::index::locale=' . $locale;
	$cacheTTL = $config['Cache']['TTL']['index'];

	ob_start();
	header("Expires: " . gmdate("D, d M Y H:i:s", time() + $cacheTTL) . " GMT");
	header("Cache-Control: max-age=$cacheTTL, s-maxage=$cacheTTL, public, must-revalidate");
	ini_set('zlib.output_compression', 1);

	// check cache
	if (FALSE || $output = Cache::get($cacheKey))
	{
		Dispatch::now(1, $output);
	}

	// not in cache, we must validate locale
	$locale = Locale::setUp($locale);

	// reset cache key to actual locale used
	$cacheKey = 'TWITTER-PARTY::index::locale=' . $locale;

	initDb($config);

	// mosaic config file
	$jsMosaicConfig = $config['Store']['url'] . $config['UI']['js-config']['grid'];

	// js config
	$uiOptions = $config['UI']['options'];

?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">

	<head>

		<title><?= /* L10n: Browser title */ _('Firefox 4 Twitter Party') ?></title>

		<meta charset="utf-8" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="keywords" content="<?= /* L10n: Meta tag keywords */ _('Mozilla, Firefox, Firefox 4, Party, Twitter, Tweet') ?>" />
		<meta name="description" content="<?= /* L10n: Meta tag description */ _('Firefox 4 Twitter Party is an interactive visualization of Firefox 4 activity on Twitter.') ?>" />
		<meta name="author" content="Quodis, Mozilla" />
		<meta name="copyright" content="© 2011" />
		<meta name="distribution" content="global" />

		<!-- stylesheets -->
<?php if ($config['UI']['minified']) { ?>
		<link rel="stylesheet" href="<?=$config['UI']['css']['minified']?>" type="text/css" media="screen, projection" />
<?php } else { ?>
		<link rel="stylesheet" href="<?=$config['UI']['css']['main']?>" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="<?=$config['UI']['css']['mosaic']?>" type="text/css" media="screen, projection" />
<?php } ?>

		<link rel="shortcut icon" href="/favicon.ico" />
		<link rel="apple-touch-icon" type="image/png" href="assets/images/global/apple-touch-icon-precomposed.png">
		<link rel="image_src" href="assets/images/global/ftp-facebook-thumb.png">

		<!-- scripts -->
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<?php if ($config['UI']['minified']) { ?>
		<script type="text/javascript" src="<?=$config['UI']['js']['minified']?>"></script>
<?php } else { ?>
		<script type="text/javascript" src="/assets/js/global.js?>"></script>
		<script type="text/javascript" src="<?=$config['UI']['js']['general']?>"></script>
<?php } ?>
		<script type="text/javascript" src="<?=$jsMosaicConfig?>"></script>
		<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

		<style  type="text/css">
			<?= /* L10n: Replace this with your CSS rules to tweak the site in your locale */ _('placeholder{}') ?>
		</style>

	</head>

	<body>

		<div id="container" class="clearfix">

			<div class="wrapper clearfix">

				<!-- HEADER -->
				<header id="brand" role="banner">
					<a href="/" title="<?= _('Start over') ?>">
						<?= /* L10n: Logo translation. Feel free to change the order of the h1, h2 and p blocks. h1 max of 9 characters. h2 max of 13 characters. em max of 18 characters. */ _('<p><em>Join the</em></p><h1>Firefox 4</h1><h2>Twitter Party</h2>') ?>
					</a>
				</header>

				<!-- CONTENT -->
				<aside id="main-content" class="clearfix">

					<!-- Here goes the text explaining how Firefox Twitter Party works. -->
					<p><?= sprintf(_('Be part of Team Firefox! Tweet about Firefox 4 with the %s hashtag and your avatar will join thousands of others from around the world as part of our logo mosaic.'), '<span class="hashtag">#fx4</span>') ?></p>

					<div id="twitter-counter" class="clearfix">
						<dl>
							<dt><a href="http://twitter.com/share?url=http://mzl.la/hDx6aM&amp;via=firefox&amp;related=firefox&amp;text=<?= urlencode( /* L10n: Default text to tweet, max 89 characters */ _('Join me at the Firefox 4 Twitter Party and celebrate the newest version') . ' #fx4 #teamfirefox') ?>" title="<?= /* L10n: Action verb, will open the new tweet window */ _('Tweet') ?>" rel="external"><?= _('Tweet') ?></a></dt>
							<dd><span></span></dd>
						</dl>
					</div><!-- twitter-counter -->


					<form id="search-box" role="search">
						<h3><?= _("Who's at the party?") ?></h3>
						<label for="search-input" accesskey="f"><?= /* L10n: This is an invisible label field for the input */ _('Find a Twitter username') ?></label>
						<input type="text" name="search-input" id="search-input" value="<?= /* L10n: This is an input placeholder. Users can only find exact twitter usernames */ _('Find a Twitter username') ?>" tabindex="1" disabled="disabled" class="disabled" />
						<button type="submit" name="search-button" id="search-submit-bttn" value="<?= _('Find') ?>" tabindex="2" title="<?= _('Find') ?>" disabled="disabled" class="button disabled"><?= _('Find') ?></button>
						<div class="error">
							<p><?= _("This user hasn't joined the party yet.") ?></p>
						</div>

					</form><!-- search-box -->

					<div id="download">
						<a class="download-link download-firefox" href="http://www.mozilla.com/"><span class="download-content"><span class="download-title">Firefox 4</span><?= /* L10n: Max of 15 characters */ _('Download here') ?></span></a>
					</div><!-- download -->

				</aside><!-- main-content -->


				<section id="mosaic" role="img">
					<h2><?= _('Mosaic') ?></h2>

					<ul id="loading">
						<li><?= /* L10n: Funny loading message */ _('Sorting guest list alphabetically') ?></li>
						<li><?= /* L10n: Funny loading message */ _('Randomizing seating order') ?></li>
						<li><?= /* L10n: Funny loading message */ _('Cooling drinks to optimal temperature') ?></li>
						<li><?= /* L10n: Funny loading message */ _('Handing out name tags') ?></li>
						<li><?= /* L10n: Funny loading message */ _('Waxing the dance floor') ?></li>
						<li><?= /* L10n: Funny loading message */ _('Setting up Firefox deco') ?></li>
					</ul>

					<img  src="data:image/gif;base64,R0lGODlhAQABAPAAAP8A/wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" id="tile-hover" />

					<article id="bubble" class="bubble">

						<header>

							<?php $link = '<a href="#" title="' . _('Twitter profile') . '" rel="author external" target="_blank"></a>'; ?>
							<h1><?= sprintf(/* L10n: Used in: "twitter username" wrote */ _('%s <span>wrote</span>'), $link) ?></h1>
							<a href="#" title="" rel="author external" class="twitter-avatar" target="_blank">
								<img  src="data:image/gif;base64,R0lGODlhAQABAPAAAP8A/wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" alt="<?= _('Twitter profile picture') ?>" width="48" height="48" />
							</a>
							<time datetime="<?=date('Y-m-d')?>" pubdate><a href="#" rel="bookmark external" title="<?= _('Permalink') ?>" target="_blank"></a></time>

						</header>

						<p></p>

					</article><!-- bubble template -->

				</section><!-- mosaic -->

			</div><!-- wrapper -->

			<div id="mozilla-badge">
				<a href="http://www.mozilla.org/" class="mozilla" title="<?= _('Visit Mozilla') ?>" rel="external"><?= _('Visit Mozilla') ?></a>
			</div><!-- mozilla-badge -->

		</div><!-- container -->

		<!-- FOOTER -->
		<footer>

			<div id="sub-footer" role="contentinfo" class="clearfix">
				<h3><span><?= /* L10n: Keep the <em> at beginning or end only. Max 20 characters before the em, Max 10 characters for the em. */ _("Let's be <em>Friends!</em>") ?></span></h3>

				<ul>
					<li id="footer-twitter"><a href="http://twitter.com/firefox" rel="external"><?= _('Twitter') ?></a></li>
					<li id="footer-facebook"><a href="http://Facebook.com/Firefox" rel="external"><?= _('Facebook') ?></a></li>
					<li id="footer-connect"><a href="http://www.mozilla.com/en-US/firefox/connect/" rel="external"><?= /* L10n: Max of 20 characters */ _('More Ways to Connect') ?></a></li>
				</ul>

				<p id="sub-footer-newsletter">
					<span class="intro"><?= /* L10n: Max of 30 characters */ _('Want us to keep in touch?') ?></span>
					<a href="http://www.mozilla.com/newsletter/"><?= /* L10n: Max of 25 characters */ _('Get Monthly News') ?> <span>»</span></a>
				</p>

			</div><!-- sub-footer -->

			<div id="footer-copyright">

				<div id="footer-left">
					<p id="footer-links">
						<a href="http://www.mozilla.com/privacy-policy.html"><?= _('Privacy Policy') ?></a> &nbsp;|&nbsp;
						<a href="http://www.mozilla.com/about/legal.html"><?= _('Legal Notices') ?></a> &nbsp;|&nbsp;
						<a href="http://www.mozilla.com/legal/fraud-report/index.html"><?= _('Report Trademark Abuse') ?></a>
					</p>

					<p><?= sprintf(/* L10n: Leave all html code unchanged */ _('Except where otherwise <a href="%s">noted</a>, content on this site is licensed under the <br /><a href="%s" rel="external">Creative Commons Attribution Share-Alike License v3.0</a> or any later version.'), 'http://www.mozilla.com/about/legal.html#site', 'http://creativecommons.org/licenses/by-sa/3.0/') ?></p>

					<p><?= sprintf( /* L10n: The variable will hold a linked name to the web agency */ _('Visualization by %s'), '<a href="http://quodis.com" title="Quodis" rel="external">Quodis</a>') ?></p>

				</div><!-- footer-left -->

				<div id="footer-right">

					<form id="lang_form" dir="ltr" method="get">

						<label for="flang"><?= _('Other Languages') ?></label>

						<select id="flang" name="flang">
							<?=Locale::langOptions($locale); ?>
						</select>

					</form>

				</div><!-- footer-right -->

			</div><!-- footer-copyright -->

		</footer>

		<script type="text/javascript">
		//<![CDATA[
		(function($) {
			$.extend(party, <?=json_encode($uiOptions)?>);
			$.extend(party, {l10n: {
				date_format:'<?= /* L10n: Date format. Documentation: http://php.net/manual/en/function.date.php */ _('M j Y, g:i A') ?>',
				dec_point:'<?= /* L10n: Decimal separator for numbers (dec_point). */ _('.') ?>',
				thousands_sep:'<?= /* L10n: Thousands separator for numbers (thousands_sep) */ _(',') ?>'
			} } );
		} )(jQuery);
		//]]>
		</script>

	</body>

</html>
<?php

	$output = ob_get_contents();
	Cache::set($cacheKey, $output, $cacheTTL);
	header('Content-type: text/html');
	exit();

} // main()

try
{
	main();
}
catch(Exception $e) {
	Debug::logError($e, 'EXCEPTION ' . $e->getMessage());
	Dispatch::now(0, 'EXCEPTION ' . $e->getMessage());
}

?>
