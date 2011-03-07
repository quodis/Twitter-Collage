/**
 * Firefox 4 Twitter Party
 * Design and development by Mozilla, Quodis
 * http://www.mozilla.com
 * http://www.quodis.com
 * 
 * Licensed under a Creative Commons Attribution Share-Alike License v3.0 http://creativecommons.org/licenses/by-sa/3.0/ 
 */

/**
 * mock support for window.console
 */
if (!window.console || !window.console.log) {
	window.console = {};
	window.console.log = function(whatever) {};
	window.console.dir = function(whenever) {};
}




/**
 * NOTE: jQuery handling of scroll position has poor bruwser-compatibility
 * borrowed from http://www.softcomplex.com/docs/get_window_size_and_scrollbar_position.html
 * 
 * @return integer
 */
function f_scrollLeft() {
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
function f_scrollTop() {
	return f_filterResults (
		window.pageYOffset ? window.pageYOffset : 0,
		document.documentElement ? document.documentElement.scrollTop : 0, document.body ? document.body.scrollTop : 0
	);
}
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
function f_filterResults(n_win, n_docel, n_body) {
	var n_result = n_win ? n_win : 0;
	if (n_docel && (!n_result || (n_result > n_docel)))
		n_result = n_docel;
return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}


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
 * Get object's length
 */
function objectLength(obj) {
	var length = 0,
		key;
	for (key in obj) {
		if (obj.hasOwnProperty(key)) length += 1;
	}
	return length;
}
/**
 * Add array.shuffle
 */
Array.prototype.shuffle = function (){ 
	for(var rnd, tmp, i=this.length; i; rnd=parseInt(Math.random()*i, 10), tmp=this[--i], this[i]=this[rnd], this[rnd]=tmp);
};


/**
 * PHP JS
 */
function number_format (number, decimals, dec_point, thousands_sep) {
    // http://kevin.vanzonneveld.net
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     bugfix by: Michael White (http://getsprink.com)
    // +     bugfix by: Benjamin Lupton
    // +     bugfix by: Allan Jensen (http://www.winternet.no)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +     bugfix by: Howard Yeend
    // +    revised by: Luke Smith (http://lucassmith.name)
    // +     bugfix by: Diogo Resende
    // +     bugfix by: Rival
    // +      input by: Kheang Hok Chin (http://www.distantia.ca/)
    // +   improved by: davook
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Jay Klehr
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Amir Habibi (http://www.residence-mixte.com/)
    // +     bugfix by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +      input by: Amirouche
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: number_format(1234.56);
    // *     returns 1: '1,235'
    // *     example 2: number_format(1234.56, 2, ',', ' ');
    // *     returns 2: '1 234,56'
    // *     example 3: number_format(1234.5678, 2, '.', '');
    // *     returns 3: '1234.57'
    // *     example 4: number_format(67, 2, ',', '.');
    // *     returns 4: '67,00'
    // *     example 5: number_format(1000);
    // *     returns 5: '1,000'
    // *     example 6: number_format(67.311, 2);
    // *     returns 6: '67.31'
    // *     example 7: number_format(1000.55, 1);
    // *     returns 7: '1,000.6'
    // *     example 8: number_format(67000, 5, ',', '.');
    // *     returns 8: '67.000,00000'
    // *     example 9: number_format(0.9, 0);
    // *     returns 9: '1'
    // *    example 10: number_format('1.20', 2);
    // *    returns 10: '1.20'
    // *    example 11: number_format('1.20', 4);
    // *    returns 11: '1.2000'
    // *    example 12: number_format('1.2000', 3);
    // *    returns 12: '1.200'
    // *    example 13: number_format('1 000,50', 2, '.', ' ');
    // *    returns 13: '100 050.00'
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
     prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
     sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
     dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
     s = '',
     toFixedFix = function (n, prec) {
         var k = Math.pow(10, prec);
         return '' + Math.round(n * k) / k;
     };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
     s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
     s[1] = s[1] || '';
     s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function date (format, timestamp) {
    // http://kevin.vanzonneveld.net
    // +   original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
    // +      parts by: Peter-Paul Koch (http://www.quirksmode.org/js/beat.html)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: MeEtc (http://yass.meetcweb.com)
    // +   improved by: Brad Touesnard
    // +   improved by: Tim Wiel
    // +   improved by: Bryan Elliott
    //
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: David Randall
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +  derived from: gettimeofday
    // +      input by: majak
    // +   bugfixed by: majak
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Alex
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +   improved by: Thomas Beaucourt (http://www.webapp.fr)
    // +   improved by: JT
    // +   improved by: Theriault
    // +   improved by: Rafał Kukawski (http://blog.kukawski.pl)
    // %        note 1: Uses global: php_js to store the default timezone
    // %        note 2: Although the function potentially allows timezone info (see notes), it currently does not set
    // %        note 2: per a timezone specified by date_default_timezone_set(). Implementers might use
    // %        note 2: this.php_js.currentTimezoneOffset and this.php_js.currentTimezoneDST set by that function
    // %        note 2: in order to adjust the dates in this function (or our other date functions!) accordingly
    // *     example 1: date('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400);
    // *     returns 1: '09:09:40 m is month'
    // *     example 2: date('F j, Y, g:i a', 1062462400);
    // *     returns 2: 'September 2, 2003, 2:26 am'
    // *     example 3: date('Y W o', 1062462400);
    // *     returns 3: '2003 36 2003'
    // *     example 4: x = date('Y m d', (new Date()).getTime()/1000); 
    // *     example 4: (x+'').length == 10 // 2009 01 09
    // *     returns 4: true
    // *     example 5: date('W', 1104534000);
    // *     returns 5: '53'
    // *     example 6: date('B t', 1104534000);
    // *     returns 6: '999 31'
    // *     example 7: date('W U', 1293750000.82); // 2010-12-31
    // *     returns 7: '52 1293750000'
    // *     example 8: date('W', 1293836400); // 2011-01-01
    // *     returns 8: '52'
    // *     example 9: date('W Y-m-d', 1293974054); // 2011-01-02
    // *     returns 9: '52 2011-01-02'
    var that = this,
        jsdate, f, formatChr = /\\?([a-z])/gi,
        formatChrCb,
        // Keep this here (works, but for code commented-out
        // below for file size reasons)
        //, tal= [],
        _pad = function (n, c) {
            if ((n = n + "").length < c) {
                return new Array((++c) - n.length).join("0") + n;
            } else {
                return n;
            }
        },
        txt_words = ["Sun", "Mon", "Tues", "Wednes", "Thurs", "Fri", "Satur", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        txt_ordin = {
            1: "st",
            2: "nd",
            3: "rd",
            21: "st",
            22: "nd",
            23: "rd",
            31: "st"
        };
    formatChrCb = function (t, s) {
        return f[t] ? f[t]() : s;
    };
    f = {
        // Day
        d: function () { // Day of month w/leading 0; 01..31
            return _pad(f.j(), 2);
        },
        D: function () { // Shorthand day name; Mon...Sun
            return f.l().slice(0, 3);
        },
        j: function () { // Day of month; 1..31
            return jsdate.getDate();
        },
        l: function () { // Full day name; Monday...Sunday
            return txt_words[f.w()] + 'day';
        },
        N: function () { // ISO-8601 day of week; 1[Mon]..7[Sun]
            return f.w() || 7;
        },
        S: function () { // Ordinal suffix for day of month; st, nd, rd, th
            return txt_ordin[f.j()] || 'th';
        },
        w: function () { // Day of week; 0[Sun]..6[Sat]
            return jsdate.getDay();
        },
        z: function () { // Day of year; 0..365
            var a = new Date(f.Y(), f.n() - 1, f.j()),
                b = new Date(f.Y(), 0, 1);
            return Math.round((a - b) / 864e5) + 1;
        },

        // Week
        W: function () { // ISO-8601 week number
            var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3),
                b = new Date(a.getFullYear(), 0, 4);
            return 1 + Math.round((a - b) / 864e5 / 7);
        },

        // Month
        F: function () { // Full month name; January...December
            return txt_words[6 + f.n()];
        },
        m: function () { // Month w/leading 0; 01...12
            return _pad(f.n(), 2);
        },
        M: function () { // Shorthand month name; Jan...Dec
            return f.F().slice(0, 3);
        },
        n: function () { // Month; 1...12
            return jsdate.getMonth() + 1;
        },
        t: function () { // Days in month; 28...31
            return (new Date(f.Y(), f.n(), 0)).getDate();
        },

        // Year
        L: function () { // Is leap year?; 0 or 1
            return new Date(f.Y(), 1, 29).getMonth() === 1 | 0;
        },
        o: function () { // ISO-8601 year
            var n = f.n(),
                W = f.W(),
                Y = f.Y();
            return Y + (n === 12 && W < 9 ? -1 : n === 1 && W > 9);
        },
        Y: function () { // Full year; e.g. 1980...2010
            return jsdate.getFullYear();
        },
        y: function () { // Last two digits of year; 00...99
            return (f.Y() + "").slice(-2);
        },

        // Time
        a: function () { // am or pm
            return jsdate.getHours() > 11 ? "pm" : "am";
        },
        A: function () { // AM or PM
            return f.a().toUpperCase();
        },
        B: function () { // Swatch Internet time; 000..999
            var H = jsdate.getUTCHours() * 36e2,
                // Hours
                i = jsdate.getUTCMinutes() * 60,
                // Minutes
                s = jsdate.getUTCSeconds(); // Seconds
            return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
        },
        g: function () { // 12-Hours; 1..12
            return f.G() % 12 || 12;
        },
        G: function () { // 24-Hours; 0..23
            return jsdate.getHours();
        },
        h: function () { // 12-Hours w/leading 0; 01..12
            return _pad(f.g(), 2);
        },
        H: function () { // 24-Hours w/leading 0; 00..23
            return _pad(f.G(), 2);
        },
        i: function () { // Minutes w/leading 0; 00..59
            return _pad(jsdate.getMinutes(), 2);
        },
        s: function () { // Seconds w/leading 0; 00..59
            return _pad(jsdate.getSeconds(), 2);
        },
        u: function () { // Microseconds; 000000-999000
            return _pad(jsdate.getMilliseconds() * 1000, 6);
        },

        // Timezone
        e: function () { // Timezone identifier; e.g. Atlantic/Azores, ...
            // The following works, but requires inclusion of the very large
            // timezone_abbreviations_list() function.
/*              return this.date_default_timezone_get();
*/
            throw 'Not supported (see source code of date() for timezone on how to add support)';
        },
        I: function () { // DST observed?; 0 or 1
            // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
            // If they are not equal, then DST is observed.
            var a = new Date(f.Y(), 0),
                // Jan 1
                c = Date.UTC(f.Y(), 0),
                // Jan 1 UTC
                b = new Date(f.Y(), 6),
                // Jul 1
                d = Date.UTC(f.Y(), 6); // Jul 1 UTC
            return 0 + ((a - c) !== (b - d));
        },
        O: function () { // Difference to GMT in hour format; e.g. +0200
            var a = jsdate.getTimezoneOffset();
            return (a > 0 ? "-" : "+") + _pad(Math.abs(a / 60 * 100), 4);
        },
        P: function () { // Difference to GMT w/colon; e.g. +02:00
            var O = f.O();
            return (O.substr(0, 3) + ":" + O.substr(3, 2));
        },
        T: function () { // Timezone abbreviation; e.g. EST, MDT, ...
            // The following works, but requires inclusion of the very
            // large timezone_abbreviations_list() function.
/*              var abbr = '', i = 0, os = 0, default = 0;
            if (!tal.length) {
                tal = that.timezone_abbreviations_list();
            }
            if (that.php_js && that.php_js.default_timezone) {
                default = that.php_js.default_timezone;
                for (abbr in tal) {
                    for (i=0; i < tal[abbr].length; i++) {
                        if (tal[abbr][i].timezone_id === default) {
                            return abbr.toUpperCase();
                        }
                    }
                }
            }
            for (abbr in tal) {
                for (i = 0; i < tal[abbr].length; i++) {
                    os = -jsdate.getTimezoneOffset() * 60;
                    if (tal[abbr][i].offset === os) {
                        return abbr.toUpperCase();
                    }
                }
            }
*/
            return 'UTC';
        },
        Z: function () { // Timezone offset in seconds (-43200...50400)
            return -jsdate.getTimezoneOffset() * 60;
        },

        // Full Date/Time
        c: function () { // ISO-8601 date.
            return 'Y-m-d\\Th:i:sP'.replace(formatChr, formatChrCb);
        },
        r: function () { // RFC 2822
            return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
        },
        U: function () { // Seconds since UNIX epoch
            return jsdate.getTime() / 1000 | 0;
        }
    };
    this.date = function (format, timestamp) {
        that = this;
        jsdate = ((typeof timestamp === 'undefined') ? new Date() : // Not provided
        (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
        new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
        );
        return format.replace(formatChr, formatChrCb);
    };
    return this.date(format, timestamp);
}
/**
 * Firefox 4 Twitter Party
 * Design and development by Mozilla, Quodis
 * http://www.mozilla.com
 * http://www.quodis.com
 * 
 * Licensed under a Creative Commons Attribution Share-Alike License v3.0 http://creativecommons.org/licenses/by-sa/3.0/ 
 */
var party = party || {};

(function () {
	/**
	tile object structure:
		id AS i
		position AS p
		twitterId AS w
		userName AS u
		imageUrl AS m
		createdTs AS c
		contents AS n
		imageData AS d
	*/
	var initial_draw_timer,
		loading_message_timer,
		polling_timer,
		tile_counter = 0,
		auto_bubble_timer,
		auto_bubble_index = 0,
		visible_tiles = {},
		visible_tiles_random = [],
		autoplay_pool = [], // Index of the most recent tiles
		new_tiles = [], // Tiles got from the server in "real-time"
		total_positions = 0,
		draw_tiles_timer,
		performance = {},
		tile_hover = null,
		colors = ['#ACE8F1', '#2D4891', '#F7DC4B', '#C52F14'], // Light-blue, Dark-blue, Yellow, Dark-orange
		counter = {
			canvas: null,
			current: 0,
			increment: 0
		},
		search = {
			input: null,
			original_caption: null
		},
		state = {
			active_bubble_pos: 0,
			keep_bubble_open: false,
			last_id: 0,
			last_page: 0,
			mosaic_offset: {},
			initial_tiles_per_frame_incremental: 1,
			draw_new_tiles_every: 0,
			draw_new_tiles_every_counter: 0,
			total_tiles: 0,
			last_tile_drawn_pos: -1
		},
		performance_settings = {
			high: {
				initial_frames_per_second: 24,
				initial_tiles_per_frame: 10,
				new_tiles_per_second: 8
			},
			medium: {
				initial_frames_per_second: 12,
				initial_tiles_per_frame: 20,
				new_tiles_per_second: 4
			},
			low: {
				initial_frames_per_second: 1,
				initial_tiles_per_frame: 200,
				new_tiles_per_second: 1
			}
		};
	
	function create_urls(input) {
		return input
		.replace(/(ftp|http|https|file):\/\/([\S]+(\b|$))/gim, '<a href="$&" class="my_link" target="_blank">$2</a>')
		.replace(/([^\/])(www[\S]+(\b|$))/gim, '$1<a href="http://$2" class="my_link" target="_blank">$2</a>')
		.replace(/(^|\s)@(\w+)/g, '$1<a href="http://twitter.com/$2" class="my_link" target="_blank">@$2</a>')
		.replace(/(^|\s)#(\S+)/g, '$1<a href="http://search.twitter.com/search?q=%23$2" class="my_link" target="_blank">#$2</a>');
	}
	
	function tileHtml(tile) {
		var position,
			index;
			
		// Make sure this is an existing data entry
		if (!tile) {
		  return '';
		}
		
		// Cache the tile's position
		position = tile.p;
		index = party.mosaic.index[position];
		if (!index) {
		  return '';
		}
		
		// Add it to the HTML to draw
		return '<div class="tile" id="' + position + '" style="background-image:url(data:image/gif;base64,' + tile.d + '); left: ' + (index[0]*12) + 'px; top: ' + (index[1]*12) + 'px;"></div>';
		
	}
	
	// Draw the Initial Mosaic
	function initialDraw() {

		// Create an array for the random order
		var i,
			f;
		for (i = 0; i < total_positions; i += 1) {
			visible_tiles_random.push(i);
		}
		// Randomize!
		visible_tiles_random.shuffle();
		// Calculate the number of frames
		f = parseInt(total_positions/party.performance.initial_tiles_per_frame, 10);
		// Calculate the counter increment on each frame
		counter.increment = parseInt(state.total_tiles/f, 10);
		// Start the recursive call for each frame
		initial_draw_timer = window.setInterval(initialDrawFrame, (1000/party.performance.initial_frames_per_second) );
	}
	
	// Construct each frame for the initial draw
	function initialDrawFrame() {
		
		var tiles_to_draw = "",
			i = 0,
			j = 0,
			p;
		
		// Next time draw one tile more towards initial_tiles_per_frame
		if (state.initial_tiles_per_frame_incremental < party.performance.initial_tiles_per_frame) {
			state.initial_tiles_per_frame_incremental  += 0.02;
		}
		
		j = (tile_counter + state.initial_tiles_per_frame_incremental);
		
		// Draw tiles_per_frame tiles and draw them
		for (i = tile_counter; i < j; i += 1) {
			p = visible_tiles_random[i];
			tiles_to_draw = tiles_to_draw + tileHtml(visible_tiles[p]);
		}
		tile_counter = i;
		
		// Check if anything to draw was processed
		if (tiles_to_draw) {

			// Draw the tiles and proceed
			party.canvas.append(tiles_to_draw);
			// Update counter
			if (counter.current < state.total_tiles) {
				counter.current += counter.increment;
				setCounter();
			}
			
		} else {
			
			// No Tiles were built - task is complete
			window.clearInterval(initial_draw_timer);
			// Set counter to last id
			counter.current = parseInt(state.total_tiles, 10);
			setCounter();
			startAutoBubble();
			// Start the recursive "tile updater"
			draw_tiles_timer = window.setInterval(drawNewTiles, (1000/party.performance.new_tiles_per_second));
		}
		
	}
	
	// Set the counter to a new int
	function setCounter() {
		counter.canvas.text(number_format(counter.current, 0, party.l10n.dec_point, party.l10n.thousands_sep));
	}
	
	
	
	// Randomize and show the loading message
	function loadingShow() {
		var loading_messages = $.makeArray($('#loading li')),
			loading_message_index = 0,
			loadingMessage;
			
		loading_messages.shuffle();
		
		// Iterate through the loading messages
		loadingMessage = function() {
			// Advance in the array - if at the end, restart
			$(loading_messages[loading_message_index]).hide();
			loading_message_index += 1;
			if (loading_message_index >= loading_messages.length) {
				loading_message_index = 0;
			}
			$(loading_messages[loading_message_index]).show();
		}
		
		// Loop through the array
		loadingMessage();
		loading_message_timer = window.setInterval(loadingMessage, (party.loading_message_seconds * 1000));
		
	}
	
	// Hide the loading message
	function loadingHide(){
		window.clearInterval(loading_message_timer);
		$('#loading').remove();
	}
	
	
	// First to be called
	function init() {
		var bubble,
		    imgsToPreload = [
		        'assets/images/layout/bubble-light-blue.png',
		        'assets/images/layout/bubble-dark-blue.png',
		        'assets/images/layout/bubble-yellow.png',
		        'assets/images/layout/bubble-dark-orange.png'
		    ];
		
		//Bubble image preloading
		for (var i=imgsToPreload.length; i--; ) {
		    (function(){
		        var img = new Image();
		        img.src = imgsToPreload[i];
		    })();
		}
		
		// Check the browser's performance
		party.performance = party.performance_settings.high;
		if ($.browser.msie) {
			party.performance = party.performance_settings.medium;
		} else if ($.browser.mozilla) {
			// Remove the download button if this is already firefox >= 4
			if (window.navigator.userAgent.search('Firefox/4') != -1) {
				$('#download').remove();
			}
		}
		
		// Cache DOM elements
		counter.canvas = $('#twitter-counter dd span');
		tile_hover = $('#tile-hover');
		party.canvas = $('#mosaic');
		bubble = $('#bubble');
		party.bubble = {
			container: bubble,
			username_a: bubble.find('h1 a'),
			avatar_a: bubble.find('a.twitter-avatar'),
			avatar_img: bubble.find('a.twitter-avatar > img'),
			time: bubble.find('time'),
			time_a: bubble.find('time > a'),
			p: bubble.find('p')
		};
		state.mosaic_offset = party.canvas.offset();
		
		// Setup the search functionality
		searchInit();
		// Get the page of visible tiles
		getVisibleTiles();
		// Bind the hover action
		
		party.canvas.bind('mouseleave', function(){
		   party.autoBubbleStartTimer = setTimeout(startAutoBubble, 1000);
		});
		
        party.canvas.bind('mousemove', function(ev) {
			var x,
				y,
				pos,
				offset = party.canvas.offset();
				
			clearTimeout(party.mousemoveTimer);
			clearTimeout(party.autoBubbleStartTimer);

			if (state.keep_bubble_open) {
				return;
			}

			x = Math.ceil((ev.clientX + f_scrollLeft() - offset.left) / 12) - 1;
			y = Math.ceil((ev.clientY + f_scrollTop() - offset.top) / 12) - 1;
            if (x < 0 || y < 0) {
				return;
			}

            pos = party.mosaic.grid[x][y];
            
            party.mousemoveTimer = setTimeout(function(){
                // is valid x,y
                if (pos) {
    				// Check if this is not the already opened bubble
    				if (state.active_bubble_pos != pos.i) {
    					stopAutoBubble();
    					state.active_bubble_pos = pos.i;
    					showBubble(pos.i);
    				}
                } else {
    				// Not a tile
    				startAutoBubble();
    			}
            }, 50);			
        });
		// Hide the bubble if the mouse leavese the mosaic
		// party.canvas.bind('mouseout', function() {
		// 	if (state.keep_bubble_open || auto_bubble_timer) {
		// 		return;
		// 	}
		// 	hideBubble();
		// 	startAutoBubble();
		// });
		// Keep bubble open/hover
		tile_hover.bind('click', function(event){
			state.keep_bubble_open = true;
			event.stopPropagation();
			return false;
		});
		// Close the bubble
		party.canvas.bind('click', hideBubble);
		party.bubble.container.bind('click', function(event){
			if (!state.keep_bubble_open) {
				state.keep_bubble_open = true;
			}
			
			stopAutoBubble();
						
			event.stopPropagation();
			return (event.target.nodeName.toLowerCase() == 'a');
		});
		
		//Proxying bubble mouseenter and mouseleave to above click events
		party.bubble.container.bind('mouseenter', function() {
		    tile_hover.trigger('click');
		});
		
		party.bubble.container.bind('mouseleave', function() {
		    party.canvas.trigger('click');
		});
	}
	
	function searchInit() {
		// Cache the search input DOM
		search.input_dom = $('#search-input');
		// Store the original search input caption
		search.original_caption = search.input_dom.val();
		
		search.input_dom.focus(function(){
			if ($(this).val() === search.original_caption) {
				$(this).val('');
			}
		});
		
		search.input_dom.blur(function(){
			if ($(this).val() == '') {
				$(this).val(search.original_caption);
			}
		});
		
		$('#search-box').submit(function() {
			var user_name = search.input_dom.val();
			if (user_name == "") {
				return false;
			}
		  	// Show loading
			$('#search-box button').addClass('loading');
			// Request server
			$.ajax({
				url: '/tiles-by-username.php',
				type: 'GET',
				dataType: 'json',
				data: {user_name: user_name},
				success: processSearchResult
			});
			
			return false;
		});
	}
	
	
	function processSearchResult(data){
		var new_tile,
			pos;
		// Hide Loading
		$('#search-box button').removeClass('loading');

		if (data.payload.total == 0) {
			// No results!
			$('#search-box .error').fadeIn('fast');
			window.setTimeout(function(){
				$('#search-box .error').fadeOut('fast');
			}, 3 * 1000);
			return;
		}

		// Found a result
		new_tile = data.payload.tiles[0];
		pos = new_tile.p;
		// Write the new tile over the visible
		$.extend(visible_tiles[pos], new_tile);
		// Show and persist it!
		stopAutoBubble();
		state.keep_bubble_open = true;
		showBubble(pos);
		// Clean memory
		data = null;
	}
	
	function showAutoBubble() {
		var t;
		
		t = autoplay_pool[auto_bubble_index];
		if (!t) {
			auto_bubble_index = 0;
			return;
		}
		auto_bubble_index += 1;
		showBubble(t.position);
	}
	
	function startAutoBubble() {
		// Start it only if it's not already started
		if (!auto_bubble_timer) {
			showAutoBubble();
			auto_bubble_timer = setInterval(showAutoBubble, party.auto_bubble_seconds * 1000);
		}
	}
	
	function stopAutoBubble() {
		clearInterval(auto_bubble_timer);
		auto_bubble_timer = null;
	}
	
	function showBubble(pos) {
		var x,
			y,
			tile,
			b = party.bubble,
			position_class,
			position_css,
			i,
			g,
			formatted_date;
		
		tile = visible_tiles[pos];
		if (!tile || !b) {
			return;
		}
		
		i = party.mosaic.index[pos];
		if (!i) {
			return;
		}
		x = i[0];
		y = i[1];
		
		g = party.mosaic.grid[x][y];
		if (!g) {
			return;
		}
		
		// Choose the arrow's position
		if (y > 24) {
			if (x > 24) {
				position_class = "bottom-right";
				position_css = {
					top: '',
					right: (564 - (x * 12)) + 'px',
					bottom: (532 - (y * 12)) + 'px',
					left: ''
				};
			} else {
				position_class = "bottom-left";
				position_css = {
					top: '',
					right: '',
					bottom: (532 - (y * 12)) + 'px',
					left: ((x * 12) + 2) + 'px'
				};
			}
		} else {
			if (x > 24) {
				position_class = "top-right";
				position_css = {
					top: ((y * 12) - 16) + 'px',
					right: (564 - (x * 12)) + 'px',
					bottom: '',
					left: ''
				};
			} else {
				position_class = "top-left";
				position_css = {
					top: ((y * 12) - 16) + 'px',
					right: '',
					left: ((x * 12) + 8) + 'px',
					bottom: ''
				};
			}	
		}
		
		// Hide previous
		b.container.hide();
		tile_hover.hide();
		
		// Create a fake "zoomed tile" element
		tile_hover.attr('src', 'data:image/gif;base64,' + tile.d);
		tile_hover.css({
			'left': (x*12) + 'px',
			'top': (y*12) + 'px'
		});
		
		// Localize stuff
		formatted_date = date(party.l10n.date_format, tile.c);
		
		// Change the bubble
		b.username_a.text(tile.u).attr('href', 'http://twitter.com/' + tile.u);
		b.avatar_a.attr('title', tile.u).attr('href', 'http://twitter.com/' + tile.u);
		b.p.html(create_urls(tile.n));
		b.time_a.attr('href', 'http://twitter.com/' + tile.u + '/status/' + tile.w).text(formatted_date);
		b.time.attr('datetime', formatted_date);
		b.avatar_img.hide();
		b.container.css(position_css).removeClass().addClass('bubble ' + position_class + ' color-' + g.r);
		
		//Show the image on a small timeout window
		party.showBubbleImageTimer = setTimeout(function(){
		    b.avatar_img.attr('src', tile.m);
		    b.avatar_img.load(function(){
		        $(this).fadeIn('fast');
		    })
		    party.showBubbleImageTimer = null;
		}, 500);
		
		// Show
		b.container.show();
		tile_hover.show();
		
	}
	
	function hideBubble() {
		state.active_bubble_pos = 0;
		state.keep_bubble_open = false;
		party.bubble.container.hide();
		tile_hover.hide();
		
		//Clean the image showing timer if bubble is closed in the meanwhile
		if (party.showBubbleImageTimer) {
		    clearTimeout(party.showBubbleImageTimer);
		    party.showBubbleImageTimer = null;
		}
	}
	
	// Reload the whole page
	function reloadPage() {
		window.location = window.location;
	}
	
	// Get the last complete page of tiles
	function getVisibleTiles() {
		
		// Check if we have a complete page. If not, try again later
		if (party.state.last_page == 0) {
			setTimeout(reloadPage, 60 * 1000);
			return;
		}
		
		// Show the loading
		loadingShow();
		
		// Request URL
		var url = party.store_url + '/pages/page_' + party.state.last_page + '.json';
		
		// Get the first visible page from server
		$.getJSON(url, function(data) {
			
			// Hide the Loading
			loadingHide();
			
			// Update last id
			if (data.last_id > state.last_id) {
				state.last_id = data.last_id;
			}
			// Write the data locally
			visible_tiles = data.tiles;
			// 
			
			var key;
			for (key in visible_tiles) {
				if (visible_tiles[key].p) {
					autoplay_pool.push({id: parseInt(visible_tiles[key].i,10), position: parseInt(visible_tiles[key].p,10)});
				}
			}
			total_positions = autoplay_pool.length;
			// Put the newest on top
			autoplay_pool.sort(function(a, b) {
				return b.id - a.id;
			});
			// Keep the newest 200
			autoplay_pool = autoplay_pool.slice(0, 199);
			state.total_tiles = parseInt(party.state.last_page * total_positions, 10);
			
			// Draw the mosaic!
			initialDraw();
			
			// Start real-time polling
			startPolling();
			
			// Clean memory
			data = null;

		});
	}
	
	function drawNewTiles() {
		
		// Get a random position
		var pos,
			new_tile,
			idx,
			grid,
			css_changes,
			last_tile;

		// Priority to new tiles
		if (state.draw_new_tiles_every_counter >= state.draw_new_tiles_every) {
			new_tile = new_tiles[0];
			state.draw_new_tiles_every_counter = 0;
		}
		
		state.draw_new_tiles_every_counter += 1;
		
		if (new_tile) {
			// Get the position
			pos = parseInt(new_tile.p);
			if (!visible_tiles[pos]) {
				new_tiles.shift();
				return;
			}
			
			// Update the CSS
			css_changes = {
				'background-image': 'url(data:image/gif;base64,' + new_tile.d + ')'
			};
			// Write the new tile over the visible
			$.extend(visible_tiles[pos], new_tile);
			// Store this to the newest tiles to autoplay
			autoplay_pool.shift();
			autoplay_pool.push({id: parseInt(new_tile.i, 10), position: pos});
			// Remove this tile from the new tiles
			new_tiles.shift();
			
			counter.current += 1;
			setCounter();
		} else {
			// Choose a random position
			pos = Math.floor(Math.random() * total_positions);
			idx = party.mosaic.index[pos];
			grid = party.mosaic.grid[idx[0]][idx[1]];
			// Update the CSS
			css_changes = {
				'background-image': 'none',
				'background-color': colors[grid.r]
			};
		}

		// Update the previous tile
		if (state.last_tile_drawn_pos > -1) {
			$('#' + state.last_tile_drawn_pos).css({
				'background-image': 'url(data:image/gif;base64,' + visible_tiles[state.last_tile_drawn_pos].d + ')'
			});
		}
		
		// Save the previous tile
		state.last_tile_drawn_pos = pos;
		
		// Update the new tile
		$('#' + pos).css(css_changes);
		
	}
	
	// Start the Real-time polling
	function startPolling() {

		// Start the recursive poller
		poll();
		polling_timer = window.setInterval(poll, (party.polling_timer_seconds * 1000));
		
	}
	
	function poll() {

		$.ajax({
		  url: '/poll.php',
		  dataType: 'json',
		  data: {last_id: state.last_id},
		  success: function(data) {
			
				// Update last id
				if (data.payload.last_id > state.last_id) {
					state.last_id = data.payload.last_id;
				}
				
				// Reverse the tiles to get the newest first and append the data to the buffer
				new_tiles = new_tiles.concat(data.payload.tiles.reverse());
				// Calculate at which speed new tiles should be drawn
				state.draw_new_tiles_every = Math.round((party.performance.new_tiles_per_second * party.polling_timer_seconds) / new_tiles.length);

				// Clean memory
				data = null;
			}
		});
	}

	/**
	 * public
	 * 
	 * @return integer
	 */
	function getLastId() {
		return state.last_id;
	}
	
	
	/**
	 * public, enable dashboard ui
	 * 
	 * @return
	 */
	function pause() {
		window.clearInterval(draw_tiles_timer);
		window.clearInterval(polling_timer);
	}

	
	/**
	 * public, enable dashboard ui
	 * 
	 * @return
	 */
	function resume() {
		startPolling();
	}
	
	
	$.extend(party, {
		"loading_message_seconds": 2,
		"polling_timer_seconds": 180, 
		"auto_bubble_seconds": 7,
		"grid": [],
		"index": [],
		"init": init,
		"getLastId": getLastId,
		"pause": pause,
		"resume": resume,
		"showBubble": showBubble,
		"performance": performance,
		"performance_settings": performance_settings,
		"state": state,
		"new_tiles": new_tiles
	});
	
}());


$(document).ready(function() {
	var brand_center = 0,
		brand_total = 0;
	
	// Language chooser
	$('#flang').change(function(){
		window.location = '/' + $(this).val();
	});
	
	// Tweet popup window
	$('#twitter-counter > dl > dt > a').click(function(){
		var w = 550,
			h = 500,
			l = (window.screen.width - w)/2,
			t = (window.screen.height - h)/2;
		window.open($(this).attr('href'), 'tweet', 'left=' + l + ',top=' + t + ',width=' + w + ',height=' + h + ',toolbar=0,resizable=1');
		return false;
	});
	
	// Draw the lines on the logo
	brand_center = parseInt($('#brand em').width(), 10) + 20;
	brand_total = parseInt($('#brand p').width(), 10);
	$('#brand em').before('<span style="left:0; width:' + (brand_total-brand_center)/2 + 'px" />').fadeIn('slow');
	$('#brand em').after('<span style="right:0; width:' + (brand_total-brand_center)/2 + 'px" />').fadeIn('slow');
	
	// Let's get it started!
	party.init();
	
});