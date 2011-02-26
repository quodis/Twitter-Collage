<?php
/**
 * @pacjage    Firefox 4 Twitter Party
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
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()-10) . " GMT");
	header("Expires: " . gmdate("D, d M Y H:i:s", time() + 5) . " GMT");
	header("Pragma: no-cache");
	header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
	header('P3P: CP="CAO PSA OUR"');

	DEFINE('CLIENT', 'html');
	DEFINE('CONTEXT', __FILE__);
	include '../bootstrap.php';
	session_cache_limiter("nocache");

	$isOldBrowser = isset($_GET['oldBrowser']);

	// body classes
	$classes = array();
	if ($isOldBrowser) $classes[] = 'old-browser';

	?>
<!DOCTYPE html>
<html lang="en">

	<head>

		<meta charset="utf-8" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="keywords" content="<?=$config['UI']['keywords']?>" />
		<meta name="description" content="<?=$config['UI']['description']?>" />
		<meta name="author" content="Quodis" />
		<meta name="copyright" content="© 2011" />
		<meta name="distribution" content="global" />

		<link rel="shortcut icon" href="/assets/imgs/favicon.png">

		<title><?=$config['UI']['title']?></title>

		<link href="assets/css/reset.css" type="text/css" rel="stylesheet" />
		<link rel="stylesheet" href="<?=$config['UI']['css']['main']?>" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="<?=$config['UI']['css']['mosaic']?>" type="text/css" media="screen, projection" />
		<link href="assets/css/debug.css" type="text/css" rel="stylesheet" />

		<script type="text/javascript" src="assets/js/jquery-1.4.2.min.js" charset="utf-8"></script>

	</head>

	<body>

		<div id="container">

			<div class="wrapper">

				<!-- HEADER -->
				<header id="brand">
					<h1><a href="#" title="Join the Firefox 4 Twitter Party">Dashboard</a></h1>
					<a href="#" class="option">button</a>
					<a href="#" class="option">reset</a>
					<a href="#" class="option">foo</a>
					<a href="#" class="option">bar</a>
				</header>


				<!-- CONTENT -->
				<aside id="main-content" class="clearfix">

					<!-- Here goes the text explaining how Firefox Twitter Party works. -->
					<p>Now feeding on hash <span class="hashtag"><?=$config['Twitter']['terms']?></span>.</p>

					<div class="counter">
						<dl class="tweets">
							<dt><span>Last Tweet</span></dt>
							<dd id="last-tweet"><span></span></dd>
						</dl>
						<dl class="pages">
							<dt><span>Cooked Pages</span></dt>
							<dd id="last-page"><span><?=(Mosaic::getCurrentWorkingPageNo() - 1)?></span></dd>
						</dl>
						<dl class="delay">
							<dt><span>Delay</span></dt>
							<dd id="job-delay"><span></span></dd>
						</dl>
					</div><!-- twitter-counter -->

					<div class="control-box page clearfix">
						<h3>Go To Page</h3>
						<label for="find-user" accesskey="p">PageNo</label>
						<input type="text" id="page-no" value="<?=(Mosaic::getCurrentWorkingPageNo())?>" tabindex="1" />
						<button class="submit" type="submit" id="page-load-bttn" tabindex="2" title="Go" class="button"><span>Go</span></button>
						<button class="submit" type="submit" id="force-poll-bttn" tabindex="3" title="Force Poll" class="button"><span>Poll Now</span></button>
					</div><!-- control-box page -->

					<div class="control-box user clearfix" role="search">
						<h3>Find User</h3>
						<label for="find-user" accesskey="f">Twitter username</label>
						<input type="text" id="find-user" value="Find a Twitter username" tabindex="4" />
						<button class="decorator" type="submit" id="find-user-submit-bttn" tabindex="5" title="Find" class="button">Find</button>
					</div><!-- control-box user -->

					<div class="control-box terms clearfix" role="search">
						<h3>Search Tweets</h3>
						<label for="search-input" accesskey="s">Search terms</label>
						<input type="text" id="search-tweets" value="Find tweets that match" tabindex="6" />
						<button class="decorator" type="submit" id="search-tweets-submit-bttn" tabindex="7" title="Search" class="button">Search</button>
					</div><!-- control-box terms -->


				</aside><!-- main-content -->

			</div><!-- wrapper -->

		<section id="mosaic">
			<h2>Firefox Twitter Mosaic</h2>
		</section>


	</div><!-- container -->

		<!-- FOOTER -->
		<footer>

			<div id="footer-copyright">

				<p id="footer-links">
					<a href="/en-US/privacy-policy.html">Privacy Policy</a> &nbsp;|&nbsp;
					<a href="/en-US/about/legal.html">Legal Notices</a> &nbsp;|&nbsp;
					<a href="/en-US/legal/fraud-report/index.html">Report Trademark Abuse</a>
				</p>

				<p>Except where otherwise <a href="/en-US/about/legal.html#site">noted</a>, content on this site is licensed under the <br /><a href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution Share-Alike License v3.0</a> or any later version.</p>
			</div><!-- footer-copyright -->

		</footer>

	</body>

<script type="text/javascript">
	//<![CDATA[
	(function($) {
<?php if (!$isOldBrowser) { ?>

		eval('var config = <?=json_encode(Mosaic::getPageConfig())?>');

		config.tileSize = <?=$config['Mosaic']['tileSize']?>;

		//console.log('GRID CONFIG', config);

		/**
		 * mock support for window.console
		 */
		if (!window.console || !window.console.log) {
			window.console = {};
			window.console.log = function(whatever) {};
			window.console.dir = function(whenever) {};
		}

		/**
		 * add support for prototype like bind()
		 */
		Function.prototype.bind = function(){
			if (arguments.length < 2 && arguments[0] === undefined) {
				return this;
			}
			var _method = this;
			var lesArguments = [];
			var that = arguments[0];
			for(var i=1, l=arguments.length; i<l; i++){
				lesArguments.push(arguments[i]);
			}
			return function(){
				return _method.apply(that, lesArguments.concat(function(tmpArgs){
					 var leArgument2 = [];
					 for (var j=0, total=tmpArgs.length; j < total; j++) {
						 leArgument2.push(tmpArgs[j]);
					 }
					 return leArgument2;
				 }(arguments)));
			};
		}

		var lastId = 0;
		var lastPage = '<?=(Mosaic::getCurrentWorkingPageNo() - 1)?>';

		function addImage(data, i)
		{
			var x = config.index[i].x;
			var y = config.index[i].y;
			var offsetX = config.tileSize * x;
			var offsetY = config.tileSize * y;
			$('#mosaic').append('<img id="image-' + i + '" src="data:image/gif;base64,' + data + '" style="width:12px; height:12px; position: absolute; top: ' + offsetY +'px; left: ' + offsetX + 'px" />');
		}

		function loadPage(pageNo)
		{
			$('#mosaic img').remove();
			$('<div id="loading"></div>').appendTo('#mosaic');

			console.log('PAGE > PAGE NO', pageNo);

			alert('<?=$config['UI']['options']['store_url']?>/pages/page_' + pageNo + '.json');

			$.ajax( {
				type: 'GET',
				url: '<?=$config['UI']['options']['store_url']?>/pages/page_' + pageNo + '.json',
				dataType: 'text',
				success: function(data) {
					console.log(data);
					return;

					lastId = data.payload.lastId;
					$('#last-tweet span').text(data.payload.lastId);
					var count = showTweets(data.payload.tweets);
					if (!count) {
						alert('empty page, TODO proper dialog');
					}
					else lastId = data.payload.lastId;
				}
			});


		}

		function poll()
		{
			var params = {
				'lastId' : lastId
			}

			console.log('POLL > PARAMS', params);

			$.ajax( {
				type: 'GET',
				url: 'poll.php',
				data: params,
				dataType: 'json',
				success: function(data) {
					$('#last-tweet span').text(data.payload.lastId);
					var count = showTweets(data.payload.tweets);
					if (!count) {
						alert('empty poll, TODO proper dialog');
					}
					else {
						lastId = data.payload.lastId;
						alert(count + ' tweets, TODO proper dialog');
					}
				}.bind(this),
					error: function() {
				}.bind(this)
			});
		}

		function showTweets(tweets)
		{
			var imageData, i, count = 0;
			for (i in tweets) {
				count++;
				imageData = tweets[i].imageData;
				addImage(imageData, tweets[i].position);
				// fetch position from index
			}
			return count;
		}

		$('#page-load-bttn').click( function() {

			loadPage($('#page-no').val());

		} );

		$('#force-poll-bttn').click( function() {

			poll();

		} );

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