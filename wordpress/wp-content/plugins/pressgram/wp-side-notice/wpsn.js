jQuery(document).ready(function($) {

	$('.wpsn-notice').hover(
		function() { $('.wpsn-outer-container').css('min-width', '800px') },
		function() { $('.wpsn-outer-container').not('.active').css('min-width', '62px') }
	);

	$('.wpsn-notice').click(function() {
		$(this).toggleClass('selected');
		$('.wpsn-outer-container').toggleClass('active');
	}).find('a','img').click(function(e) {
		e.stopPropagation();
	});
});