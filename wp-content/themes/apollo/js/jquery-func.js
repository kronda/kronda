jQuery(document).ready(function($){
    $('.blink').
        live('focus', function() {
            if(this.title==this.value) {
                this.value = '';
            }
        }).
        live('blur', function(){
            if(this.value=='') {
                this.value = this.title;
            }
        });
        
        
    $('.apollo-newsletter-form, .apollo-contact-form').submit(function() {
    	var name = $(this).find('.name');
    	var message = $(this).find('.message');
    	var email = $(this).find('.email');
    	
    	if ( name.length && (name.val() == '' || name.val() == name.attr('title')) ) {
    		alert('Please enter your name!');
    		return false;
    	};
    	if( email.val() == '' || !email.val().match(/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/) ) {
    		alert('Please enter a valid email!');
    		return false;
    	}
    	if ( message.length && (message.val() == '' || message.val() == message.attr('title')) ) {
    		alert('Please enter your message!');
    		return false;
    	};
    	
    	var is_newsletter = $(this).is('.apollo-newsletter-form');
    	var _form = this;
    	
    	$.post( window.location.href, $(this).serialize(), function() { 
    		$(_form).find('.blink').val('');
    		if (is_newsletter) {
    			alert('You joined your newsletter successfuly');
    		} else {
    			alert('Information has been sent successfuly');
    		}
    	 });
    	return false;
	});
    
    var interval = null;
    $('.slider-hld li:gt(0)').hide();
    $('.slider-nav a').click(function() {
    		reset_interval();
    		if ($(this).is('.active') ) { return false; };
    		var index = $('.slider-nav a').index(this);
    		$('.slider-hld li.active').removeClass('active').stop(true, true).fadeOut();
    		$('.slider-hld li:eq(' + index + ')').addClass('active').stop(true, true).fadeIn();
    		$('.slider-nav a.active').removeClass('active');
    		$(this).addClass('active');
    		return false;
	});
	function next_slide() {
		var next = $('.slider-hld li.active').next();
		if ( !next.length ) {
			next = $('.slider-hld li:first');
		};
		$('.slider-hld li.active').removeClass('active').stop(true, true).fadeOut();
		next.addClass('active').stop(true, true).fadeIn();
		
		var next_nav = $('.slider-nav a.active').parent().next().find('a');
		if ( !next_nav.length ) {
			next_nav = $('.slider-nav a:first');
		};
		$('.slider-nav a.active').removeClass('active');
		next_nav.addClass('active');
	}
	function reset_interval() {
		if ( !window.autorotation ) { return; }
		window.clearInterval(interval);
		interval = window.setInterval(next_slide, window.speed);
	}
	if (window.autorotation && $('.slider-nav a').length > 1) {
		var interval = window.setInterval(next_slide, window.speed);;
	};
	function gallery_fix_thumbs_height () {
		$('.gallery-thumbs ul').each(function() {
			var max_p = 0, max_header = 0;
			$(this).find('li').each(function() {
				max_p = (max_p < $(this).find('p').height() ) ? $(this).find('p').height() : max_p ;
				max_header = (max_header < $(this).find('h3').height() ) ? $(this).find('h3').height() : max_header ;
			})
			$(this).find('li p').height(max_p);
			$(this).find('li h3').height(max_header);
		});
	}
	gallery_fix_thumbs_height();
    $('#bottom .widget_links li:first').addClass('first');
    $('#bottom .widget:last').addClass('col-last');
    $('.right-side .menu li:last').addClass('last');


});
