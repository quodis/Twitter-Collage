var party = party || {};

(function () {
	var initial_draw_timer,
		loading_message_timer,
		polling_timer,
		loading_message_index,
		tile_counter = 0,
		frame_counter = 0,
		visible_tiles = {},
		hidden_tiles = {},
		last_id = 0, // The ID of the newest tile
		new_tiles = {}, // Tiles got from the server in "real-time"
		counter_current = 0,
		counter_target = 0,
		counter_timer;
		
	// Draw the Initial Mosaic
	function initialDraw() {
		
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
		index = party.index[position];
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
			j = (tile_counter + party.initial_tiles_per_frame);
		
		// Draw tiles_per_frame tiles and draw them
		for (i = tile_counter; i < j; i += 1) {
			tiles_to_draw = tiles_to_draw + tileHtml(visible_tiles[i]);
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
		// TODO
		party.last_page = 4;
		// Cache the counter DOM
		party.counter_canvas = $('#twitter-counter dd span');
		// Get the page of visible tiles
		getVisibleTiles();
		// Start the counter
		counter_timer = setInterval(counterDraw, 60);
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
		
		if (dif > 3200) {
			inc = 73;
		} else if (dif > 800) {
			inc = 39;
		} else if (dif > 200) {
			inc = 17;
		} else if (dif > 50) {
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
			reloadPage();
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
			
			// Draw the mosaic!
			initialDraw();
			
		});
	}
	
	// Get the previous
	function getHiddenTiles() {
		console.log('Getting hidden tiles...');
		// Check if we have a second complete page. If not, try again later
		if ((party.last_page-1) == 0) {
			reloadPage();
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
			console.log('Got hidden tiles');
			// Start the Real-time polling
			startPolling();
			
		});
	}
	
	function drawNewTiles() {
		// What tiles to draw?
		// At what speed?
		// Hide previous
			// Choosing which to keep
			
	}
	
	// Start the Real-time polling
	function startPolling() {
		console.log('Starting polling...');
		// Start the recursive "tile updater"
		
		// Start the recursive poller
		polling_timer = setInterval(poll, (party.polling_timer_seconds * 1000));
		
	}
	
	function poll() {
		console.log('Getting poll...');
		$.ajax({
		  url: '/poll.php',
		  dataType: 'json',
		  data: {last_id: last_id},
		  success: function(data) {
			
				// Update last id
				if (data.payload.last_id > last_id) {
					last_id = data.payload.last_id;
				}
				console.log('data.payload.last_id: ' + data.payload.last_id);
				console.log('last_id: ' + last_id);
				console.log('new_tiles.length: ' + new_tiles.length);
				
				// Append the data locally
				$.extend(new_tiles, data.payload.tiles);
				
			}
		});
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
		"init": init
	});
	
}());


$(document).ready(function() {
	
	// Let's get it started!
	party.init();
	
});
