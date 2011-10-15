jQuery.noConflict();
     
// Use jQuery via jQuery(...)
jQuery(document).ready(function(){

  // Hover on product shows title
  jQuery("#products #product").hover(
    function () {
      jQuery(this).children("#title").fadeTo('slow', .7);
    },
    function () {
      jQuery(this).children("#title").fadeTo('slow', .0);
    }
  );  
});

