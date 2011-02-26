var party = party || {};

(function () {
	
	// Build the Initial Mosaic
	function initialBuild() {
		var frame_interval,
			tile_counter = 0,
			frame_counter = 0;
			
		// Start the recursive call for each frame
		frame_interval = setInterval(initialBuildFrame, (1000/party.initial.frames_per_second) );
		
	}
	
	// Construct each frame for the initial build
	function initialBuildFrame() {
		
		var tiles_to_draw = "",
			tweets = party.tweets,
			tweet,
      position,
      index,
      x = 0,
      y = 0;

		// Build tiles_per_frame tiles and draw them
		for (; tile_counter < (tile_counter + party.initial.tiles_per_frame); tile_counter += 1) {
			
			// Make sure this is an existing data entry
			tweet = tweets[tile_counter];
		  if (!tweet) {
			  continue;
		  }
		
		  // Cache the tweets position
		  position = tweet.position;
		  index = party.index[position];
		  if (!index) {
			  continue;
		  }

			// Calculate top/left position of the tile
	    x = index.x * party.tile_size;
	    y = index.y * party.tile_size;

			// Add it to the HTML to draw
		  tiles_to_draw = '<div class="tile" style="background-image:url(data:image/gif;base64,' + tweet.imageData + '); left: ' + x + 'px; top: ' + y + 'px;" original-title="' + tweet.contents + '"></div>';
		
		}
		
		// Check if anything to draw was processed
		if (tiles_to_draw) {
			
			// Draw the tiles and proceed
			party.canvas.append(tiles_to_draw);
			// Another frame completed
			frame_counter += 1;
			
		} else {
			
			// No Tiles were built - task is complete
			clearInterval(frame_interval);
			
		}
		
	}
	
	function showLoading() {
		console.log('Loading');
	}
	
	function hideLoading() {
		console.log('Stopped loading');
	}
	
	$.extend(party, {
		"initial": {
			"frames_per_second": 12,
			"tiles_per_frame": 20,
			"build": initialBuild,
			"loadingMessages": [
				"Sorting guest list alphabetically",
				"Waiting for eye-contact with bouncer",
				"Randomnizing seating-order",
				"Syncing disco-lights to the beat",
				"Cooling drinks to ideal temperature",
				"Handing out name-tags" ]
			"showLoading": showLoading,
			"hideLoading": hideLoading
		},
		"cols": 48,
		"rows": 47,
		"tile_size": 12,
		"grid": [],
		"index": []
	});
	
}());


$(document).ready(function() {
	
	// cache dom element
	party.performance_mode = $.browser.msie;
	party.canvas = $('#mosaic');
	party.initial.showLoading();
	
	$.getJSON('page.php', function(data) {
		party.tweets = data.payload.tweets;
		party.initial.hideLoading();
		party.initial.build();
	});
	
});
