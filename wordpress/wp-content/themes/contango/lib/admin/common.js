(function($){
	
	/** Options Tabs */
	function contangoOptionsTabs() {
		
		var relid = $.cookie( 'contango_tab_relid' );
		
		if( relid >= 1  ) {
			contangoOptionsTabControl( relid );
		} else {
			contangoOptionsTabControl( 0 );
		}
		
		$( '.contango-group-tab-link-a' ).click( function() {
			
			relid = $(this).attr( 'data-rel' );
			$.cookie( 'contango_tab_relid', relid );
			contangoOptionsTabControl( relid );		
			
		});
		
	}
	
	function contangoOptionsTabControl( relid ) {
		
		$( '.contango-group-tab' ).each( function() {
				
			if( $(this).attr( 'id' ) == relid + '_section_group' ) {					
				$(this).delay( 400 ).fadeIn( 1200 );				
			} else{					
				$(this).fadeOut( 'fast' );
			}
			
		});
		
		$( '.contango-group-tab-link-li' ).each( function() {
			
			if( $(this).attr('id') != relid + '_section_group_li' && $(this).hasClass( 'active' ) ) {					
				$(this).removeClass( 'active' );				
			}
			
			if( $(this).attr('id') == relid + '_section_group_li' ) {					 
				 $(this).addClass('active');				
			}
		
		});
		
	}
	
	/** jQuery Document Ready */
	$(document).ready(function(){		
		contangoOptionsTabs();		
	});

	/** jQuery Windows Load */
	$(window).load(function(){
	});	

})(jQuery);