/**
 * Firefox 4 Twitter Party
 * by Mozilla, Quodis © 2011
 * http://www.mozilla.com
 * http://www.quodis.com
 * 
 * Licensed under a Creative Commons Attribution Share-Alike License v3.0 http://creativecommons.org/licenses/by-sa/3.0/ 
 */ 
(function($) {  
	
	/**
	 * mock support for window.console
	 */
	if (!window.console || !window.console.log) {
		window.console = {};
		window.console.log = function(whatever) {};
		window.console.dir = function(whenever) {};
	}

	
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
		 * element toggles class + data attr on click 
		 * activate/deactivate callbacks
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
		 * collection of sort control buttons with asc/desc toggle
		 * applies to all children elements classed .sort-button
		 * onChange callback
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
		 * input aware of state and ENTER key
		 * onChange: callback
		 * onEnter: callback
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
				
				$(this).bind('keyup', function(ev) {
					
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
		 * input aware of state and ENTER key
		 * onChange: callback
		 * onEnter: callback
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
					console.log('update', results, 'text', inputText);
					
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
					
					// TODO show/navitage matches (callback)
					
					console.log('match', match)
				}
				
				$(this).addClass('auto-filter');
				
				$(this).bind('keyup', function(ev) {
					
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
	}		

	/**
	 * Dashboard static class
	 */	
	Dashboard = {}; 
	$.extend(Dashboard, {

		// configuration
		defaults : {
			isOldBrowser : false,
			publicUrl : null
		},
		
		options : {},
		
		dialog : null,

		/**
		 * init 
		 * 
		 * @param mixed options
		 */
		init : function(options) 
		{
			
			this.options = $.extend(this.defaults, options);
			
			// prevent selection
			$('#' + this.options.pageContainer).mousedown( function(ev) { 
				if (ev.cancelable) { ev.preventDefault(); } 
				ev.stopPropagation();
			} );
			
			this.buildInterface();
			
			var confirmBrowseAway = function() {
				return "You are about to navigate away from this page. If you do that " +
				"now, you will loose all that sticker work!";
			}.bind(this);
			// bind only for working browsers
			if (!this.options.isOldBrowser) {
				window.onbeforeunload = confirmBrowseAway;
			}
		},
		
		
		// ---- ajax
		loadRequests : {},
		load : function(url, id, params, callback) 
		{
			var date = new Date();
			this.loadRequests[id] = 'id:' + Math.ceil(Math.random() * 1000) + ':' + date.getTime();
				
			var requestId = this.loadRequests[id];
			
			console.log(url, requestId);
			
			return $.ajax( {
				type: 'GET',
				url: 'json/' + url,
				dataType: 'json',
				data: params,
				success: function(data) {
					if (this.loadRequests[id] != requestId) return;
					if (!data) {
						this.loadError('NO_DATA', data);
					}
					else if ("undefined" == typeof data.code) {
						this.loadError('NO_CODE', data);
					}
					else if (data.code != 1) {
						this.loadError('ERROR_CODE:' + data.code, data);
					}
					else if ("undefined" == typeof data.payload) {
						this.loadError('NO_PAYLOAD', data);
					}
					else if ("function" == typeof callback) {
						callback(data.payload);
					}
				}.bind(this),
				error: function() {
					this.loadError(arguments);
				}.bind(this)
			});
		},

		loadError : function() 
		{
			console.log(arguments);
		},
		
		post : function(url, params, callback, noFeedback) 
		{
			$.ajax( {
				type: 'POST',
				url: 'json/' + url,
				dataType: 'json',
				data: params,
				success: function(data) {
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

		// ---- build ui


		buildInterface : function() 
		{
			// bind search user
			
			// bind search tweets
			
			// bind go to page
		},
		
		
		// ---- url fragments


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
	
	/**
	 * MovieList class
	 */	
	Dashboard.List = function(container, options) {
		
		this.defaults = {};
		
		this.options = $.extend(this.defaults, options);
		
		this.app = app;
		
		this.id = 'results';
		
		this.loaded = false;
		
		this.loadedResults = 0;
		
		/**
		 * init
		 * 
		 * @param mixed options
		 */
		this.init = function() {
			
		}
		
		this.loadResults = function(more)
		{
			if (!more) {
				this.loadedResults = 0;
			}
			
			var tagSets = this.app.getTagSetFilters();
			
			var params = {
				'offset' : this.loadedResults,
				'terms' : $("#search-q").val() != '...' ? $("#search-q").val() : '',
				'extended' : $("#search-extend").hasClass('toggle-on'),
				'has-media' : $("#search-media").attr('data-toggle-three'),
				'has-trailer' : $("#search-trailer").attr('data-toggle-three'),
				'has-poster' : $("#search-poster").attr('data-toggle-three'),
				'year-after' : $("#search-year-after").val(),
				'year-before' : $("#search-year-before").val(),
			};
			
			params.sort = $("#results-sort-control").attr('data-field') + ' ' + $("#results-sort-control").attr('data-direction');
			
			for (set in tagSets)
			{
				for (tagId in tagSets[set])
				{
					if ("undefined" == typeof params.tagFilter) params.tagFilter = {};
					
					if ("undefined" == typeof params.tagFilter[set]) params.tagFilter[set] = [];
					
					params.tagFilter[set].push({
						'id' : tagSets[set][tagId].id,
						'tagSet' : tagSets[set][tagId].tagSetId,
					});
				}
			}
			
			console.log(params);
			
			if (!more) {
				$('#results .load-more').remove();
				$('#results .ruler').remove();
				$('#results .result').remove();
			}
			
			$('#results').addClass('loading');
			
			var loadId = 'tweetList';
			
			Dashboard.load('getMovies', loadId, params, function(movies) {
				
				this.loaded = true;
				$('#results').addClass('loaded');
				
				$('#results').removeClass('loading');
				
				if (more) {
					$('#results .load-more').remove();
					var node = $('<li class="ruler"><hr/></li>').appendTo('#results .result-items');
				}
				var firstMovie, loadedMovie;
				for (i in movies.results) {
					this.loadedResults ++;
					if (!movies.results[i]) continue;
					loadedMovie = this.addMovie(movies.results[i]);
					if (!firstMovie) firstMovie = loadedMovie;
				}
				
				var loadedTotal = (parseInt(movies.offset) + parseInt(movies.count));
				var resultsLeft = movies.total - loadedTotal;
				
				if (more) {
					console.log('more');
					var scroll = $('#results .result-items').scrollTop(); 
					$('#results .result-items').animate( { 'scrollTop' : scroll + 200 }, 300, function() {
						if (more) $("#results .load-more").show();
					} );
				}
				
				if (movies.total > loadedTotal) {
					$('#results h2.result-summary').text('showing ' + loadedTotal + ' of ' + movies.total);
					var button = $('<li class="load-more"><a>load more (' + resultsLeft + ') </a></li>').appendTo('#results .result-items');
					if (more) $("#results .load-more").hide();
					button.find('a').click( function() {
						this.loadResults(true);
					}.bind(this) );
				}
				else if (movies.count && !more) {
					$('#results h2').text('showing all ' + movies.count + ' results');
				}
				else if (!more) {
					$('#results h2').text('no results');
				}
			}.bind(this) );
		}
		
		this.addMovie = function(movie)
		{
			var newMovie = new Movies.Movie('#results .result-items', movie);
			Movies.registerMovie(newMovie);
			return newMovie;
		}
		
		this.init();
	};

})(jQuery);


/**
 * http://james.padolsey.com/javascript/create-a-tinyurl-with-jsonp/
 * 
 * @param string longUrl
 * @param function successCallback
 */
function getTinyUrl(longUrl, success) 
{
	var api = 'http://json-tinyurl.appspot.com/?url=';
	var apiUrl = api + encodeURIComponent(longUrl) + '&callback=?';
	
	$.getJSON(apiUrl, function(data){
		success && success(data.tinyurl);
	});
}


/**
 * NOTE: jQuery handling of scroll position has poor bruwser-compatibility  
 * borrowed from http://www.softcomplex.com/docs/get_window_size_and_scrollbar_position.html 
 * 
 * @return integer
 */
function f_scrollLeft() 
{
	return f_filterResults (
		window.pageXOffset ? window.pageXOffset : 0,
		document.documentElement ? document.documentElement.scrollLeft : 0,
		document.body ? document.body.scrollLeft : 0
	);
}
/**
 * NOTE: jQuery handling of scroll position has poor bruwser-compatibility
 * borrowed from http://www.softcomplex.com/docs/get_window_size_and_scrollbar_position.html
 * 
 * @return integer
 */
function f_scrollTop() 
{
	return f_filterResults (
		window.pageYOffset ? window.pageYOffset : 0,
		document.documentElement ? document.documentElement.scrollTop : 0,
		document.body ? document.body.scrollTop : 0
	);
}
/**
 * borrowed from http://www.softcomplex.com/docs/get_window_size_and_scrollbar_position.html
 */
function f_clientWidth() 
{
	return f_filterResults (
		window.innerWidth ? window.innerWidth : 0,
		document.documentElement ? document.documentElement.clientWidth : 0,
		document.body ? document.body.clientWidth : 0
	);
}
function f_clientHeight() 
{
	return f_filterResults (
		window.innerHeight ? window.innerHeight : 0,
		document.documentElement ? document.documentElement.clientHeight : 0,
		document.body ? document.body.clientHeight : 0
	);
}
function f_filterResults(n_win, n_docel, n_body) 
{
	var n_result = n_win ? n_win : 0;
	if (n_docel && (!n_result || (n_result > n_docel)))
		n_result = n_docel;
	return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}
