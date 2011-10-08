<?php
if ('eshop-uninstall.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
    
//See eshop.php for information and license terms

if (file_exists(ABSPATH . 'wp-includes/l10n.php')) {
    require_once(ABSPATH . 'wp-includes/l10n.php');
}
else {
    require_once(ABSPATH . 'wp-includes/wp-l10n.php');
}
global $wpdb;
$eshopoptions = get_option('eshop_plugin_settings');

echo '<div class="wrap"><div id="eshopicon" class="icon32"></div><h2>'.__('eShop Uninstall','eshop').'</h2>';

if(isset($_POST['delete'])){
	echo '<h3>'.__('Confirm uninstall of eShop','eshop').'</h3>';
	echo '<p>'.__('Are you really sure you want to do this? All information will be lost and this action is irreversible.','eshop').'</p>';
	?>
	<form action="plugins.php?page=eshop-uninstall.php" method="post">
	<p class="submit">
	<input type="submit" id="uninstall" name="uninstall" value="<?php _e('Confirm Uninstall','eshop'); ?>" />
	</p>
	</form>
	<?php
}elseif(isset($_POST['uninstall'])){
	//required for deleting meta - grab bfore its deleted
	$numoptions=$eshopoptions['options_num'];
	echo '<ul>';

	//tables
	$etable[] = $wpdb->prefix . "eshop_states";
	$etable[] = $wpdb->prefix . "eshop_shipping_rates";
	$etable[] = $wpdb->prefix . "eshop_rates";
	$etable[] = $wpdb->prefix . "eshop_order_items";
	$etable[] = $wpdb->prefix . "eshop_orders";
	$etable[] = $wpdb->prefix . "eshop_stock";
	$etable[] = $wpdb->prefix . "eshop_downloads";
	$etable[] = $wpdb->prefix . "eshop_download_orders";
	$etable[] = $wpdb->prefix . "eshop_countries";
	$etable[] = $wpdb->prefix . "eshop_base_products";
	$etable[] = $wpdb->prefix . "eshop_discount_codes";
	$etable[] = $wpdb->prefix . "eshop_emails";
	$etable[] = $wpdb->prefix . 'eshop_option_names';
	$etable[] = $wpdb->prefix . 'eshop_option_sets';

	foreach($etable as $table){
		//delete it
		$wpdb->query("DROP TABLE IF EXISTS $table");
	}
	echo '<li>'.__('MySQL Tables - deleted','eshop').'</li>';

	//options
	delete_option('eshop_plugin_settings');
	echo '<li>'.__('Options - deleted','eshop').'</li>';

	//meta values
	$eshopmetaary[]= '_eshop_product';
	$eshopmetaary[]= '_eshop_stock';
	$eshopmetaary[]= '_eshop_featured';
	$eshopmetaary[]= '_eshop_sale';
	foreach( $eshopmetaary as $eshopmeta) {
		delete_post_meta_by_key($eshopmeta);
	}
	echo '<li>'.__('Product Information - deleted','eshop').'</li>';

	//user meta
	$wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key = 'eshop'");
	echo '<li>'.__('User Meta - deleted','eshop').'</li>';


	//delete files
	$dloaddir=eshop_download_directory();
	if ($handle = opendir($dloaddir)) {
		// This is the correct way to loop over the directory. //
		while (false !== ($file = readdir($handle))) {
			if($file!='.' && $file !='..'){
				if(is_dir($dloaddir.$file)){
					$subdloaddir=$dloaddir.$file;
					echo '<li> sub dir '.$subdloaddir.'</li>';
					if ($subhandle = opendir($subdloaddir)) {
						// This is the correct way to loop over the directory. //
						while (false !== ($subfile = readdir($subhandle))) {
							if($subfile!='.' && $subfile !='..')
								unlink ($subdloaddir.$subfile);
						}
					}
					rmdir($subdloaddir);
				}else{
					unlink ($dloaddir.$file);
				}
			}
		}
		closedir($handle);
		rmdir($dloaddir);
		echo '<li>'.__('Files uploaded via the plugin -  deleted','eshop').'</li>';
	}

	$filedir=eshop_files_directory();
	if ($handle = opendir($filedir[0])) {
		// This is the correct way to loop over the directory. //
		while (false !== ($file = readdir($handle))) {
			if($file!='.' && $file !='..'){
				unlink ($filedir[0].$file);
			}
		}
		closedir($handle);
		rmdir ($filedir[0]);
		echo '<li>'.__('eShop template files deleted','eshop').'</li>';
	}
	//unregister widgets
	wp_unregister_sidebar_widget('eshop_widget');
	wp_unregister_sidebar_widget('eshop_pay_widget');
	wp_unregister_sidebar_widget('eshop_products_widget');
	wp_unregister_sidebar_widget('eshop_cart_widget');
	wp_unregister_sidebar_widget('eshop_search_widget');
	
	unregister_widget('eshop_widget');
	unregister_widget('eshop_pay_widget');
	unregister_widget('eshop_products_widget');
	unregister_widget('eshop_cart_widget');
	unregister_widget('eshop_search_widget');

	delete_option('widget_eshop_widget');
	delete_option('widget_eshop_pay_widget');
	delete_option('widget_eshop_products_widget');
	delete_option('widget_eshop_cart_widget');
	delete_option('widget_eshop_search_widget');
	
	echo '<li>'.__('Widgets - deleted','eshop').'</li>';

	//clear the cron
	wp_clear_scheduled_hook('eshop_event');
	//remove eshop capability
	remove_eshop_caps();
	//and finally deactivate the plugin - might cause the page to go walkabout - may need to redirect to plugins page
	deactivate_plugins('eshop/eshop.php'); //Deactivate ourself
	echo '<li>'.__('Plugin deactivated','eshop').'</li>';
	echo '</ul>';
	echo '<p><strong>'.__('eShop uninstalled.','eshop').'</strong></>';

}else{
	echo '<p><strong>'.__('Uninstalling eShop will result in the following:','eshop').'</strong></p>';
	echo '<ul>';
	echo '<li>'.__('Removal of files generated by the plugin.','eshop').'</li>';
	echo '<li>'.__('Removal of files uploaded via the plugin (downloads).','eshop').'</li>';
	echo '<li>'.__('Removal of the database tables created by the plugin.','eshop').'</li>';
	echo '<li>'.__('Removal of meta data(product information) associated with a product page.','eshop').'</li>';
	echo '<li>'.__('Removal of User meta data(address fields etc.).','eshop').'</li>';
	echo '<li>'.__('Deactivation and removal of eShop widgets.','eshop').'</li>';
	echo '<li>'.__('Deactivation of the plugin.','eshop').'</li>';
	echo '</ul>';
	echo '<p><strong>'.__('Uninstalling the plugin will not affect the following, and will therefore have to be deleted manually:','eshop').'</strong></p>';
	echo '<ul>';
	echo '<li><strong>'.__('Page content associated with products.','eshop').'</strong></li>';
	echo '<li><strong>'.__('Pages generated by the plugin.','eshop').'</strong></li>';
	echo '<li><strong>'.__('The plugin itself will not be deleted.','eshop').'</strong></li>';
	echo '</ul>';
	echo '<form action="plugins.php?page=eshop-uninstall.php" method="post"><p class="submit"><input type="submit" id="delete" class="button-primary" name="delete" value="'.__('Uninstall','eshop').'" /></p></form>';
}
echo '</div>';
function remove_eshop_caps() {
	global $wpdb, $user_level, $wp_rewrite, $wp_version;
		$role = get_role('administrator');
		if ($role !== NULL){
			$role->remove_cap('eShop');
			$role->remove_cap('eShop_admin');
		}
		$role = get_role('editor');
		if ($role !== NULL)
			$role->remove_cap('eShop');
}
?>