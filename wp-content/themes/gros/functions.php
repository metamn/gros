<?php


// Remove the default add to cart 
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
