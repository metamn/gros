<?php
if ('eshop-settings.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
     
/*
See eshop.php for information and license terms
*/
if (file_exists(ABSPATH . 'wp-includes/l10n.php')) {
    require_once(ABSPATH . 'wp-includes/l10n.php');
}
else {
    require_once(ABSPATH . 'wp-includes/wp-l10n.php');
}


add_filter('query_vars', 'add_settings_query_vars');

require (WP_PLUGIN_DIR."/eshop/eshop-settings-class.php");
require (WP_PLUGIN_DIR."/eshop/eshop-settings-extends.php");
include_once (WP_PLUGIN_DIR.'/eshop/cart-functions.php');
include_once (WP_PLUGIN_DIR.'/eshop/eshop-base-functions.php');

define('ESHOP_SETTINGS_PAGE_NAME', 'eshop-settings.php');


if (!class_exists('eshop_metabox_class')) {
	wp_die('Unable to find the metabox class');
}
if(isset($_POST['eshop-action-status']))
	$_GET['mstatus']=$_POST['eshop-action-status'];
if (isset($_GET['mstatus']) )
	$action_status = esc_attr($_GET['mstatus']);
else
	$_GET['mstatus'] = $action_status = 'General';

switch($action_status){
	case ('General'):
		$eshop_metabox_plugin = new eshop_setting_general_class();
		break;
	case ('Merchant'):
		$eshop_metabox_plugin = new eshop_setting_merchant();
		break;
	case ('Discounts'):
		$eshop_metabox_plugin = new eshop_setting_discount_class();
		break;
	case ('Downloads'):
		$eshop_metabox_plugin = new eshop_setting_downloads_class();
		break;
	case ('Pages'):
		$eshop_metabox_plugin = new eshop_setting_pages_class();
		break;
	case ('Base'):
		$eshop_metabox_plugin = new eshop_setting_base_class();
		break;
	case ('Tax'):
		$eshop_metabox_plugin = new eshop_setting_tax();
		break;
}


function add_settings_query_vars($aVars) {
	$aVars[] = "mstatus";    // represents the name of the product category as shown in the URL
	return $aVars;
}
?>