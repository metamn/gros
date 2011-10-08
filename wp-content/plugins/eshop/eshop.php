<?php
if ('eshop.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
if(!defined('ESHOP_VERSION'))
	define('ESHOP_VERSION', '6.2.10');
/*
Plugin Name: eShop for Wordpress
Plugin URI: http://wordpress.org/extend/plugins/eshop/
Description: The accessible shopping cart for WordPress 3.0 and above.
Version: 6.2.10
Author: Rich Pedley 
Author URI: http://quirm.net/

    Copyright 2007-2011  R PEDLEY  (email : rich@quirm.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
//grab all options here in one go
$eshopoptions = get_option('eshop_plugin_settings');

add_action('init','eshop_load_lang',1);
if (!function_exists('eshop_load_lang')) {
	function eshop_load_lang(){
		$eshoplanguage=apply_filters( 'eshop_language_dir',dirname( plugin_basename( __FILE__ ) ) );
		load_plugin_textdomain('eshop', false, $eshoplanguage );
		//grab all options here in one go 
		$eshopoptions = get_option('eshop_plugin_settings');
	}
}

/* eShop general (or not sure where they are utilised! */
add_action('init','eshopsession',1);
if (!function_exists('eshopsession')) {
	function eshopsession(){
	 	if(!session_id()){
	    	session_start();
    	}
    }
}
/* cron */
add_action('eshop_event', 'eshop_cron');
if (!function_exists('eshop_cron')) {
	function eshop_cron(){
		global $wpdb,$eshopoptions;
		if($eshopoptions['cron_email']!=''){
			$dtable=$wpdb->prefix.'eshop_orders';
			$max = $wpdb->get_var("SELECT COUNT(id) FROM $dtable WHERE status='Completed' OR status='Waiting'");
			if($max>0){
				$to = $eshopoptions['cron_email'];    //  your email
				$body =  __("You may have some outstanding orders to process\n\nregards\n\nYour eShop plugin",'eshop');
				$body .="\n\n".get_bloginfo('url').'/wp-admin/admin.php?page=eshop-orders.php&action=Dispatch'."\n";
				$headers=eshop_from_address();
				$subject=get_bloginfo('name').__(": outstanding orders");
				wp_mail($to, $subject, $body, $headers);
			}
		}
	}
}
add_action('init','eshop_update_routine',1);
if (!function_exists('eshop_update_routine')) {
	function eshop_update_routine(){
		$eshopoptions = get_option('eshop_plugin_settings');
		if(get_option('eshop_version')!='')
			$eshopoptions['version']=get_option('eshop_version');
		
		if ( $eshopoptions['version']=='' || $eshopoptions['version'] >= ESHOP_VERSION )
			return false;
		
		include_once('eshop-upgrade.php');
	}
}
include_once 'cart-functions.php';
/* the widget */
include_once 'eshop-widget.php';
//make sure theme thumbnail support is on, even for those themes that don't use it.
add_theme_support('post-thumbnails');

if(is_admin()){
	/* eShop ADMIN SPECIFIC HERE */
	include_once 'eshop-admin-functions.php';
	include_once 'eshop-settings.php';
	include_once 'eshop-product-entry.php';
	include_once 'eshop-dashboard.php';
	/* activations */
	register_activation_hook(__FILE__,'eshop_install');
	/*deactivation*/
	register_deactivation_hook( __FILE__, 'eshop_deactivate' );
	add_action('admin_init','eshopdata');
	add_action('admin_init', 'eshop_admin_init');
	add_action('admin_menu', 'eshop_admin');
	add_filter( 'admin_footer_text', 'eshop_admin_footer_text' );
	add_action( 'in_plugin_update_message-eshop/eshop.php', 'eShopPluginUpdateMessage' );
	add_action('wp_dashboard_setup', 'eshop_add_dashboard_widgets' );

}else{
	/* eShop Public facing only */
	include_once 'public-functions.php';
	include_once( 'eshop-shortcodes.php' );
	include_once( 'eshop-add-cart.php' );
	
	//add credits
	add_action('wp_footer', 'eshop_visible_credits');
	//process cart
	add_action ('init','eshop_cart_process');
	add_filter('the_content', 'eshop_boing');
	
	//this automatically hides the relevant pages
	add_filter('wp_list_pages_excludes', 'eshop_add_excludes');
	//fold the page menu as it is likely to get long...
	add_filter('style_loader_src','eshop_unversion');
	//removes version number from css, needed for multisite
	add_action ('init','eshop_bits_and_bobs');
	add_filter('query_vars', 'add_eshop_query_vars');
	add_action('wp', 'eshop_pre_wp_head');
	add_action('wp_print_scripts', 'eshop_wp_head_add');
	add_action('wp_print_styles', 'eshop_stylesheet');
	add_action('wp_head','eshop_ie_fix');
	new eshop_search();
	add_action('template_redirect','eshopgbase');
}
/* ajax the cart - won't allow me to put this in the above*/
if(isset($eshopoptions['ajax_cart']) && 'yes' == $eshopoptions['ajax_cart']){
	include_once 'public-functions.php';
	add_action('wp_head', 'eshop_action_javascript');
	add_action('wp_print_scripts', 'eshop_ajax_inc');
	add_action('wp_ajax_eshop_special_action', 'eshop_special_action_callback');
	add_action('wp_ajax_nopriv_eshop_special_action', 'eshop_special_action_callback');
	add_action('wp_ajax_eshop_cart', 'eshop_cart_callback');
	add_action('wp_ajax_nopriv_eshop_cart', 'eshop_cart_callback');
	if ( isset( $_GET['eshoprandom'] ) )
 		add_action( 'template_redirect', 'eshop_random' );
 	
}
//test/live mode
add_action ('init','eshop_test_or_live');
?>