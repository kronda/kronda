jQuery(document).ready(function($) {

	//start up lightbox
    jQuery('.home #slider .img_holder a').lightBox();

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

  //alert(jQuery.browser.version);
  if (jQuery.browser.version == 7.0) {
    alert('What are ya trapped at work with some old legacy office computer? Upgrade to IE8 or 9 why don\'tcha?');
  }

	//noise for background
	// function generateNoise (opacity) {
	//     if (!!!document.createElement('canvas').getContext ) {
	//       return false;
	//     }
	//     var canvas = document.createElement('canvas'),
	//       ctx = canvas.getContext('2d'),
	//       x, y,
	//       r, g, b,
	//       opacity = opacity || 0.2;
	//       
	//       canvas.width = 55;
	//       canvas.height = 55;
	//       
	//     for ( x = 0; x < canvas.width; x++ ) {
	//       for ( y = 0; y < canvas.height; y++ ) {
	//         r = Math.floor( Math.random() * 255 );
	//         g = Math.floor( Math.random() * 255 );
	//         b = Math.floor( Math.random() * 255 );
	//         
	//         ctx.fillStyle = 'rgba(' + r + ',' + g + ',' + b + ',' + opacity + ')';
	//         ctx.fillRect(x, y, 1, 1);
	//       }
	//     }
	//     
	//     document.getElementById('wrapper').style.backgroundImage = "url(" + canvas.toDataURL("image/png") + ")";
	//   }
	//   generateNoise(0.07);
});