var party = party || {};

Array.prototype.shuffle = function (){ 
	for(var rnd, tmp, i=this.length; i; rnd=parseInt(Math.random()*i, 10), tmp=this[--i], this[i]=this[rnd], this[rnd]=tmp);
};

(function () {
	var initial_draw_timer,
		loading_message_timer,
		polling_timer,
		loading_message_index,
		tile_counter = 0,
		frame_counter = 0,
		visible_tiles = {},
		visible_tiles_random = [],
		hidden_tiles = {},
		newest_tiles = [], // Index of the most recent tiles
		new_tiles = [], // Tiles got from the server in "real-time"
		total_positions = 0,
		draw_tiles_timer,
		performance = {},
		tile_hover = null,
		draw_new_tiles_every = 1,
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
			initial_tiles_per_second_incremental: 1,
			draw_new_tiles_every_counter: 0
		},
		available_performances = {
			high: {
				initial_frames_per_second: 24,
				initial_tiles_per_frame: 10,
				new_tiles_per_second: 10
			},
			medium: {
				initial_frames_per_second: 12,
				initial_tiles_per_frame: 20,
				new_tiles_per_second: 5
			},
			low: {
				initial_frames_per_second: 1,
				initial_tiles_per_frame: 200,
				new_tiles_per_second: 3
			}
		};
	
	/**
	 * NOTE: jQuery handling of scroll position has poor bruwser-compatibility
	 * borrowed from
	 * http://www.softcomplex.com/docs/get_window_size_and_scrollbar_position.html
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
	 * borrowed from
	 * http://www.softcomplex.com/docs/get_window_size_and_scrollbar_position.html
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
	
	function f_filterResults(n_win, n_docel, n_body) 
	{
	    var n_result = n_win ? n_win : 0;
	    if (n_docel && (!n_result || (n_result > n_docel)))
	        n_result = n_docel;
	    return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
	}
	
	function tileHtml(tile) {
		var position,
			index;
			
		// Make sure this is an existing data entry
		if (!tile) {
		  return '';
		}
		
		// Cache the tile's position
		position = tile.position;
		index = party.mosaic.index[position];
		if (!index) {
		  return '';
		}
		
		// Add it to the HTML to draw
		return '<div class="tile" id="' + position + '" style="background-image:url(data:image/gif;base64,' + tile.imageData + '); left: ' + (index.x*12) + 'px; top: ' + (index.y*12) + 'px;"></div>';
	}
	
	// Draw the Initial Mosaic
	function initialDraw() {
		
		// Create an array for the random order
		var i;
		for (i = 0; i < total_positions; i += 1) {
			visible_tiles_random.push(i);
		}
		// Randomnize!
		visible_tiles_random.shuffle();
		// Calculate the number of frames
		counter.increment = parseInt(total_positions/party.performance.initial_frames_per_second, 10);
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
			state.initial_tiles_per_frame_incremental += 0.02;
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
			counter.current += counter.increment;
			setCounter();
			// Another frame completed
			frame_counter += 1;
			
		} else {
			
			// No Tiles were built - task is complete
			window.clearInterval(initial_draw_timer);
			// Set counter to last id
			counter.current = parseInt(state.last_id, 10);
			setCounter();
			// Start the recursive "tile updater"
			draw_tiles_timer = window.setInterval(drawNewTiles, (1000/party.performance.new_tiles_per_second));
		}
		
	}
	
	// Set the counter to a new int
	function setCounter() {
		counter.canvas.text(counter.current);
	}
	
	// Iterate through the loading messages
	function loadingMessage() {
		
		// Set the loading text
		$('#loading').text(party.loading_messages[loading_message_index]);
		
		// Advance in the array - if at the end, restart
		loading_message_index += 1;
		if (loading_message_index >= party.loading_messages.length) {
			loading_message_index = 0;
		}
		
	}
	
	// Randomnize and show the loading message
	function loadingShow() {
		
		// Show the loading DOM element
		$('#loading').show();
		
		// Set a random first loading message
		loading_message_index = Math.floor(Math.random() * party.loading_messages.length);
		
		// Loop through the array
		loadingMessage();
		loading_message_timer = window.setInterval(loadingMessage, (party.loading_message_seconds * 1000));
		
	}
	
	// Hide the loading message
	function loadingHide(){
		$('#loading').hide();
		window.clearInterval(loading_message_timer);
	}
	
	
	// First to be called
	function init() {
		var bubble;
		// Check the browser's performance
		if ($.browser.msie) {
			party.performance = party.available_performances.low;
		} else if ($.browser.mozilla) {
			// Remove the download button if this is already firefox >= 4
			if ($.browser.version >= 4) {
				$('#download').remove();
			}
		} else {
			party.performance = party.available_performances.high;
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
        party.canvas.bind('mousemove', function(ev) {
            var x,
				y,
				pos;
			
			if (state.keep_bubble_open) {
				return;
			}
			
			x = Math.ceil((ev.clientX + f_scrollLeft() - state.mosaic_offset.left) / 12) - 1;
			y = Math.ceil((ev.clientY + f_scrollTop() - state.mosaic_offset.top) / 12) - 1;
            if (x < 0 || y < 0) {
				return;
			}
			
            pos = party.mosaic.grid[x][y];

            // is valid x,y
            if (pos) {
				// Check if this is not the already opened bubble
				if (state.active_bubble_pos != pos.i) {
					state.active_bubble_pos = pos.i;
					showBubble(pos.i);
				}
            } else {
				// Not a tile
				hideBubble();
			}
        });
		// Hide the bubble if the mouse leavese the mosaic
		party.canvas.bind('mouseout', function() {
			if (state.keep_bubble_open) {
				return;
			}
			hideBubble();
		});
		// Keep bubble open/hover
		tile_hover.bind('click', function(){
			state.keep_bubble_open = !state.keep_bubble_open;
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
	}
	
	function showBubble(pos) {
		var x,
			y,
			tile,
			b = party.bubble,
			position_class,
			position_css,
			i,
			g;
		
		tile = visible_tiles[pos];
		if (!tile || !b) {
			return;
		}
		
		i = party.mosaic.index[pos];
		if (!i) {
			return;
		}
		x = i.x;
		y = i.y;
		
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
		tile_hover.attr('src', 'data:image/gif;base64,' + tile.imageData);
		tile_hover.css({
			'left': (x*12) + 'px',
			'top': (y*12) + 'px',
			'border-color': 'rgb(' + g.c.join(',') + ')'
		});
		
		// Change the bubble
		b.username_a.text(tile.userName).attr('href', 'http://twitter.com/' + tile.userName);
		b.avatar_a.attr('title', tile.userName).attr('href', 'http://twitter.com/' + tile.userName);
		b.avatar_img.attr('src', tile.imageUrl);
		b.p.text(tile.contents);
		b.time_a.attr('href', 'http://twitter.com/' + tile.userName + '/status/' + tile.twitterId).text(tile.createdDate);
		b.time.attr('datetime', tile.createdDate);
		b.container.css(position_css).removeClass().addClass('bubble ' + position_class + ' color-' + g.r);
		
		// Show
		b.container.show();
		tile_hover.show();
		
	}
	
	function hideBubble() {
		state.active_bubble_pos = 0;
		state.keep_bubble_open = false;
		party.bubble.container.hide();
		tile_hover.hide();
	}
	
	// Get an object's length
	function objectLength(obj) {
		var length = 0,
			key;
	    for (key in obj) {
	        if (obj.hasOwnProperty(key)) length += 1;
	    }
	    return length;
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
			
			// Get the invisible tiles page from the server
			getHiddenTiles();
			
			// Update last id
			if (data.last_id > state.last_id) {
				state.last_id = data.last_id;
			}
			// Write the data locally
			visible_tiles = data.tiles;
			newest_tiles = data.newest_tiles;
			total_positions = objectLength(visible_tiles);
			
			// Draw the mosaic!
			initialDraw();
			
		});
	}
	
	// Get the previous
	function getHiddenTiles() {

		// Check if we have a second complete page. If not, try again later
		if ((party.state.last_page-1) == 0) {
			setTimeout(reloadPage, 60 * 1000);
			return;
		}
		
		// Request URL
		var url = party.store_url + '/pages/page_' + (party.state.last_page-1) + '.json';
		
		// Get the previous completed page
		$.getJSON(url, function(data) {

			// Update last id
			if (data.last_id > state.last_id) {
				state.last_id = data.last_id;
			}
			
			// Write the data locally
			hidden_tiles = data.tiles;

			// Start the Real-time polling
			startPolling();
			
		});
	}
	
	function drawNewTiles() {
		
		// Get a random position
		var pos,
			old_visible,
			new_tile,
			i;
			
		// Priority to new tiles
		if (state.draw_new_tiles_every_counter === draw_new_tiles_every) {
			new_tile = new_tiles[0];
			state.draw_new_tiles_every_counter = 0;
		}
		state.draw_new_tiles_every_counter += 1;
		
		if (new_tile) {
			// Get the position
			pos = new_tile.position;
			// Check if we should keep the visible or hidden tile from this position
			// depending on which is the most recent
			if (visible_tiles[pos].id > hidden_tiles[pos].id){
				$.extend(hidden_tiles[pos], old_visible);
			}
			// Write the new tile over the visible
			$.extend(visible_tiles[pos], new_tile);
			// Remove this tile from the new tiles
			new_tiles.shift();
			counter.current += 1;
			setCounter();
		} else {
			// Choose a random position
			pos = Math.floor(Math.random() * total_positions);
			// Copy the visible
			old_visible = $.extend({}, visible_tiles[pos]);
			// Replace the visible with the hidden
			$.extend(visible_tiles[pos], hidden_tiles[pos]);
			// Replace the hidden with the visible
			$.extend(hidden_tiles[pos], old_visible);
		}
		
		// Update the new tile
		$('#' + pos).css({
			'background-image': 'url(data:image/gif;base64,' + visible_tiles[pos].imageData + ')'
		});
		
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
				// Append the data locally
				new_tiles = new_tiles.concat(data.payload.tiles);
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
		"loading_messages": [
			"Sorting guest list alphabetically",
			"Waiting for eye-contact with club bouncer",
			"Randomnizing seating-order",
			"Syncing disco-lights to the beat",
			"Cooling drinks to ideal temperature",
			"Handing out name-tags"],
		"loading_message_seconds": 2,
		"polling_timer_seconds": 40, 
		"grid": [],
		"index": [],
		"init": init,
		"getLastId": getLastId,
		"pause": pause,
		"resume": resume,
		"showBubble": showBubble,
		"performance": performance,
		"available_performances": available_performances,
		"state": state,
		"newest_tiles": newest_tiles,
		"new_tiles": new_tiles,
		"draw_new_tiles_every": 4
	});
	
}());


$(document).ready(function() {
	
	// Let's get it started!
	party.init();

	// Resize listener
	$(window).resize(function() {
		party.state.mosaic_offset = party.canvas.offset();
	});
	
});