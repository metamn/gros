<?php


// Don't check the Checkout fields
add_filter('eshopCheckoutReqd','eshop_extras_required');
function eshop_extras_required($values) {
	#print_r($values);
	#$values = array(array());
	#$values[1] = 'admin';
	return $values;
}

// Remove the default 'add to cart' for posts. 
// Only logged in users will see the 'add to cart' 
remove_filter( 'the_content', 'eshop_boing' );

// Replace the nav menu items
function new_nav_menu_items($items) {
  $homelink = '<li class="menu-item"><a title="' . __('Home') . '" href="' . home_url( '/' ) . '">' . __('Home') . '</a></li>';
	
	if ( is_user_logged_in() ) { 
	  return $homelink . $items;
	} else {
	  return $homelink;
	} 	
}
add_filter( 'wp_nav_menu_items', 'new_nav_menu_items' );


?>
