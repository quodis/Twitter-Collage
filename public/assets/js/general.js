var party = party || {};

(function () {
	var initial_draw_interval,
		loading_message_interval,
		polling_interval,
		loading_message_index,
		tile_counter = 0,
		frame_counter = 0,
		visible_tiles = {},
		hidden_tiles = {},
		last_id = 0, // The ID of the newest tile
		polled_tiles = {}, // Tiles got from the server in "real-time"
		counter_current
		
	// Draw the Initial Mosaic
	function initialDraw() {
		
		// Start the recursive call for each frame
		initial_draw_interval = setInterval(initialDrawFrame, (1000/party.initial_frames_per_second) );
		
	}
	
	// Construct each frame for the initial draw
	function initialDrawFrame() {
		
		var tiles_to_draw = "",
			tile,
      position,
      index,
      x = 0,
      y = 0,
			i = 0,
			j = (tile_counter + party.initial_tiles_per_frame);
		
		// Draw tiles_per_frame tiles and draw them
		for (i = tile_counter; i < j; i += 1) {
			
			// Make sure this is an existing data entry
			tile = visible_tiles[i];
			if (!tile) {
			  continue;
			}
			
			// Cache the tile's position
			position = tile.position;
			index = party.index[position];
			if (!index) {
			  continue;
			}
			
			// Calculate top/left position of the tile
			x = index.x * party.tile_size;
			y = index.y * party.tile_size;
			
			// Add it to the HTML to draw
			tiles_to_draw = tiles_to_draw + '<div class="tile" id="' + position + '" style="background-image:url(data:image/gif;base64,' + tile.imageData + '); left: ' + x + 'px; top: ' + y + 'px;"></div>';

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
			clearInterval(initial_draw_interval);
			
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
		loading_message_interval = setInterval(loadingMessage, (party.loading_message_seconds * 1000) );
		
	}
	
	// Hide the loading message
	function loadingHide() {
		$('#loading').hide();
		clearInterval(loading_message_interval);
	}
	
	// First to be called
	function init() {
		// Check the browser's performance
		party.performance_mode = $.browser.msie;
		// Cache the canvas
		party.canvas = $('#mosaic');
		// Get the page of visible tiles
		getVisibleTiles();
	}
	
	// Get the last complete page of tiles
	function getVisibleTiles() {
		
		// Request URL
		var url = party.store_url + '/pages/page_' + party.last_page + '.json';
		
		// Show the loading
		loadingShow();
		
		// Get the first visible page from server
		$.getJSON(url, function(data) {
			
			// Hide the Loading
			loadingHide();
			
			// Get the invisible tiles page from the server
			getHiddenTiles();
			
			// Update last id
			if (data.last_id > last_id) {
				last_id = data.last_id;
			}
			// Write the data locally
			visible_tiles = data.tiles;
			console.log('Got ' + visible_tiles.length + ' visible tiles');
			
			// Draw the mosaic!
			initialDraw();
			
		});
	}
	
	// Get the previous
	function getHiddenTiles() {
		
		// Request URL
		var url = party.store_url + '/pages/page_' + (party.last_page-1) + '.json';
		
		// Get the previous completed page
		$.getJSON(url, function(data) {

			// Update last id
			if (data.last_id > last_id) {
				last_id = data.last_id;
			}
			
			// Write the data locally
			hidden_tiles = data.tiles;
			console.log('Got ' + hidden_tiles.length + ' hidden tiles');
			
			// Start the Real-time polling
			startPolling();
			
		});
	}
	
	// Start the Real-time polling
	function startPolling() {
		
		// Start the recursive "tile updater"
		
		// Start the recursive poller
		polling_interval = setInterval(poll, (party.polling_interval_seconds * 1000));
		
	}
	
	function poll() {
		$.ajax({
		  url: '/poll.php',
		  dataType: 'json',
		  data: {last_id: last_id},
		  success: function(data) {
			
				// Update last id
				if (data.last_id > last_id) {
					last_id = data.last_id;
				}
				
				// Append the data locally
				$.extend(polled_tiles, data.tiles);
				console.log('Got ' + data.tiles.length + ' polled tiles');
				
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
		"polling_interval_seconds": 60, 
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
