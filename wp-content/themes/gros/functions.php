<?php


// Don't check the Checkout fields
add_filter('eshopCheckoutReqd','eshop_extras_required');
function eshop_extras_required($values) {
	$current_user = wp_get_current_user();
	if ( $current_user->ID != 0 ) {
	  $values[1] = $current_user->user_firstname;
	  $values[2] = $current_user->user_lastname;
	  $values[3] = $current_user->user_email;
  }	
  return $values;
}

// Remove the default 'add to cart' for posts. 
// Only logged in users will see the 'add to cart' 
remove_filter( 'the_content', 'eshop_boing' );


// Replace the nav menu items
function new_nav_menu_items($items) {
  $homelink = '<li class="menu-item"><a title="' . __('Home') . '" href="' . home_url( '/' ) . '">' . __('Home') . '</a></li>';
	
	return $homelink . $items;
}
add_filter( 'wp_nav_menu_items', 'new_nav_menu_items' );


/** Tell WordPress to run child_theme_setup()
when the 'after_setup_theme' hook is run.
*/
add_action( 'after_setup_theme', 'child_theme_setup' );
 
/** This function will hold our new calls and over-rides */
if ( !function_exists( 'child_theme_setup' ) ):
function child_theme_setup() {
 
    /*
    Add menus
    */
    register_nav_menus( array(
      'secondary' => __( 'Secondary', 'twentyeleven' ),
      'footer' => __( 'Footer', 'twentyeleven' ),
    ) );
}
endif;

?>
