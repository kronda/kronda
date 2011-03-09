jQuery(document).ready(function($) {

	//start up lightbox
    jQuery('.home #slider a').lightBox();
	
	//start up slide show
	jQuery("#slider").easySlider({
		auto: false, 
		continuous: true,
		numeric: true,
		speed: 400,
		pause: 4000
	});

	//get tweets via jquery.tweet.js
	jQuery("#tweet").tweet({
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
	jQuery('#showbusy').toggle(function(){
		jQuery('#social').hide();
		jQuery('#busymeter').slideDown();
	}, function(){
		jQuery('#social').fadeIn('3000');
		jQuery('#busymeter').slideUp('fast');
	});
	
	jQuery('.home #footer, .home #colophon').css('height', function(){
		return $('#blogteaser').height() + 10 + 'px';
	});

});