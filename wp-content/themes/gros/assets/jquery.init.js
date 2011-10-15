jQuery.noConflict();
     
// Use jQuery via jQuery(...)
jQuery(document).ready(function(){

  // Highligh the active product on cursor move
  jQuery(".category #product, .single #product").hover(
    function () {
      jQuery(this).addClass('highlight');
    },
    function () {
      jQuery(this).removeClass('highlight');
    }
  );  

  // Display more images and/or details
  jQuery("#product h3").click(function() {
    jQuery(this).next().slideToggle('slow');
  });
  
  // Hover on product shows title
  jQuery(".home #product, .page #product").hover(
    function () {
      jQuery(this).children("#title").fadeTo('slow', .7);
    },
    function () {
      jQuery(this).children("#title").fadeTo('slow', .0);
    }
  );  
});

