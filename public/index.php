<?php
/**
 * @pacjage    Firefox 4 Twitter Party
 * @subpackage front-end
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>, Leonardo Xavier <leo@quodis.com>, Leihla Pinho <leihla@quodis.com>, Luis Abreu <luis@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * escape from global scope
 */
function main()
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

	// mosaic config file
	$jsMosaicConfig = $config['Store']['url'] . $config['UI']['js-config']['grid'];

	// js config
	$uiOptions = $config['UI']['options'];
	$uiOptions['last_page'] = Mosaic::getCurrentWorkingPageNo() - 1;
	$uiOptions['tile_size'] = $config['Mosaic']['tileSize'] + 20;

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
		<meta name="author" content="Quodis, Mozilla" />
		<meta name="copyright" content="© 2011" />
		<meta name="distribution" content="global" />

		<!-- stylesheets -->
		<link rel="stylesheet" href="<?=$config['UI']['css']['main']?>" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="<?=$config['UI']['css']['mosaic']?>" type="text/css" media="screen, projection" />

		<link rel="shortcut icon" href="assets/img/global/favicon.ico" />
		<link rel="apple-touch-icon" type="image/png" href="">
		<link rel="image_src" href="">

		<!-- scripts -->
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
		<script type="text/javascript" src="<?=$config['UI']['js']['general']?>"></script>
		<script type="text/javascript" src="<?=$jsMosaicConfig?>"></script>
		<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->


		<?php if ($config['UI']['gaEnabled']) { ?>
		<script type="text/javascript">
		//<![CDATA[
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', '<?=$config['UI']['gaId']?>']);
			_gaq.push(['_trackPageview']);

			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		//]]>
		</script>
		<?php } ?>

	</head>

	<body>

		<div id="container">

			<div class="wrapper">

				<!-- HEADER -->
				<header id="brand">
					<h1><a href="#" title="Join the Firefox 4 Twitter Party">Join the Firefox 4 Twitter Party</a></h1>
				</header>


				<!-- CONTENT -->
				<aside id="main-content" class="clearfix">

					<!-- Here goes the text explaining how Firefox Twitter Party works. -->
					<p>Be part of Team Firefox! Tweet about Firefox 4 with the <span class="hashtag">#fx4</span> hashtag and your avatar will join thousands of others from around the world as part of our logo mosaic.</p>

					<div id="twitter-counter">
						<dl>
							<dt><a href="http://twitter.com/share?related=firefox&text=Join+me+at+the+Firefox+4+Twitter+Party+and+celebrate+the+newest+version&url=http%3A%2F%2Ftwitterparty.mozilla.com&via=firefox" title="Tweet" rel="external">Tweet</a></dt>
							<dd><span>-</span></dd>
						</dl>
					</div><!-- twitter-counter -->


					<form id="search-box" role="search">
						<h3>Who's at the party?</h3>
						<label for="search-input" accesskey="f">Find a Twitter username</label>
						<input type="text" name="search-input" id="search-input" value="Find a Twitter username" tabindex="1" />
						<button type="submit" name="search-button" id="search-submit-bttn" value="Find" tabindex="2" title="Find" class="button">Find</button>
					</form><!-- search-box -->


				</aside><!-- main-content -->


				<div id="download">

					<ul class="home-download">
						<li class="os_windows hide">
							<a class="download-link download-firefox" onclick="init_download('http://download.mozilla.org/?product=firefox-3.6.13&amp;os=win&amp;lang=en-US');" href="http://www.mozilla.com/products/download.html?product=firefox-3.6.13&amp;os=win&amp;lang=en-US" ><span class="download-content"><span class="download-title">Firefox 4</span>Free Download</span></a>
						</li>
						<li class="os_linux hide">
							<a class="download-link download-firefox" onclick="init_download('http://download.mozilla.org/?product=firefox-3.6.13&amp;os=linux&amp;lang=en-US');" href="http://www.mozilla.com/products/download.html?product=firefox-3.6.13&amp;os=linux&amp;lang=en-US" ><span class="download-content"><span class="download-title">Firefox 4</span>Free Download</span></a>
						</li>
						<li class="os_osx">
							<a class="download-link download-firefox" onclick="init_download('http://download.mozilla.org/?product=firefox-3.6.13&amp;os=osx&amp;lang=en-US');" href="http://www.mozilla.com/products/download.html?product=firefox-3.6.13&amp;os=osx&amp;lang=en-US" ><span class="download-content"><span class="download-title">Firefox 4</span>Free Download</span></a>
						</li>
					</ul> <!-- home-download -->

				</div><!-- download -->
			</div><!-- wrapper -->

		<section id="mosaic">
			<h2>Firefox Twitter Mosaic</h2>
			<p id="loading"></p>
      
			<article id="bubble" class="bubble">
			
				<header>
										
					<h1><a href="#" title="Twitter profile" rel="author external"></a><span> wrote</span></h1>
					<a href="#" title="" rel="author external" class="twitter-avatar">	
					  <img src="" alt="Twitter profile picture" width="48" height="48" />
					</a>
				
				  <time datetime="" pubdate><a href="#" rel="bookmark external" title="Permalink"></a></time>
					
				</header>
				
				<p></p>
				
			</article><!-- bubble -->
			
		</section>

		<div id="mozilla-badge">
			<a href="http://www.mozilla.org/" class="mozilla" title="Visit Mozilla" rel="external">Visit Mozilla</a>
		</div><!-- mozilla-badge -->



	</div><!-- container -->

		<!-- FOOTER -->
		<footer>

			<div id="sub-footer">

				<h3>Let's be <span>Friends!</span></h3>

				<ul>
					<li id="footer-twitter"><a href="http://twitter.com/firefox">Twitter</a></li>
					<li id="footer-facebook"><a href="http://Facebook.com/Firefox">Facebook</a></li>
					<li id="footer-connect"><a href="/en-US/firefox/connect/">More Ways to Connect</a></li>
				</ul>

				<p id="sub-footer-newsletter">
					<span class="intro">Want us to keep in touch?</span>
					<a href="/en-US/newsletter/">Get Monthly News <span>»</span></a>
				</p>

			</div><!-- sub-footer -->

			<div id="footer-copyright">

				<p id="footer-links">
					<a href="/en-US/privacy-policy.html">Privacy Policy</a> &nbsp;|&nbsp;
					<a href="/en-US/about/legal.html">Legal Notices</a> &nbsp;|&nbsp;
					<a href="/en-US/legal/fraud-report/index.html">Report Trademark Abuse</a>
				</p>

				<p>Except where otherwise <a href="/en-US/about/legal.html#site">noted</a>, content on this site is licensed under the <br /><a href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution Share-Alike License v3.0</a> or any later version.</p>
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
	main();
}
catch(Exception $e) {
	Debug::logError($e, 'EXCEPTION ' . $e->getMessage());
	Dispatch::now(0, 'EXCEPTION ' . $e->getMessage());
}

?>