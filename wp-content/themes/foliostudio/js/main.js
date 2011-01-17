jQuery(function($) {
	
	if (window.transition_speed == undefined || isNaN(window.transition_speed)) {
		var transition_speed = 400;
	} else {
		var transition_speed = window.transition_speed;
	}
	
	if(window.rotation_interval == undefined || isNaN(window.rotation_interval)) {
		var rotation_interval = 8000;
	} else {
		var rotation_interval = window.rotation_interval;
	}
	
	$(document).pngFix();

	$('.blink').focus(function(){
		if( $(this).attr('value') == $(this).attr('title') ) {
			$(this).attr({ 'value': '' });
		}
	}).blur(function(){
		if( $(this).attr('value') == '' ) {
			$(this).attr({ 'value': $(this).attr('title') })
		}
	});
	
	var login_open = false;
	
	$('#navigation ul li').hover(
		function() {
			if ($(this).find('ul').length > 0) {
				$(this).find('ul:eq(0)').show();
				$(this).addClass('hover');
				if(login_open == true) {
					$('#navigation ul li.login .dd').hide();
					$('#navigation ul li.login a').removeClass('hover');	
					login_open = false;				
				}

			} else if($(this).hasClass('login')) {
				$(this).find('.login-dd').show();
				$(this).find('a:eq(0)').addClass('hover');
			}
		},
		function() {
			if ($(this).find('ul').length > 0) {
				$(this).find('ul:eq(0)').hide();
			} else if($(this).hasClass('login')) {
				if (login_open == false) {
					$(this).find('a:eq(0)').removeClass('hover');
					$(this).find('.login-dd').hide();					
				}
			}
			$(this).removeClass('hover');		
		}	
	);
	
	$('.login-field input').focus(function() {
		login_open = true;
	}).blur(function() {
		login_open = false;
	});
	
	$('.login-button, .search-button').hover(
		function() {
			$(this).addClass('button-hover');
		},
		function() {
			$(this).removeClass('button-hover');
		}	
	);
	
	$(document).click(function(e) {
		if ($(e.target).parents('li.login').length == 0) {
			$('.login-dd').hide();
			$('#navigation ul li.login a.hover').removeClass('hover');
			$('.login-dd .login-field input').blur();
		}
	});
	
	
	$('.archive-list ul').hide();
	$('.archive-list li.active ul').show();
	$('.archive-list > li > a').click(function(){
		var parent = $(this).parent();
		if ( parent.hasClass('active')) { return false; };
		$('.archive-list > li').removeClass('active');
		parent.addClass('active');
		$('.archive-list li ul').slideUp();
		parent.find('ul').eq(0).slideDown();
		return false;
	});
	
	var slider_index  = 0;
	function cycle_slide_to(index) {
		clearTimeout(timeout);
		$('#cycle-slider .slides').animate({'left': -1*index*940 + 'px' }, transition_speed);
		$('#cycle-slider .slide-navigation a.active').removeClass('active');
		$('#cycle-slider .slide-navigation a:eq(' + index + ')').addClass('active');
		timeout = setTimeout(auto_cycle, rotation_interval);			
	}

	var timeout = setTimeout(auto_cycle, rotation_interval);
	
	function auto_cycle() {
		var current = $('#cycle-slider .slide-navigation a').index($('#cycle-slider .slide-navigation a.active'));
		var total = $('#cycle-slider .slide-navigation a').length;
		if (current < total - 1) {
			current+= 1;
		} else {
			current = 0;
		}
		cycle_slide_to(current);
	}

	
	$('#cycle-slider .slide-navigation a').click(function() {
		if ($(this).hasClass('active') || $('#cycle-slider .slides:animated').length > 0) {
			return false;
		};
		slider_index = $('#cycle-slider .slide-navigation a').index($(this));
		cycle_slide_to(slider_index);
		return false;
	});
	
	$('#cycle-slider .next').click(function() {
		if ($('#cycle-slider .slides:animated').length == 0) {
			slider_index ++;
			if (slider_index>$('#cycle-slider .slide').length-1) {
				slider_index = 0;
			};
			cycle_slide_to(slider_index);			
		}
		return false;
	});
	
	
	$('#cycle-slider .prev').click(function() {
		if ($('#cycle-slider .slides:animated').length == 0) {
			slider_index --;
			if (slider_index < 0) {
				slider_index = $('#cycle-slider .slide').length-1;
			};
			cycle_slide_to(slider_index);			
		}
		return false;
	});
	
	var gallery_index = 0;
	
	function gallery_show(index) {
		var current = $('#gallery-slider .slides .slide').index($('#gallery-slider .slides .slide:visible'));
		if (current != index && $('#gallery-slider .slides .slide:animated').length == 0) {
			$('#gallery-slider .slides .slide:visible').fadeOut(transition_speed);
			$('#gallery-slider .slides .slide:eq(' + index + ')').fadeIn(transition_speed);			
		}
	}
	
	$('#gallery-slider .slide-navigation .slide-thumbs a').css('opacity', 0.7);
	$('#gallery-slider .slide-navigation .slide-thumbs a:first').css('opacity', 1);	
	
	$('#gallery-slider .slide-navigation .slide-thumbs a').click(function() {	
		gallery_show( $('#gallery-slider .slide-navigation .slide-thumbs a').index($(this)) );
		$('#gallery-slider .slide-navigation .slide-thumbs a').css('opacity', 0.7);
		$(this).css('opacity', 1);
		return false;
	});
	
	$('#gallery-slider .slide-control .prev').click(function() {
		if ($('#gallery-slider .slide-thumbs-inner:animated').length > 0 || parseInt($('#gallery-slider .slide-thumbs-inner').css('left')) >= 0) {
			return false;
		}
		$('#gallery-slider .slide-thumbs-inner').animate({'left' : '+=150'}, transition_speed);
		return false;
	});
	$('#gallery-slider .slide-control .next').click(function() {
		var full_size = 150*$('#gallery-slider .slide-thumbs-inner a').length;
		var box_size = 860;
		if ( parseInt($('#gallery-slider .slide-thumbs-inner').css('left')) -150 < box_size-full_size || $('#gallery-slider .slide-thumbs-inner:animated').length > 0) {
			return false;
		};
		$('#gallery-slider .slide-thumbs-inner').animate({'left' : '-=150'}, transition_speed);
		return false;
	});
	
	$('#navigation ul.menu li').each(function() {
		if($(this).find('ul').length > 0) {
			$(this).addClass('has-dd');
		}	
		if ($(this).find('a[title="Login"]').length > 0) {
			$(this).addClass('last login');
		}
	})
	
	$('#sidebar ul li:has(.sub-menu)').css('border', 'none');
	
	$('a[rel=fancybox]').fancybox({
		'showNavArrows': false
	});
	
});