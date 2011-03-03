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
