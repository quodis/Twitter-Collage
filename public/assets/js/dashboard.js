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
			'short_stat_interval' : 10 * 1000
		},
		options : { },

		// state
		state : {
			'token' : null,
			'party_on' : 'wild',
			'guest_count' : 0,
			'last_id' : 0,
			'guest_count' : 0,
			'tweet_count' : 0,
			'short_stat_interval' : null,
			'highlighted_position' : null,
			'highlight_img_timeout' : null
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
			
			this.loadMosaic();
		},


		// ---- build ui

		/**
		 * binds, the works
		 */
		buildInterface : function() 
		{
			// show last id
			$('#guest-count span').text(this.state.guest_count);
			$('#tweet-count span').text(this.state.tweet_count);
			
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
			
			// bind load tiles buttons
			$('#force-poll-bttn').click( function(ev) {
				this.poll();
			}.bind(this) );
			$('#load-mosaic-bttn').click( function(ev) {
				this.loadMosaic();
			}.bind(this) );
			
			// bind mouse move over mosaic
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
					$('#tweet-count span').text(this.state.guest_count);
					$('#tweet-count span').text(this.state.tweet_count);
					$('#job-delay-seconds .value span').html(this.state.delay.seconds);
					$('#job-delay-tweets .value span').html(this.state.delay.tweets);
				}.bind(this) );
			}.bind(this), this.options.short_stat_interval);
		},
		
		
		// ---- state

		loadMosaic : function()
		{
			this.reset();
			
			this.tiles = null;
			this.tiles = {};
			$('#mosaic li').remove();
			
			$('<li id="loading">loading mosaic...</li>').appendTo('#mosaic');

			// load
			var url = this.freshUrl(this.options.store_url + '/mosaic.json');
			this.load(url, {}, function(data) {
				$('#loading').remove();
				var count = this.addTiles(data.tiles);
				if (!count) {
					$('<li id="loading" class="empty">no mosaic...</li>').appendTo('#mosaic');
				}
			}.bind(this), 'mosaic', function() { 
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
					$('<li id="loading" class="empty">no new results...</li>').appendTo('#mosaic');
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
			if ('undefined' == typeof tiles.length) {
				var i = 0, count = 0;
				for (i in tiles) {
					count++;
					this.addTile(tiles[i])
				}
				return count;
			}
			else {
				for (var i = 0; i < tiles.length; i++) {
					this.addTile(tiles[i]);
				}
				return tiles.length;
			}
		},

		addTile : function(tile)
		{
			this.tiles[tile.p] = tile;
			$('#' + tile.p).remove();
			var x = this.mosaic.index[tile.p][0];
			var y = this.mosaic.index[tile.p][1];
			var offsetX = this.options.tile_size * x;
			var offsetY = this.options.tile_size * y;
			var tileImg;
			if (tile.d) {
				tileImg = '<img src="data:image/gif;base64,' + tile.d + '" />';
			}
			else {
				var bgStyle = 'url(' + party.store_url + '/mosaic.jpg) no-repeat  -' + (x * 12) + 'px -' + (y * 12) + 'px';
				tileImg = '<span style="display: block; width: 12px; height: 12px; background: '  + bgStyle + '"></span>';
			}
			var html = '<li id="' + tile.p + '" style="position: absolute; top: ' + offsetY +'px; left: ' + offsetX + 'px">' + tileImg + '</li>';
			$(html).appendTo('#mosaic');
			$('#' + tile.p).click( function(ev) {
				ev.stopPropagation();
				this.openTile(tile.p);
			}.bind(this) );
		},
		
		highlightTilePos : function(position)
		{
			if (position == this.state.highlighted_position) return;
			this.state.highlighted_position = position;
				
			if ('undefined' == typeof this.tiles[position]) return;

			var tile = this.tiles[position];
			
			$('#highlight').remove();
			$('<div id="highlight" class="widget clearfix"></div>').appendTo('#widgets');
			$('#highlight .tweet').remove();
			$('#highlight .user').remove();
			var deleteBtn ='<button class="delete">delete tweet</button>';
			var userBtn = '<span class="user-link">all tweets by ' + tile.u + '</span>';
			$(this.getTitleHtml('Tweet') + '<article class="tweet clearfix">' + this.getTweetHtml(tile) + userBtn + deleteBtn + '</article>').appendTo('#widgets #highlight');
			// todo set timeout
			window.clearTimeout(this.state.highlight_img_timeout);
			this.state.highlight_img_timeout = window.setTimeout( function() {
				$('#highlight article img').attr('src', tile.m);
			}, 500 );
			$('#highlight .delete').click( function() {
				this.deleteTweet(tile.i, function() {
					this.reset();
				}.bind(this) );
			}.bind(this) );
			$('#highlight .user-link').click( function() {
				this.findUser(tile.u);
			}.bind(this)  );
		},
		
		getTitleHtml : function(text) {
			return '<h3><span class="title">' + text + '</span><span class="close">close</span></h3>';
		},
		
		getTweetHtml : function(tweet, showImage)
		{
			var date = new Date(tweet.c * 1000);
			var contents = (showImage) ? '<img src="' + tweet.m + '">' : '<img>';
			contents += '<p class="contents">' + tweet.n + '</p>\
				<p class="user-name">' + tweet.u + '</p>\
				<p class="created-date">' + date + '</p>';
			return contents;
		},
		
		getUserHtml : function(tweet)
		{
			var contents = '<img src="' + tweet.m + '">\
				<p class="user-name">' + tweet.u + '</p>';
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
		},
		
		findUser : function(user_name)
		{
			this.reset();
			
			$('body').addClass('shade user-list');
			
			$('<li id="loading">searching users...</li>').appendTo('#mosaic');
			
			this.load('/dashboard/users-by-terms.php', {'terms' : user_name}, function(data) {
				
				$('#loading').remove();
				
				if (data.total == 1) {
					this.showUser(data.users[0].u, data.users[0].m);
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
						$('<article class="user clearfix">' + this.getUserHtml(data.users[i]) + '</article>').appendTo('#widgets #user-list');
					}
					// user-name click
					$('#user-list .user').click( function(ev) {
						ev.stopPropagation();
						var el = $(ev.target).hasClass('user') ? $(ev.target) : $(ev.target).parents('.user');
						this.showUser(el.find('.user-name').text(), el.find('img').attr('src'));
					}.bind(this) );
				}
			}.bind(this), 'users-by-terms' );
		},
		
		
		showUser : function(name, picture_url)
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
			$('<article class="user clearfix">' + html + '</article>').appendTo('#widgets #highlight');
			$('.user .delete').click( function() {
				this.deleteUser(name, function() {
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
					$('<article class="tweet clearfix">' + this.getTweetHtml(data.tweets[i], true) + '<button class="delete" data-id="' + data.tweets[i].i + '">delete tweet</button></article>').appendTo('#widgets #tweet-list');
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
					var userBtn = '<span class="user-link">all tweets by ' + data.tweets[i].u + '</span>';
					$('<article class="tweet clearfix" data-id="' + data.tweets[i].i + '">' + this.getTweetHtml(data.tweets[i], true) + userBtn + deleteBtn + '</article>').appendTo('#widgets #tweet-list');
				}
				// user-name click
				$('#tweet-list .user-link').click( function(ev) {
					ev.stopPropagation();
					var el = $(ev.target).hasClass('tweet') ? $(ev.target) : $(ev.target).parents('.tweet');
					this.showUser(el.find('.user-name').text(), el.find('img').attr('src'));
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
		
		deleteUser : function(name, callback) {
			this.post('/dashboard/user-delete.php', { 'user_name' : name}, callback )
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
			
			if (!params) params = {};
			params.token = this.state.token;
			
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
					else if (data.code == 9) {
						document.location.href = '/dashboard';
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
			$('#loading').remove();
			$('<li id="loading">not found</li>').appendTo('#mosaic');
		},
		
		post : function(url, params, callback, noFeedback) 
		{
			if (!params) params = {};
			params.token = this.state.token;
			
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
					else if (data.code == 9) {
						document.location.href = '/dashboard';
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
		},
		
		postSuccess : function(message) 
		{
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

