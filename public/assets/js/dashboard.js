/**
 * Firefox 4 Twitter Party
 * Design and development by Mozilla, Quodis
 * http://www.mozilla.com
 * http://www.quodis.com
 * 
 * Licensed under a Creative Commons Attribution Share-Alike License v3.0 http://creativecommons.org/licenses/by-sa/3.0/ 
 */

/**
 * Party mock
 */
var party = party || {};

/**
 * ready
 */
(function($) {  
	
	/**
	 * Dashboard static class
	 */	
	Dashboard = {}; 
	$.extend(Dashboard, {

		// configuration
		defaults : {
			'store_url' : '',
			'tile_size' : 0,
			'idle_timeout' : 120 * 1000,
			'highlight_timeout' : 500,
			'short_stat_interval' : 3000
		},
		options : { },

		// state
		state : {
			'party_on' : 'wild',
			'guest_count' : 0,
			'last_id' : 0,
			'tweet_count' : 0,
			'last_page' : 0,
			'idle_timeout' : null,
			'highlight_timeout' : null,
			'short_stat_interval' : null
		},
		
		
		// stores ongoing requests (indexed by id) to prevent obsoletes ... see load
		load_requests : { },
		
		// borrow from party (hey, who let this guy in?)
		mosaic : party.mosaic,
		
		// visible tiles
		tiles : {},
		
		dialog : null,

		/**
		 * init
		 * 
		 * @param mixed options
		 */
		init : function(options, state) 
		{
			this.mosaic = party.mosaic;
			this.options = $.extend(this.defaults, options);
			this.state = $.extend(this.state, state);
			
			this.buildInterface();
			
			this.loadPage(parseInt(this.state.last_page, 10) + 1);
		},


		// ---- build ui

		/**
		 * binds, the works
		 */
		buildInterface : function() 
		{
			// show last id
			$('#guest-count span').rollNumbers(this.state.guest_count, 3000);
			$('#tweet-count span').rollNumbers(this.state.tweet_count, 5000);
			$('#last-page span').rollNumbers(this.state.last_page, 3000);
			
			// bind search user
			$('#find-user').inputDefault().inputState( { 'onEnter' : function() {
				if ($('#find-user').val().length) {
					this.findUser($('#find-user').val());
				}
			}.bind(this) } );
			
			// bind search tweets
			$('#search-tweets').inputDefault().inputState( { 'onEnter' : function() {
				if ($('#search-tweets').val().length) {
					this.searchTweets($('#search-tweets').val());
				}
			}.bind(this) } );
			
			// bind go to page
			$('#page-load-bttn').click( function(ev) {
				ev.stopPropagation();
				this.loadPage($('#page-no').val());
			}.bind(this) );
			
			// bind poll
			$('#mosaic').mousemove( function(ev) {
				if ($('body').hasClass('shade')) return;
				var offset = $('#mosaic').offset();
				var x = Math.ceil((ev.clientX + f_scrollLeft() - offset.left) / 12) - 1;
				var y = Math.ceil((ev.clientY + f_scrollTop() - offset.top) / 12) - 1;
				if (x < 0 || y < 0) return;
				var tile = this.mosaic.grid[x][y];
				// is valid x,y
				if ('undefined' != typeof tile) {
					$('#mosaic').find('li').removeClass('excite');
					// is loaded tile
					if ('undefined' !== typeof this.tiles[tile.i]) {
						$('#' + tile.i).addClass('excite');
						console.log($('#' + tile.i));
						this.highlightTilePos(tile.i);
						$('#highlight').addClass('excite');
					}
				}
			}.bind(this) );
			
			// bind reset
			$('#mosaic').click( this.reset );
			
			// bind reset
			$('.close').live('click', this.reset );
			
			// stat timer
			this.state.short_stat_interval = window.setInterval( function() {
				this.load('/dashboard/stat-short.php', null, function(data) {
					if (!data) return;
					$.extend(this.state, data);
					$('#tweet-count span').rollNumbers(this.state.tweet_count, this.options.short_stat_interval);
					$('#last-page span').text(this.state.last_page);
					$('#job-delay span').html('<em>' + this.state.delay.seconds + ' sec / ' + this.state.delay.tweets + ' tweets</em>');
				}.bind(this) );
			}.bind(this), this.options.short_stat_interval);
		},
		
		
		// ---- state

		loadPage : function(page)
		{
			this.reset();
			
			this.tiles = null;
			this.tiles = {};
			$('#mosaic li').remove();
			
			$('<li id="loading">loading page...</li>').appendTo('#mosaic');

			// load
			var url = this.freshUrl(this.options.store_url+ '/pages/page_' + page + '.json');
			this.load(url, {}, function(data) {
				$('#loading').remove();
				var count = this.addTiles(data.tiles);
				if (!count) {
					$('<li id="loading" class="empty">empty page...</li>').appendTo('#mosaic');
				}
			}.bind(this), 'page', function() { 
				$('#loading').remove();
				$('<li id="loading">not found</li>').appendTo('#mosaic');
			} ) ;
		},

		poll : function()
		{
			this.reset();
			
			this.tiles = null;
			this.tiles = {};
			$('#mosaic li').remove();
			
			$('<li id="loading">loading poll...</li>').appendTo('#mosaic');
			
			var params = {
				'last_id' : this.state.last_id
			}

			Dashboard.load('/poll.php', params, function(data) {
				$('#loading').remove();
				var count = this.addTiles(data.tiles);
				if (count) {
					this.state.last_id = data.last_id;
				}
				else {
					$('<li id="loading" class="empty">empty poll...</li>').appendTo('#mosaic');
				}
			}.bind(this) );
		},
		
		reset : function() {
			$('#loading').remove();
			$('body').removeClass('shade');
			$('body').removeClass('highlight');
			$('body').removeClass('user');
			$('body').removeClass('user-list');
			$('body').removeClass('tweet-list');
			$('body').removeClass('user-tweets');
			$('#highlight').remove();
			$('#user-list').remove();
			$('#tweet-list').remove();
		},

		addTiles : function(tiles)
		{
			var imageData, i = 0, count = 0;
			for (i in tiles) {
				count++;
				this.addTile(tiles[i])
			}
			return count;
		},

		addTile : function(tile)
		{
			this.tiles[tile.position] = tile;
			$('#' + tile.position).remove();
			var x = this.mosaic.index[tile.position].x;
			var y = this.mosaic.index[tile.position].y;
			var offsetX = this.options.tile_size * x;
			var offsetY = this.options.tile_size * y;
			var html = '<li id="' + tile.position + '" style="position: absolute; top: ' + offsetY +'px; left: ' + offsetX + 'px"><img src="data:image/gif;base64,' + tile.imageData + '" /></li>';
			$(html).appendTo('#mosaic');
			$('#' + tile.position).click( function(ev) {
				ev.stopPropagation();
				this.openTile(tile.position);
			}.bind(this) );
		},
		
		highlightTilePos : function(position)
		{
			if ('undefined' == typeof this.tiles[position]) return;

			var tile = this.tiles[position];
			
			$('#highlight').remove();
			$('<div id="highlight" class="widget clearfix"></div>').appendTo('#widgets');
			$('#highlight .tweet').remove();
			$('#highlight .user').remove();
			var deleteBtn ='<button class="delete">delete tweet</button>';
			var userBtn = '<span class="user-link">all tweets by ' + tile.userName + '</span>';
			$(this.getTitleHtml('Tweet') + '<article class="tweet clearfix">' + this.getTweetHtml(tile) + userBtn + deleteBtn + '</article>').appendTo('#widgets #highlight');
			$('#highlight .delete').click( function() {
				this.deleteTweet(tile.id, function() {
					this.reset();
				}.bind(this) );
			}.bind(this) );
			$('#highlight .user-link').click( function() {
				this.showUser(tile.userId, tile.userName, tile.imageUrl);
			}.bind(this)  );
		},
		
		getTitleHtml : function(text) {
			return '<h3><span class="title">' + text + '</span><span class="close">close</span></h3>';
		},
		
		getTweetHtml : function(tweet)
		{
			var date = new Date(tweet.createdTs * 1000);
			// page, position, twitterId, userId, isoLanguage
			var contents = '<img src="' + tweet.imageUrl + '">\
				<p class="contents">' + tweet.contents + '</p>\
				<p class="user-name">' + tweet.userName + '</p>\
				<p class="created-date">' + date + '</p>';
			return contents;
		},
		
		getUserHtml : function(tweet)
		{
			// page, position, twitterId, userId, isoLanguage
			var contents = '<img src="' + tweet.imageUrl + '">\
				<p class="user-name">' + tweet.userName + '</p>';
			return contents;
		},
		
		openTile : function(position)
		{
			if ($('body').hasClass('shade')) {
				this.reset();
				return;
			}
			this.reset();
			
			$('body').addClass('shade highlight');
			
			this.highlightTilePos(position);
			
			// FIX user click
		},
		
		findUser : function(user_name)
		{
			this.reset();
			
			$('body').addClass('shade user-list');
			
			$('<li id="loading">searching users...</li>').appendTo('#mosaic');
			
			this.load('/dashboard/users-by-terms.php', {'terms' : user_name}, function(data) {
				
				$('#loading').remove();
				
				if (data.total == 1) {
					this.showUser(data.users[0].userId, data.users[0].userName, data.users[0].imageUrl);
					return;
				}
				
				if (!$('#user-list').length) {
					var title = 'Found ' + data.count;
					if (data.count > data.total) title += '/' + data.total;
					title = this.getTitleHtml(title + ' users matching «' + user_name + '»');
					$('<div id="user-list" class="widget list clearfix">' + title + '</div>').appendTo('#widgets');
				}
				
				if (!data.total) {
					return;
				}
				
				for (i = 0; i < data.users.length; i++) {
					for (i = 0; i < data.users.length; i++) {
						$('<article class="user clearfix" data-id="' + data.users[i].userId + '">' + this.getUserHtml(data.users[i]) + '</article>').appendTo('#widgets #user-list');
					}
					// user-name click
					$('#user-list .user').click( function(ev) {
						ev.stopPropagation();
						var el = $(ev.target).hasClass('user') ? $(ev.target) : $(ev.target).parents('.user');
						this.showUser(el.attr('data-id'), el.find('.user-name').text(), el.find('img').attr('src'));
					}.bind(this) );
				}
			}.bind(this), 'users-by-terms' );
		},
		
		
		showUser : function(userId, name, picture_url)
		{
			this.reset();
			
			$('body').addClass('shade user-tweets');
			
			$('<li id="loading">loading user tweets...</li>').appendTo('#mosaic');
			
			if (!$('#highlight').length) {
				$('<div id="highlight" class="widget clearfix"></div>').appendTo('#widgets');
			}
			var html = this.getTitleHtml('User') + '\
				<img src="' + picture_url + '" />\
				<p class="user-name">' + name + '</p>\
				<button class="delete">delete user</button>';
			$('<article class="user clearfix" data-id="' + userId + '">' + html + '</article>').appendTo('#widgets #highlight');
			$('.user .delete').click( function() {
				this.deleteUser(userId, function() {
					this.reset();
				}.bind(this) );
			}.bind(this) );
			
			// load
			this.load('/dashboard/tweets-by-username.php', {'user_name' : name}, function(data) {
				
				$('#loading').remove();
				
				if (!$('#tweet-list').length) {
					var title = 'Found ' + data.count;
					if (data.count > data.total) title += '/' + data.total;
					title = this.getTitleHtml(title + ' by «' + name + '»');
					$('<div id="tweet-list" class="widget list clearfix">' + title + '</div>').appendTo('#widgets');
				}
				
				if (!data.total) {
					return;
				}
				
				for (i = 0; i < data.tweets.length; i++) {
					$('<article class="tweet clearfix">' + this.getTweetHtml(data.tweets[i]) + '<button class="delete" data-id="' + data.tweets[i].id + '">delete tweet</button></article>').appendTo('#widgets #tweet-list');
				}
				$('.tweet .delete').click( function(ev) {
					ev.stopPropagation();
					this.deleteTweet($(ev.target).attr('data-id'), function() {
						$(ev.target).parents('.tweet').remove();
					} );
				}.bind(this) );
			}.bind(this), 'users-by-terms' );
		},
		
		searchTweets : function(terms)
		{
			this.reset();
			
			$('body').addClass('shade tweet-list');
			
			$('<li id="loading">searching tweets...</li>').appendTo('#mosaic');
			
			this.load('/dashboard/tweets-by-terms.php', {'terms' : terms}, function(data) {
				
				$('#loading').remove();
				
				if (!$('#tweet-list').length) {
					var title = 'Found ' + data.count;
					if (data.count > data.total) title += '/' + data.total;
					title = this.getTitleHtml(title + ' tweets matching «' + terms + '»');
					$('<div id="tweet-list" class="widget list clearfix">' + title + '</div>').appendTo('#widgets');
				}
				
				if (!data.total) {
					return;
				}
				
				for (i = 0; i < data.tweets.length; i++) {
					var deleteBtn ='<button class="delete">delete tweet</button>';
					var userBtn = '<span class="user-link">all tweets by ' + data.tweets[i].userName + '</span>';
					$('<article class="tweet clearfix" data-id="' + data.tweets[i].id + '">' + this.getTweetHtml(data.tweets[i]) + userBtn + deleteBtn + '</article>').appendTo('#widgets #tweet-list');
				}
				// user-name click
				$('#tweet-list .user-link').click( function(ev) {
					ev.stopPropagation();
					var el = $(ev.target).hasClass('tweet') ? $(ev.target) : $(ev.target).parents('.tweet');
					this.showUser(el.attr('data-id'), el.find('.user-name').text(), el.find('img').attr('src'));
				}.bind(this) );
				// delete
				$('.tweet .delete').click( function(ev) {
					ev.stopPropagation();
					var el = $(ev.target).hasClass('tweet') ? $(ev.target) : $(ev.target).parents('.tweet');
					this.deleteTweet(el.attr('data-id'), function() {
						el.remove();
					} );
				}.bind(this) );
			}.bind(this), 'tweets-by-terms' );
		},
		
		deleteTweet : function(id, callback) {
			this.post('/dashboard/tweet-delete.php', { 'id' : id }, callback )
		},
		
		deleteUser : function(id, callback) {
			this.post('/dashboard/user-delete.php', { 'user_id' : id}, callback )
		},

		// ---- ajax helpers


		/**
		 * a request_key is generated if id param is given (representing a
		 * resource) if two requests are made with same id, obsolete responses
		 * will be muted
		 * 
		 * @param string url
		 * @param object params
		 * @param function callback
		 * @param string id
		 */
		load : function(url, params, callback, id, errorCallback) 
		{
			// generate a new key for this request?
			var request_key = null;
			if (id) {
				var date = new Date();
				this.load_requests[id] = request_key;
			}
			
			console.log(url, params, request_key);
			
			return $.ajax( {
				'type': 'GET',
				'url': url,
				'dataType': 'json',
				'data': params,
				'success': function(data) {
					// ignore obsolete responses
					if (id && this.load_requests[id] != request_key) return;
					// welformed data
					if (!data) {
						this.loadError('NO_DATA', data);
					}
					else if ("undefined" == typeof data.payload) {
						callback(data);
					}
					else if ("function" == typeof callback) {
						callback(data.payload);
					}
				}.bind(this),
				error: function() {
					if ("function" == typeof errorCallback) {
						errorCallback(arguments);
					}
					else this.loadError(arguments);
				}.bind(this)
			});
		},

		loadError : function() 
		{
			console.log('load fail, error:', arguments);
			$('#loading').remove();
			$('<li id="loading">not found</li>').appendTo('#mosaic');
		},
		
		post : function(url, params, callback, noFeedback) 
		{
			console.log(url, params);
			
			$.ajax( {
				'type': 'POST',
				'url': url,
				'dataType': 'json',
				'data': params,
				'success': function(data) {
					var noFeedback = ("undefined" == typeof noFeedback) ? noFeedback : false; 
					if (!data) {
						this.postError('NO_DATA', data);
					}
					else if ("undefined" == typeof data.code) {
						this.postError('NO_CODE', data);
					}
					else if (data.code != 1) {
						this.postError('ERROR_CODE:' + data.code, data);
					}
					else if ("function" == typeof callback) {
						var payload = ("undefined" != typeof data.payload) ? data.payload : {}; 
						callback(payload);
					}
					if (!noFeedback) {
						var message = ("undefined" != typeof data.msg) ? data.msg : null;
						this.postSuccess(message);
					}
				}.bind(this),
				error: function() {
					this.postError(arguments);
				}.bind(this)
			});
		},
		
		postError : function() 
		{
			console.log('post fail, error:', arguments);
		},
		
		postSuccess : function(message) 
		{
			console.log('post ok, message:', message);
		},

		
		// urls
		
		/**
		 * freshness, appends ?r=123456789 to file url to bypass browser cache
		 * 
		 * @param string fileUrl
		 * @return string
		 */
		freshUrl : function(fileUrl) 
		{
			return fileUrl + '?r=' + new Date().getTime();
		},


		/**
		 * updates urlFragment
		 * 
		 * @param string fragment
		 */
		urlFragment : function(fragment) 
		{
			var href = document.location.href;
			href = href.replace(/#.*$/, '');
			document.location.href = href + '#' + fragment;
		},


		/**
		 * @return string
		 */
		getUrlFragment : function() 
		{
			var href = document.location.href;
			var pos = href.indexOf('#');
			return (pos > 0) ? href.substring(pos + 1) : null
		}

	});


	$.fn.extend( {
	
		/**
		 * input with focus/unfocus handling of default text
		 */
		inputDefault : function(options) 
		{
			var defaults = {
				'defaultText': false
			};
			
			options = $.extend(defaults, options);
			
			return this.each( function() {
				if (!options.defaultText) options.defaultText = this.value;
				this.value = options.defaultText;
				$(this).bind('focus', function(ev) {
					if (ev.target.value == options.defaultText) ev.target.value = '';
				} );
				$(this).bind('blur', function(ev) {
					if (!ev.target.value) ev.target.value = options.defaultText;
				} );	
			} );
		},


		/**
		 * element toggles class + data attr on click activate/deactivate
		 * callbacks
		 */
		toggleSwitch: function(options) 
		{
			var defaults = {
				'onDeactivate': null,
				'onActivate': null
			}
			
			options = $.extend(defaults, options);
			
			return this.each( function() {
				
				this.toggleOff = function(el)
				{
					$(this).removeClass('toggle-on').addClass('toggle-off').attr('data-toggle', 'off');
					if ("function" == typeof options.onDeactivate) options.onDeactivate();
				}
				
				this.toggleOn = function(el)
				{
					$(this).removeClass('toggle-off').addClass('toggle-on').attr('data-toggle', 'on');
					if ("function" == typeof options.onActivate) options.onActivate();
				}
				
				$(this).addClass('toggle');
				this.toggleOff();
				
				$(this).click(function() { 
				
					var state = $(this).attr('data-toggle');
					
					if (state == 'off') {
						this.toggleOn();
					}
					else this.toggleOff();
				} );
			} );
		},
		
		/**
		 * collection of sort control buttons with asc/desc toggle applies to
		 * all children elements classed .sort-button onChange callback
		 */
		sortControl : function(options) 
		{
			var defaults = {
				'default': 'name',
				'onChange': false
			}
			
			options = $.extend(defaults, options);
			
			return this.each( function() {
				
				var activeField = options['default'];
				var direction = 'asc';
				var control = this;
				
				$(this).find('.sort-button').click( function() {
					switchTo($(this).attr('data-field'));
				} );
				
				var switchTo = function(activateField)
				{
					if (activateField != activeField)
					{
						activeField = activateField;
						direction = 'asc';
					}
					else {
						direction = (direction == 'asc' ? 'desc' : 'asc');
					}
					
					update();
					if ("function" == typeof options.onChange) options.onChange();
				}

				var update = function()
				{
					$(control).attr('data-field', activeField);
					$(control).attr('data-direction', direction);
					$(control).find('.sort-button').each( function() {
						$(this).removeClass('sort-asc');
						$(this).removeClass('sort-desc');
						if (activeField == $(this).attr('data-field')) {
							$(this).addClass('sort-' + direction);
						}
					} );
				} 
				
				update();
			} );
		},

		/**
		 * input aware of state and ENTER key onChange: callback onEnter:
		 * callback
		 */
		inputState : function(options) 
		{
			var defaults = {
				'minChars': 3,
				'timeoutMs': 500,
				'onChange': false,
				'onEnter': false 
			}
			
			options = $.extend(defaults, options);
			
			return this.each( function() {
				
				var inputText = '';
				
				var timeout = null;
				
				$(this).addClass('auto-filter');
				
				$(this).bind('keyup', function(event) {
					
					var text = $(this).val();
					
					if (event.keyCode == '13') {
						event.preventDefault();
						if (inputText != text) {
							inputText = text;
							if ("function" == typeof options.onChange) {
								options.onChange(text);
							}
						}
						if ("function" == typeof options.onEnter) {
							options.onEnter(text);
						}
					} 
					else if (!text.length || text.length >= options.minChars) {
						window.clearTimeout(timeout);
						timeout = window.setTimeout( function() { 
							if (inputText == text) return;
							inputText = text;
							if ("function" == typeof options.onChange) {
								options.onChange(text);
							}
						}.bind(this), options.timeoutMs);
					}
				} );
			} );
		},
		
		/**
		 * input aware of state and ENTER key onChange: callback onEnter:
		 * callback
		 */
		inputAutoComplete : function(options) 
		{
			var defaults = {
				'minChars': 3,
				'timeoutMs': 500,
				'onChange': false,
				'onEnter': false
			}
			
			options = $.extend(defaults, options);
			
			return this.each( function() {
				
				var inputText = '';
				
				var timeout = null;
				
				var match = null;
				
				var updateResults = function(results)
				{
					var cnt = 0;
					for (id in results) {
						cnt++;
						if (id == inputText) {
							match = results[id];
						}
					}
					if (cnt == 1) {
						match = results[id];
					}
					else if (!cnt) {
						match = null;
					}
				}
				
				$(this).addClass('auto-filter');
				
				$(this).bind('keyup', function(event) {
					
					var text = $(this).val();
					
					if (event.keyCode == '13') {
						event.preventDefault();
						if (inputText != text) {
							inputText = text;
							if ("function" == typeof options.onChange) {
								options.onChange(inputText, function(results) {
									updateResults(results);
									if ("function" == typeof options.onEnter) {
										options.onEnter(match, inputText);
									}
								} );
							}
						}
						else if ("function" == typeof options.onEnter) {
							options.onEnter(match, inputText);
						}
					} 
					else if (!text.length || text.length >= options.minChars) {
						window.clearTimeout(timeout);
						timeout = window.setTimeout( function() { 
							if (inputText == text) return;
							inputText = text;
							if ("function" == typeof options.onChange) {
								options.onChange(inputText, function(results) {
									updateResults(results);
								} );
							}
						}.bind(this), options.timeoutMs);
					}
				} );
			} );
		}
	} );


	/**
	 * add support for prototype like bind()
	 */
	Function.prototype.bind = function()
	{
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
				 for(var j=0, total=tmpArgs.length; j < total; j++) {
					 leArgument2.push(tmpArgs[j]);
				 }
				 return leArgument2;
			 }(arguments)));
		};
	};

})(jQuery);

