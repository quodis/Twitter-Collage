var party = party || {};

(function () {
	var initial_build_interval,
		loading_message_interval,
		polling_interval,
		loading_message_index,
		tile_counter = 0,
		frame_counter = 0,
		visible_tiles = {},
		hidden_tiles = {};
		
	// Build the Initial Mosaic
	function initialBuild() {
		
		// Start the recursive call for each frame
		initial_build_interval = setInterval(initialBuildFrame, (1000/party.initial_frames_per_second) );
		
	}
	
	// Construct each frame for the initial build
	function initialBuildFrame() {
		
		var tiles_to_draw = "",
			tile,
      position,
      index,
      x = 0,
      y = 0,
			i = 0,
			j = (tile_counter + party.initial_tiles_per_frame);
		
		// Build tiles_per_frame tiles and draw them
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
			clearInterval(initial_build_interval);
			
		}
		
	}
	
	function loadingMessage() {
		
		// Set the loading text
		$('#loading').text(party.loading_messages[loading_message_index]);
		
		// Advance in the array - if at the end, restart
		loading_message_index += 1;
		if (loading_message_index >= party.loading_messages.length) {
			loading_message_index = 0;
		}
		
	}
	
	function loadingShow() {
		
		// Show the loading DOM element
		$('#loading').show();
		
		// Set a random first loading message
		loading_message_index = Math.floor(Math.random() * party.loading_messages.length);
		
		// Loop through the array
		loading_message_interval = setInterval(loadingMessage, (party.loading_message_seconds * 1000) );
		
	}
	
	function loadingHide() {
		$('#loading').hide();
		clearInterval(loading_message_interval);
	}
	
	function init() {
		// Check the browser's performance
		party.performance_mode = $.browser.msie;
		// Cache the canvas
		party.canvas = $('#mosaic');
		// Get the page of visible tiles
		getVisibleTiles();
	}
	
	function getVisibleTiles() {
		// Show the loading
		loadingShow();
		// Get the first visible page from server
		$.ajax({
		  url: 'page.php',
		  dataType: 'json',
		  data: {page: 0},
		  success: function(data) {
				// Hide the Loading
				loadingHide();
				// Get the invisible tiles page from the server
				getHiddenTiles();
				// Write the data locally
				visible_tiles = data.payload.tweets;
				console.log('Got ' + visible_tiles.length + ' visible tiles');
				// Build the mosaic!
				initialBuild();
			}
		});
	}
	
	function getHiddenTiles() {
		$.ajax({
		  url: 'page.php',
		  dataType: 'json',
		  data: {page: -1},
		  success: function(data) {
				// Write the data locally
				hidden_tiles = data.payload.tweets;
				console.log('Got ' + hidden_tiles.length + ' hidden tiles');
				// Initiate the Real-time polling
				polling_interval = setInterval(poll, (polling_interval_seconds * 1000));
			}
		});
	}
	
	function poll() {
		
	}
	
	$.extend(party, {
		"initial_frames_per_second": 24,
		"initial_tiles_per_frame": 10,
		"initialBuild": initialBuild,
		"loading_messages": [
			"Sorting guest list alphabetically",
			"Waiting for eye-contact with club bouncer",
			"Randomnizing seating-order",
			"Syncing disco-lights to the beat",
			"Cooling drinks to ideal temperature",
			"Handing out name-tags"],
		"loading_message_seconds": 2,
		"loadingShow": loadingShow,
		"loadingHide": loadingHide,
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
	party.init();
});
