jQuery.noConflict();
     
// Use jQuery via jQuery(...)
jQuery(document).ready(function(){


  // Populate checkout adderess & email
  var userName = jQuery("#userinfo #name").html();
  var userEmail = jQuery("#userinfo #email").html();
  jQuery(".eshopcheckoutconf ul.confirm li").first().html("<span class='items fullname'>Nume: </span>" + userName);
  jQuery(".eshopcheckoutconf ul.confirm li.email").html("<span class='items'>Email: </span>" + userEmail);

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