/** 
 * jQuery (methods) 
 */
(function($) {	
	
	$.fn.extend({
		
		/**
		 * rolls numbers to 
		 * 
		 * @param integer to
		 * @param integer milisecs 
		 * @param function callback function(value) returns formatted value
		 * @param integer iteration (used on recursion)
		 */
		rollNumbers : function(to, milisecs, formatCallback, iteration) 
		{
			var target = $(this).attr('data-value');
			var frameMsecs = 200;
			// first loop
			if (!iteration) {
				if (to == target) return;
				$(this).attr('data-value', to);
				iteration = Math.ceil(milisecs / frameMsecs);
				if (!to) to = 0;
				this.to = to;
				this.num = parseInt(this.text(), 10);
				if (!this.num) this.num = 0;
			}
			// drop obsoletes
			else if (to != target);
			iteration--;
			
			if (!iteration) {
				this.num = to;
			}
			else {
				var direction = this.num > to ? 1 : -1;
				var delta = Math.abs(to - this.num);
				delta = Math.floor(delta * 0.51);
				if (this.num < to) {
					this.num += delta;
					if (this.num > to) this.num = to;
				}
				else if (this.num > to) {
					this.num -= delta;
					if (this.num < to) this.num = to;
				}
			}
			var text = ('function' == typeof formatCallback) ? formatCallback(this.num) : this.num;
			this.text(text);
			if (this.num != to) {
				setTimeout( function(el, to, milisecs, formatCallback, iteration) {
					el.rollNumbers(to, milisecs, formatCallback, iteration);
				}, frameMsecs, this, to, milisecs, formatCallback, iteration);
			}
		}
	});
})(jQuery);

