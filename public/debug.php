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
<!doctype html>
<html dir="ltr" lang="en-US">
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
<link href="assets/css/debug.css" type="text/css" rel="stylesheet" />

	<?php if (!$isOldBrowser) { ?>
<script type="text/javascript" src="assets/js/jquery-1.4.2.min.js" charset="utf-8"></script>

	<?php }?>

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
<body class="<?=implode(" ", $classes)?>" >

	<label>Last Page:<span><?=Mosaic::getCurrentWorkingPageNo()?></span></label>
	<label>PageNo:</label>
	<input type="text" id="page-no" value="<?=Mosaic::getCurrentWorkingPageNo()?>"/>
	<label>Z:</label>
	<input type="text" id="z" value=""/>
	<button id ="bt-page-load">Go</button>
	<button id ="bt-poll">Poll</button>
	<label id="last-tweet">Last Tweet:<span>?</span></label>
	<div id="main"></div>

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

		function addImage(data, i)
		{
			var x = config.index[i].x;
			var y = config.index[i].y;
			var offsetX = config.tileSize * x;
			var offsetY = config.tileSize * y;
			$('#main').append('<img id="image-' + i + '" src="data:image/gif;base64,' + data + '" style="width:12px; height:12px; position: absolute; top: ' + offsetY +'px; left: ' + offsetX + 'px" />');
		}

		function loadPage(pageNo, z)
		{

			$('#main img').remove();

			var params = {}
			if (pageNo) {
				params.page = pageNo;
				params.z = z;
			}

			console.log('PAGE > PARAMS', params);

			$.ajax( {
				type: 'GET',
				url: 'page.php',
				data: params,
				dataType: 'json',
				success: function(data) {
					lastId = data.payload.lastId;
					$('#last-tweet span').text(data.payload.lastId);
					var imageData, i;
					for (i in data.payload.tweets) {
						imageData = data.payload.tweets[i].imageData;
						addImage(imageData, data.payload.tweets[i].position);
						// fetch position from index
					}
				}.bind(this),
					error: function() {
				}.bind(this)
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
					lastId = data.payload.lastId;
					$('#last-tweet span').text(data.payload.lastId);
					var imageData, i;
					for (i in data.payload.tweets) {
						imageData = data.payload.tweets[i].imageData;
						addImage(imageData, data.payload.tweets[i].position);
						// fetch position from index
					}
				}.bind(this),
					error: function() {
				}.bind(this)
			});
		}

		loadPage();

		$('#bt-page-load').click( function() {

			loadPage($('#page-no').val());

		} );

		$('#bt-poll').click( function() {

			poll();

		} );

<?php } else { ?>

<?php } ?>
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