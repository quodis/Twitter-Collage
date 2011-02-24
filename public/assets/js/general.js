  var idx = 0;
	var party = {
    canvas: null,
    cols: 48,
    rows: 47,
    tile_size: 12,
    performance_mode: false,
	  grid: [],
	  index: [],
	  tweets: [],
	  tweetsCount: [],
	  buffer: [],
	  bufferCount: 0
  }
  
  function finish() {
    $('div.tile').tipsy();
  }
  
  function addImage() {
    var tweet = party.tweets[idx],
      position,
      index,
      x = 0,
      y = 0;
    
    idx += 1;
    
    // Make sure this is an existing data entry
	  if (!tweet) {
		  return;
	  }
	  // Cache the tweets position
	  position = tweet.position;
	  index = party.index[position];
	  
	  if (!index) {
		  return;
	  }
	  
    x = index.x * 12;
    y = index.y * 12;
	  
	  party.buffer.push('<div class="tile" style="background-image:url(data:image/jpg;base64,' + tweet.imageData + '); left: ' + x + 'px; top: ' + y + 'px;" original-title="' + tweet.contents + '"></div>');
 		party.bufferCount += 1;
 		
    if (party.bufferCount >= 20) {
      addBufferToDom();
    }
    
    // Is this the end of the line?
    if (idx == party.tweetsCount) {
      addBufferToDom();
      finish();
    }
  }
  
  function addBufferToDom() {
    party.canvas.append(party.buffer.join(' '));
    party.bufferCount = 0;
    party.buffer = [];
  }
  
  $(document).ready(function() {
    // cache dom element
    party.performance_mode = $.browser.msie;
	  party.canvas = $('#mosaic');
	
	  function start() {
	  
		  var funcArr = [];
      
      for (var x = 0; x < (party.tweetsCount/10); x += 1) {
        funcArr.push(function(){
          addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
          addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
		      addImage();
        });
	    }
      multiStep(funcArr, 20);
    }

    /*
    * Multistep task processing
    *
    * @param Array     steps
    * @param Array     args
    * @param Function  callback
    * @param int       interval
    */
    function multiStep(steps, interval, args, callback) {
      var tasks = steps.concat(), //clone array
        task;
      setTimeout(function(){
        //execute next task
        task = tasks.shift();
        if (task && typeof task == 'function') {
          task.apply(null, args || []);
        }

        //determine if there's more tasks
        if (tasks.length > 0) {
          setTimeout(arguments.callee, interval);
        } else {
          if (typeof callback ==='function') {
            callback();
          }
        }
      }, interval);
    }

    $.getJSON('page.php', function(data) {
      party.tweets = data.payload.tweets;
      party.tweetsCount = party.tweets.length;
		  start();
    });
	});
