var party = party || {};

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
		last_id = 0, // The ID of the newest tile
		new_tiles = [], // Tiles got from the server in "real-time"
		counter_current = 0,
		counter_target = 0,
		counter_timer,
		total_positions = 0,
		draw_tiles_timer;
	
	// Draw the Initial Mosaic
	function initialDraw() {
		
		// Create an array for the random order
		var i;
		for (i = 0; i < total_positions; i += 1) {
			visible_tiles_random.push(i);
		}
		// Randomnize!
		visible_tiles_random.sort(function(){
			return (Math.round(Math.random())-0.5);
		});
		
		// Start the recursive call for each frame
		initial_draw_timer = setInterval(initialDrawFrame, (1000/party.initial_frames_per_second) );
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
	
	// Construct each frame for the initial draw
	function initialDrawFrame() {
		
		var tiles_to_draw = "",
			i = 0,
			j = (tile_counter + party.initial_tiles_per_frame),
			p;
		
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
			
			// Another frame completed
			frame_counter += 1;
			
		} else {
			
			// No Tiles were built - task is complete
			clearInterval(initial_draw_timer);
			
		}
		
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
		loading_message_timer = setInterval(loadingMessage, (party.loading_message_seconds * 1000) );
		
	}
	
	// Hide the loading message
	function loadingHide() {
		$('#loading').hide();
		clearInterval(loading_message_timer);
	}
	
	// First to be called
	function init() {
		// Check the browser's performance
		party.performance_mode = $.browser.msie;
		// Cache the canvas
		party.canvas = $('#mosaic');
		// Cache the counter DOM
		party.counter_canvas = $('#twitter-counter dd span');
		// Get the page of visible tiles
		getVisibleTiles();
		// Start the counter
		counter_timer = setInterval(counterDraw, 80);
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
	
	// Increment the counter's target
	function counterIncrement(increment_by) {
		counter_target += increment_by;
	}
	
	// Update the counter UI
	function counterDraw() {
		var dif = (counter_target - counter_current),
			inc = 1;
		
		// Check if we have anything to do
		if (dif == 0) {
			return;
		}
		
		if (dif > 2048) {
			inc = 379;
		} else if (dif > 1024) {
			inc = 197;
		} else if (dif > 512) {
			inc = 73;
		} else if (dif > 256) {
			inc = 39;
		} else if (dif > 128) {
			inc = 17;
		} else if (dif > 64) {
			inc = 3;
		}
		
		counter_current += inc;
		party.counter_canvas.text(counter_current);
		
	}
	
	// Reload the whole page
	function reloadPage() {
		window.location = window.location;
	}
	
	// Get the last complete page of tiles
	function getVisibleTiles() {
		
		// Check if we have a complete page. If not, try again later
		if (party.last_page == 0) {
			setTimeout(reloadPage, 60 * 1000);
			return;
		}
		
		// Show the loading
		loadingShow();
		
		// Request URL
		var url = party.store_url + '/pages/page_' + party.last_page + '.json';
		
		// Get the first visible page from server
		$.getJSON(url, function(data) {
			
			// Hide the Loading
			loadingHide();
			
			// Get the invisible tiles page from the server
			getHiddenTiles();
			
			// Update last id
			if (data.last_id > last_id) {
				last_id = data.last_id;
				counterIncrement(last_id);
			}
			// Write the data locally
			visible_tiles = data.tiles;
			total_positions = objectLength(visible_tiles);
			
			// Draw the mosaic!
			initialDraw();
			
		});
	}
	
	// Get the previous
	function getHiddenTiles() {

		// Check if we have a second complete page. If not, try again later
		if ((party.last_page-1) == 0) {
			setTimeout(reloadPage, 60 * 1000);
			return;
		}
		
		// Request URL
		var url = party.store_url + '/pages/page_' + (party.last_page-1) + '.json';
		
		// Get the previous completed page
		$.getJSON(url, function(data) {

			// Update last id
			if (data.last_id > last_id) {
				last_id = data.last_id;
				counterIncrement(last_id);
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
		new_tile = new_tiles[0];
		if (new_tile) {
			// Get the position
			pos = new_tile.position;
			// Check if we should keep the visible or hidden tile from this position
			// depending on which is the most recent
			if (visible_tiles[pos].id > hidden_tiles[pos].id) {
				$.extend(hidden_tiles[pos], old_visible);
			}
			// Write the new tile over the visible
			$.extend(visible_tiles[pos], new_tile);
			// Remove this tile from the new tiles
			new_tiles.shift();
			
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
		
		// Get the color of this tile
		i = party.mosaic.index[pos];
		
		var img = new Image();
		img.src = 'data:image/gif;base64,' + visible_tiles[pos].imageData;
		
		// Update the new tile
		$('#' + pos).css({
			'background-image': 'url(' + img.src + ')'
		});
		
	}
	
	// Start the Real-time polling
	function startPolling() {

		// Start the recursive "tile updater"
		draw_tiles_timer = setInterval(drawNewTiles, 100);
		// Start the recursive poller
		poll();
		polling_timer = setInterval(poll, (party.polling_timer_seconds * 1000));
		
	}
	
	function poll() {

		$.ajax({
		  url: '/poll.php',
		  dataType: 'json',
		  data: {last_id: last_id},
		  success: function(data) {
			
				// Update last id
				if (data.payload.last_id > last_id) {
					last_id = data.payload.last_id;
				}
				
				// Append the data locally
				new_tiles.concat(data.payload.tiles);
				
			}
		});
	}

	/**
	 * public
	 * 
	 * @return integer
	 */
	function getLastId() {
		return last_id;
	}
	
	
	/**
	 * public, enable dashboard ui
	 * 
	 * @return
	 */
	function pause() {
		clearInterval(draw_tiles_timer);
		clearInterval(polling_timer);
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
		"initial_frames_per_second": 24,
		"initial_tiles_per_frame": 10,
		"loading_messages": [
			"Sorting guest list alphabetically",
			"Waiting for eye-contact with club bouncer",
			"Randomnizing seating-order",
			"Syncing disco-lights to the beat",
			"Cooling drinks to ideal temperature",
			"Handing out name-tags"],
		"loading_message_seconds": 2,
		"polling_timer_seconds": 60, 
		"cols": 48,
		"rows": 47,
		"tile_size": 12,
		"grid": [],
		"index": [],
		"init": init,
		"getLastId": getLastId,
		"pause": pause,
		"resume": resume,
	});
	
}());


$(document).ready(function() {
	
	// Let's get it started!
	party.init();
	
});
