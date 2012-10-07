if(typeof jQuery == 'function')
  jQuery(function($){
      $("a[href^='http://']").each(function(){
          var thehref = $(this).attr('href');
          if(!thehref.match(window.location.host))
              $(this).attr('target', '_blank').addClass('external_link');
    });
  });