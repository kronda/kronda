if( typeof jQuery == 'function' )
  jQuery(function($){
    $('#toplevel_page_developer-tools a.toplevel_page_developer-tools').click( function(){
      document.cookie = 'developer_tools_current_menu_item=home';
    });
  });