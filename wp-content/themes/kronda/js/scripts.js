(function($) {
	//start up lightbox
    $('#slider a').lightBox();
	
	//start up slide show
	$("#slider").easySlider({
		auto: false, 
		continuous: true,
		numeric: true,
		speed: 400,
		pause: 4000
	});
	
	//get tweets from twitter.js
	// getTwitters('tweet', { 
	// 	  id: 'ephanypdx', 
	// 	  count: 4, 
	// 	  enableLinks: true, 
	// 	  ignoreReplies: true, 
	// 	  clearContents: true,
	// 	  timeout: 10,
	// 	  template: '"%text%" <a href="http://twitter.com/%user_screen_name%/statuses/%id_str%/">%time%</a>'
	// 	});
	// 	

	//get tweets via jquery.tweet.js
	$("#tweet").tweet({
        username: "ephanypdx",
        join_text: "auto",
        avatar_size: 32,
        count: 2,
        refresh_interval: 300,
		fetch: 8,
        loading_text: "loading tweets...",
		filter: function(t){ return ! /^@\w+/.test(t["tweet_raw_text"]); },
		
    }).bind("empty", function() { $(this).append('No matching tweets found, but you can <a href="http://twitter.com/ephanypdx>follow me</a> instead.'); });

	//show and hide the busy meter graphic
	$('#showbusy').toggle(function(){
		$('#social').hide();
		$('#busymeter').slideDown();
	}, function(){
		$('#social').fadeIn('3000');
		$('#busymeter').slideUp('fast');
	});
	
	$('#footer, #footer-container').css('height', function(){
		return $('#blogteaser').height() + 20 + 'px';
		
	});
	
	
	

})(jQuery);