jQuery.noConflict();
     
// Use jQuery via jQuery(...)
jQuery(document).ready(function(){

  // Display more images and/or details
  jQuery("#product h3").click(function() {
    jQuery(this).next().slideToggle('slow');
  });
  
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

