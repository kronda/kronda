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
		$('#second').hide();
		$('#first').slideDown();
	}, function(){
		$('#second').fadeIn('3000');
		$('#first').slideUp('fast');
	});
	
	$('#footer, #colophon').css('height', function(){
		return $('#first').height() + 20 + 'px';
		
	});
	
});