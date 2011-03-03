<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage front-end
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>, Leo Xavier <leo@quodis.com>, Leihla Pinho <leihla@quodis.com>, Luis Abreu <luis@quodis.com>, Bruno Abrantes <bruno@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * load localization lib - provides: $locale, $request_uri, $accept_language
 *
 */
include('../lib/localization.php');

/**
 * escape from global scope
 */
function main($language)
{
	header("ETag: PUB" . time());
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()-10) . " GMT");
	header("Expires: " . gmdate("D, d M Y H:i:s", time() + 5) . " GMT");
	header("Pragma: no-cache");
	header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
	header('P3P: CP="CAO PSA OUR"');

	DEFINE('CLIENT', 'html');
	DEFINE('CONTEXT', __FILE__);
	include '../bootstrap.php';
	session_cache_limiter("nocache");

	Debug::setLogMsgFile($config['App']['pathLog'] .'/dashboard.msg.log');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/dashboard.error.log');

	// mosaic config file
	$jsMosaicConfig = $config['Store']['url'] . $config['UI']['js-config']['grid'];

	// js config
	$uiOptions = $config['UI']['options'];
	$uiOptions['state']['last_page'] = Mosaic::getLastCompletePage();

?>
<!DOCTYPE html>
<html lang="<?= $language ?>">

	<head>

		<title>Firefox 4 Twitter Party</title>

		<meta charset="utf-8" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="keywords" content="Mozilla,Firefox, Firefox 4, Collage, Twitter, Tweet" />
		<meta name="description" content="Firefox 4 Twitter Party is a visualization of Firefox 4 activity on Twitter. It is an experiment with exclusively open web tools and technologies." />
		<meta name="author" content="Quodis, Mozilla" />
		<meta name="copyright" content="© 2011" />
		<meta name="distribution" content="global" />

		<!-- stylesheets -->
		<link rel="stylesheet" href="<?=$config['UI']['css']['main']?>" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="<?=$config['UI']['css']['mosaic']?>" type="text/css" media="screen, projection" />

		<link rel="shortcut icon" href="/favicon.ico" />
		<link rel="apple-touch-icon" type="image/png" href="assets/images/global/apple-touch-icon-precomposed.png">
		<link rel="image_src" href="assets/images/global/ftp-facebook-thumb.png">

		<!-- scripts -->
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
		<script type="text/javascript" src="/assets/js/global.js?>"></script>
		<script type="text/javascript" src="<?=$config['UI']['js']['general']?>"></script>
		<script type="text/javascript" src="<?=$jsMosaicConfig?>"></script>
		<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

	</head>

	<body>

		<div id="container" class="clearfix">

			<div class="wrapper clearfix">

				<!-- HEADER -->
				<header id="brand" role="banner">
					<h1><a href="#" title="<?= _('Join the Firefox 4 Twitter Party') ?>"><?= _('Join the Firefox 4 Twitter Party') ?></a></h1>
				</header>

				<!-- CONTENT -->
				<aside id="main-content" class="clearfix">

					<!-- Here goes the text explaining how Firefox Twitter Party works. -->
					<p><?= sprintf(_('Be part of Team Firefox! Tweet about Firefox 4 with the %s hashtag and your avatar will join thousands of others from around the world as part of our logo mosaic.'), '<span class="hashtag">#fx4</span>') ?></p>

					<div id="twitter-counter" class="clearfix">
						<dl>
							<dt><a href="http://twitter.com/share?url=http://twitterparty.mozilla.org&via=firefox&related=firefox&text=<?= urlencode(_('Join me at the Firefox 4 Twitter Party and celebrate the newest version #fx4 #teamfirefox')) ?>" title="<?= _('Tweet') ?>" rel="external"><?= _('Tweet') ?></a></dt>
							<dd><span></span></dd>
						</dl>
					</div><!-- twitter-counter -->


					<form id="search-box" role="search">
						<h3><?= _("Who's at the party?") ?></h3>
						<label for="search-input" accesskey="f"><?= _('Find a Twitter username') ?></label>
						<input type="text" name="search-input" id="search-input" value="<?= _('Find a Twitter username') ?>" tabindex="1" />
						<button type="submit" name="search-button" id="search-submit-bttn" value="<?= _('Find') ?>" tabindex="2" title="<?= _('Find') ?>" class="button"><?= _('Find') ?></button>
						<div class="error">
							<p><?= _("This user hasn't joined the party yet.") ?></p>
						</div>

					</form><!-- search-box -->

					<div id="download">
						<a class="download-link download-firefox" href="http://www.mozilla.com/"><span class="download-content"><span class="download-title">Firefox 4</span><?= _('Download here') ?></span></a>
					</div><!-- download -->

				</aside><!-- main-content -->


				<section id="mosaic" role="img">
					<h2><?= _('Firefox Twitter Mosaic') ?></h2>

          <ul id="loading">
            <li><?= _('Sorting guest list alphabetically') ?></li>
            <li><?= _('Randomizing seating order') ?></li>
            <li><?= _('Cooling drinks to optimal temperature') ?></li>
            <li><?= _('Handing out name tags') ?></li>
            <li><?= _('Waxing the dance floor') ?></li>
            <li><?= _('Setting up Firefox deco') ?></li>
          </ul>

					<img src="" id="tile-hover" />

					<article id="bubble" class="bubble">

						<header>

							<h1><a href="#" title="<?= _('Twitter profile') ?>" rel="author external"></a><span> <?= _('wrote') ?></span></h1>
							<a href="#" title="" rel="author external" class="twitter-avatar">
							  <img src="" alt="<?= _('Twitter profile picture') ?>" width="48" height="48" />
							</a>

						  <time datetime="" pubdate><a href="#" rel="bookmark external" title="<?= _('Permalink') ?>"></a></time>

						</header>

						<p></p>

					</article><!-- bubble template -->

				</section>

			</div><!-- wrapper -->

			<div id="mozilla-badge">
				<a href="http://www.mozilla.org/" class="mozilla" title="<?= _('Visit Mozilla') ?>" rel="external"><?= _('Visit Mozilla') ?></a>
			</div><!-- mozilla-badge -->

		</div><!-- container -->

		<!-- FOOTER -->
		<footer>

			<div id="sub-footer" role="content-info" class="clearfix">

				<h3><?= _("Let's be <span>Friends!</span>") ?></h3>

				<ul>
					<li id="footer-twitter"><a href="http://twitter.com/firefox"><?= _('Twitter') ?></a></li>
					<li id="footer-facebook"><a href="http://Facebook.com/Firefox"><?= _('Facebook') ?></a></li>
					<li id="footer-connect"><a href="/en-US/firefox/connect/"><?= _('More Ways to Connect') ?></a></li>
				</ul>

				<p id="sub-footer-newsletter">
					<span class="intro"><?= _('Want us to keep in touch?') ?></span>
					<a href="http://www.mozilla.com/newsletter/"><?= _('Get Monthly News') ?> <span>»</span></a>
				</p>

			</div><!-- sub-footer -->

			<div id="footer-copyright">

				<div id="footer-left">
					<p id="footer-links">
						<a href="http://www.mozilla.com/privacy-policy.html"><?= _('Privacy Policy') ?></a> &nbsp;|&nbsp;
						<a href="http://www.mozilla.com/about/legal.html"><?= _('Legal Notices') ?></a> &nbsp;|&nbsp;
						<a href="http://www.mozilla.com/legal/fraud-report/index.html"><?= _('Report Trademark Abuse') ?></a>
					</p>

					<p><?= _('Except where otherwise <a href="http://www.mozilla.com/about/legal.html#site">noted</a>, content on this site is licensed under the <br /><a href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution Share-Alike License v3.0</a> or any later version.') ?></p>
				</div><!-- footer-left -->

				<div id="footer-right">

					<form id="lang_form" dir="ltr" method="get">

						<label for="flang">Other Languages</label>

						<select id="flang" name="flang">
							<option value="en-US">English (US)</option>
							<option value="pt-PT">Português (Europeu)</option>
						</select>

					</form>

				</div> <!-- footer-right -->

			</div><!-- footer-copyright -->

		</footer>

		<script type="text/javascript">
		//<![CDATA[
		(function($) {
		  $.extend(party, <?=json_encode($uiOptions)?>);
		})(jQuery);
		//]]>
		</script>

	</body>

</html>
<?php

} // main()

try
{
	main($request_uri[1][0] . strtoupper($request_uri[2][0]));
}
catch(Exception $e) {
	Debug::logError($e, 'EXCEPTION ' . $e->getMessage());
	Dispatch::now(0, 'EXCEPTION ' . $e->getMessage());
}

?>