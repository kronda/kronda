jQuery.noConflict()(function($){
	
	// start jquery flexislider
	$('.flexslider').flexslider({
		animation: "slide",
		controlsContainer: ".flexcontrols",
		slideshow: false,                //Should the slider animate automatically by default? (true/false)
    slideshowSpeed: 4000,           //Set the speed of the slideshow cycling, in milliseconds
    animationDuration: 500,         //Set the speed of animations, in milliseconds
    directionNav: false,             //Create navigation for previous/next navigation? (true/false)
    controlNav: true,               //Create navigation for paging control of each slide? (true/false)
    keyboardNav: true,              //Allow for keyboard navigation using left/right keys (true/false)
    touchSwipe: false,               //Touch swipe gestures for left/right slide navigation (true/false)
    //prevText: "Previous",           //Set the text for the "previous" directionNav item
    //nextText: "Next",               //Set the text for the "next" directionNav item
    randomize: false,               //Randomize slide order on page load? (true/false)
    slideToStart: 0,                //The slide that the slider should start on. Array notation (0 = first slide)
    pauseOnAction: false,            //Pause the slideshow when interacting with control elements, highly recommended. (true/false)
    pauseOnHover: true,            //Pause the slideshow when hovering over slider, then resume when no longer hovering (true/false)
	});
	
	
	function addThumbnails() {
		var element = $('.flex-control-nav li a');
		var baseurl = window.location.href;
	
		element.each(function(i, j){		
			$(this).css({
				'background' : 'url(' + baseurl + 'wp-content/themes/kronda/images/thumb' + (i+1) + '.jpg )',
				'background-repeat' : 'no-repeat'
				});
			// j++;
		});
	} // addThumbnails
	addThumbnails();
	
	$('.flex-control-nav').easyListSplitter({ 
			colNumber: 2,
			direction: 'vertical'
	});

	//get tweets via jquery.tweet.js
	jQuery("#tweet").tweet({
        username: "ephanypdx",
        join_text: "auto",
        avatar_size: 32,
        count: 3,
        refresh_interval: 300,
				fetch: 15,
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
	
  //alert(jQuery.browser.version);
  if (jQuery.browser.version == 7.0) {
    alert('What are ya trapped at work with some old legacy office computer? Upgrade to IE8 or 9 why don\'tcha?');
  }

// slider for contact form
 $('#text-8 h3').click(function(){
	$('#colophon .one').slideUp('slow');
});
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
})(jQuery);