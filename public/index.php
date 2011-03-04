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
	DEFINE('NO_DB', TRUE);
	DEFINE('CLIENT', 'html');
	DEFINE('CONTEXT', __FILE__);
	include '../bootstrap.php';

	Debug::setLogMsgFile($config['App']['pathLog'] .'/dashboard.msg.log');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/dashboard.error.log');

	// check cache
	if ($output = Cache::get('TWITTER-PARTY::index::lang=' . $language))
	{
		Dispatch::now(1);
	}

	initDb($config);

	// mosaic config file
	$jsMosaicConfig = $config['Store']['url'] . $config['UI']['js-config']['grid'];

	// js config
	$uiOptions = $config['UI']['options'];
	$uiOptions['state']['last_page'] = Mosaic::getLastCompletePage();

?>
<!DOCTYPE html>
<html lang="<?= $language ?>">

	<head>

		<title><?= /* Browser title */ _('Firefox 4 Twitter Party') ?></title>

		<meta charset="utf-8" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="keywords" content="<?= /* Meta tag keywords */ _('Mozilla, Firefox, Firefox 4, Party, Twitter, Tweet') ?>" />
		<meta name="description" content="<?= /* Meta tag description */ _('Firefox 4 Twitter Party is an interactive visualization of Firefox 4 activity on Twitter.') ?>" />
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
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
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
					<a href="/" title="<?= _('Start over') ?>">
						<?= /* Logo translation. Feel free to change the order of the h1, h2 and p blocks. h1 max of 9 chars. h2 max of 13 chars. em max of 18 chars. */ _('<h1>Firefox 4</h1><h2>Twitter Party</h2><p><em>Join the</em></p>') ?>
					</a>
				</header>

				<!-- CONTENT -->
				<aside id="main-content" class="clearfix">

					<!-- Here goes the text explaining how Firefox Twitter Party works. -->
					<p><?= sprintf(_('Be part of Team Firefox! Tweet about Firefox 4 with the %s hashtag and your avatar will join thousands of others from around the world as part of our logo mosaic.'), '<span class="hashtag">#fx4</span>') ?></p>

					<div id="twitter-counter" class="clearfix">
						<dl>
							<dt><a href="http://twitter.com/share?url=http://twitterparty.mozilla.org&via=firefox&related=firefox&text=<?= urlencode( /* Default text to tweet */_('Join me at the Firefox 4 Twitter Party and celebrate the newest version') . ' #fx4 #teamfirefox') ?>" title="<?= _('Tweet') ?>" rel="external"><?= _('Tweet') ?></a></dt>
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
						<a class="download-link download-firefox" href="http://www.mozilla.com/"><span class="download-content"><span class="download-title">Firefox 4</span><?= /* Max of 15 chars */ _('Download here') ?></span></a>
					</div><!-- download -->

				</aside><!-- main-content -->


				<section id="mosaic" role="img">
					<h2><?= _('Mosaic') ?></h2>
					
          <ul id="loading">
            <li><?= /* Funny loading message */ _('Sorting guest list alphabetically') ?></li>
            <li><?= /* Funny loading message */ _('Randomizing seating order') ?></li>
            <li><?= /* Funny loading message */ _('Cooling drinks to optimal temperature') ?></li>
            <li><?= /* Funny loading message */ _('Handing out name tags') ?></li>
            <li><?= /* Funny loading message */ _('Waxing the dance floor') ?></li>
            <li><?= /* Funny loading message */ _('Setting up Firefox deco') ?></li>
          </ul>

					<img src="" id="tile-hover" />

					<article id="bubble" class="bubble">

						<header>

							<h1><a href="#" title="<?= _('Twitter profile') ?>" rel="author external"></a><span> <?= /* Used in: "twitter username" wrote */ _('wrote') ?></span></h1>
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
				<h3><span><?= /* Keep the <em> at beginning or end only. Max 20 chars before the em, Max 10 chars for the em. */ _("Let's be <em>Friends!</em>") ?></span></h3>

				<ul>
					<li id="footer-twitter"><a href="http://twitter.com/firefox"><?= _('Twitter') ?></a></li>
					<li id="footer-facebook"><a href="http://Facebook.com/Firefox"><?= _('Facebook') ?></a></li>
					<li id="footer-connect"><a href="/en-US/firefox/connect/"><?= /* Max of 20 chars */ _('More Ways to Connect') ?></a></li>
				</ul>

				<p id="sub-footer-newsletter">
					<span class="intro"><?= /* Max of 30 chars*/ _('Want us to keep in touch?') ?></span>
					<a href="http://www.mozilla.com/newsletter/"><?= /* Max of 25 chars */ _('Get Monthly News') ?> <span>»</span></a>
				</p>

			</div><!-- sub-footer -->

			<div id="footer-copyright">

				<div id="footer-left">
					<p id="footer-links">
						<a href="http://www.mozilla.com/privacy-policy.html"><?= _('Privacy Policy') ?></a> &nbsp;|&nbsp;
						<a href="http://www.mozilla.com/about/legal.html"><?= _('Legal Notices') ?></a> &nbsp;|&nbsp;
						<a href="http://www.mozilla.com/legal/fraud-report/index.html"><?= _('Report Trademark Abuse') ?></a>
					</p>

					<p><?= /* Leave all html code unchanged */ _('Except where otherwise <a href="http://www.mozilla.com/about/legal.html#site">noted</a>, content on this site is licensed under the <br /><a href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution Share-Alike License v3.0</a> or any later version.') ?></p>
				</div><!-- footer-left -->
        
				<div id="footer-right">

					<form id="lang_form" dir="ltr" method="get">

						<label for="flang"><?= _('Other Languages') ?></label>

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
		  $.extend(party, {l10n: {
		    date_format:'<?= /* Date format. Documentation: http://php.net/manual/en/function.date.php */ _('M j Y, g:i A') ?>',
		    dec_point:'<?= /* Decimal separator for numbers (dec_point). */ _('.') ?>',
		    thousands_sep:'<?= /* Thousands separator for numbers (thousands_sep) */ _(',') ?>'
		  }});
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