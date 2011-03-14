<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * escape from global scope
 */
function main()
{
	header("ETag: PUB" . time());
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()-10) . " GM	T");
	header("Expires: " . gmdate("D, d M Y H:i:s", time() + 5) . " GMT");
	header("Pragma: no-cache");
	header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");

	DEFINE('GENERATETOKEN', 1);
	DEFINE('CLIENT', 'html');
	DEFINE('CONTEXT', __FILE__);
	include '../../bootstrap.php';
	session_cache_limiter("nocache");

	Debug::setLogMsgFile($config['App']['pathLog'] .'/dashboard.msg.log');
	Debug::setLogErrorFile($config['App']['pathLog'] .'/dashboard.error.log');

	// not in use
	$isOldBrowser = isset($_GET['oldBrowser']);

	// body classes
	$classes = array();
	if ($isOldBrowser) $classes[] = 'old-browser';

	// mosaic config file
	$jsMosaicConfig = $config['Store']['url'] . $config['UI']['js-config']['grid'];

	// js config
	$uiOptions = $config['UI']['options'];
	$uiOptions['tile_size'] = $config['Mosaic']['tileSize'];

	// mosaic data
	$lastTweet = Mosaic::getLastTweet();
	$lastProcessedTweet = Mosaic::getLastTweetWithImage();
	$delaySeconds = Tweet::getAverageDelay(10);
	$delayTweets = Tweet::getCountUnprocessed();
	if (!$delayTweets) $delaySeconds = 0;

	// dashboard state
	$dashboardState = array(
		'token' => $_SESSION['token'],
		'last_id' => $lastProcessedTweet['id'],
		'tweet_count' =>  Tweet::getCount(TRUE),
		'guest_count' =>  Tweet::getUserCount(TRUE),
		'delay' => array(
			'tweets' => $delayTweets,
			'seconds' => $delaySeconds
		)
	);

?>

<!DOCTYPE html>
<html lang="en">

	<head>

		<title><?=$config['UI']['title']?></title>

		<meta charset="utf-8" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="keywords" content="<?=$config['UI']['keywords']?>" />
		<meta name="description" content="<?=$config['UI']['description']?>" />
		<meta name="author" content="Quodis" />
		<meta name="copyright" content="© 2011" />
		<meta name="distribution" content="global" />

		<!-- stylesheets -->
		<link rel="stylesheet" href="<?=$config['UI']['css']['main']?>" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="<?=$config['UI']['css']['mosaic']?>" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="<?=$config['UI']['css']['dashboard']?>" type="text/css" media="screen, projection" />

		<link rel="shortcut icon" href="/favicon.ico" />
		<link rel="apple-touch-icon" type="image/png" href="">
		<link rel="image_src" href="">

		<!-- scripts -->
		<script type="text/javascript" src="/assets/js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="/assets/js/global.js?>"></script>
		<script type="text/javascript" src="<?=$config['UI']['js']['dashboard']?>"></script>
		<script type="text/javascript" src="<?=$jsMosaicConfig?>"></script>
		<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

	</head>

	<body>

		<div id="container" class="clearfix">

			<div class="wrapper clearfix">

				<!-- HEADER -->
				<header id="brand">
					<h1><a href="#" title="Join the Firefox 4 Twitter Party">Dashboard</a></h1>
				</header>


				<!-- CONTENT -->
				<aside id="main-content" class="clearfix">

					<!-- Here goes the text explaining how Firefox Twitter Party works. -->
					<p>Party Tag: <span class="hashtag"><?=$config['Twitter']['terms']?></span>.</p>

					<div class="counter">
						<dl class="guests">
							<dt><span>Guests</span></dt>
							<dd class="value" id="guest-count"><span>0</span></dd>
						</dl>
						<dl class="tweets">
							<dt><span>Tweets</span></dt>
							<dd class="value" id="tweet-count"><span>0</span></dd>
						</dl>
						<dl class="delay delay-seconds">
							<dt><span>Delay</span></dt>
							<dd id="job-delay-seconds"><strong class="value"><span><?=$delaySeconds?></span></strong> <em>secs</em></dd>
						</dl>
						<dl class="delay delay-tweets">
							<dt><span>Delay</span></dt>
							<dd id="job-delay-tweets"><strong class="value"><span><?=$delayTweets?></span></strong> <em>tweets</em></dd>
						</dl>
					</div><!-- counters -->

					<div class="control-box first user clearfix" role="search">
						<h3>Search Users</h3>
						<label for="find-user" accesskey="u">Twitter username</label>
						<input type="text" id="find-user" value="twitter user (or part)" tabindex="1" />
						<button class="decorator" type="submit" id="find-user-submit-bttn" tabindex="2" title="Find" class="button">Find</button>
					</div><!-- control-box user -->

					<div class="control-box terms clearfix" role="search">
						<h3>Search Tweets</h3>
						<label for="search-tweets" accesskey="t">Search terms</label>
						<input type="text" id="search-tweets" value="search terms" tabindex="3" />
						<button class="decorator" type="submit" id="search-tweets-submit-bttn" tabindex="4" title="Search" class="button">Search</button>
					</div><!-- control-box terms -->

					<div class="control-box page clearfix">
						<h3>Load tiles</h3>
						<button class="submit" type="submit" id="load-mosaic-bttn" tabindex="5" class="button"><span>Full Mosaic</span></button>
						<button class="submit" type="submit" id="force-poll-bttn" tabindex="6" class="button"><span>Poll</span></button>
					</div><!-- control-box page -->

					<div id="quodis-badge">
						<a target="_blank" href="http://quodis.com" title="Quodis" class="text-replace">by Quodis</a>
					</div><!-- quodis-badge -->

				</aside><!-- main-content -->

				<ul id="mosaic">
				</ul>

				<section id="widgets">
				</section>

			</div><!-- wrapper -->

			<div id="mozilla-badge">
				<a href="http://www.mozilla.org/" class="mozilla" title="<?= _('Visit Mozilla') ?>" rel="external"><?= _('Visit Mozilla') ?></a>
			</div><!-- mozilla-badge -->

		</div><!-- container -->

		<!-- FOOTER -->
		<footer>

			<div id="footer-copyright">

				<div id="footer-left">
					<p id="footer-links">
						<a href="http://www.mozilla.com/privacy-policy.html"><?= _('Privacy Policy') ?></a> &nbsp;|&nbsp;
						<a href="http://www.mozilla.com/about/legal.html"><?= _('Legal Notices') ?></a> &nbsp;|&nbsp;
						<a href="http://www.mozilla.com/legal/fraud-report/index.html"><?= _('Report Trademark Abuse') ?></a>
					</p>

					<p><?= _('Except where otherwise <a href="http://www.mozilla.com/about/legal.html#site">noted</a>, content on this site is licensed under the <br /><a href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution Share-Alike License v3.0</a> or any later version.') ?></p>
				</div><!-- footer-left -->

			</div><!-- footer-copyright -->

		</footer>

	</body>

<script type="text/javascript">
	//<![CDATA[
	(function($) {
<?php if (!$isOldBrowser) { ?>

		$.extend(party, <?=json_encode($uiOptions)?>);

		Dashboard.init( <?=json_encode($uiOptions)?>, <?=json_encode($dashboardState) ?>);

<?php } else { ?>

<?php } ?>
	})(jQuery);
	//]]>
	</script>

</html>
<?php

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