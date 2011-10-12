<?php
/*
Plugin Name: Global Hide/Remove Admin Bar Plugin
Plugin URI: http://fischercreativemedia.com/wordpress-plugins/global-hide-admin-bar-plugin/
Description: Easily add a global option to hide/remove the new Admin bar in WP 3.1+. 
Author: Don Fischer
Author URI: http://www.fischercreativemedia.com/
Donate link: http://www.fischercreativemedia.com/wordpress-plugins/donate/
Version: 1.1

Version info:
See change log in readme.txt file.

    Copyright (C) 2011 Donald J. Fischer

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

global $show_admin_bar;
add_action( 'admin_init', 'global_adminbar_settings' );
add_action( 'admin_menu', 'global_adminbar_menu' );
add_filter( 'show_admin_bar', 'global_show_hide_admin_bar' );
add_action( 'admin_print_styles-profile.php', 'global_profile_hide_admin_bar' );
add_action( 'admin_print_styles-user-edit.php', 'global_profile_hide_admin_bar' );
add_filter('plugin_row_meta', 'global_adminbar_filter_plugin_links', 10, 2);
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'global_adminbar_filter_plugin_actions');

function global_adminbar_filter_plugin_actions($links){
	$new_links = array();
	$adminlink = get_bloginfo('url').'/wp-admin/';
	$fcmlink = 'http://www.fischercreativemedia.com/wordpress-plugins';
	$new_links[] = '<a href="'.$adminlink.'options-general.php?page=admin-bar-plugin">Settings</a>';
	$new_links[] = '<a href="'.$fcmlink.'/donate/">Donate</a>';
	return array_merge($links,$new_links );
}

function global_adminbar_filter_plugin_links($links, $file){
	if ( $file == plugin_basename(__FILE__) ){
		$adminlink = get_bloginfo('url').'/wp-admin/';
		$fcmlink = 'http://www.fischercreativemedia.com/wordpress-plugins';
		$links[] = '<a href="'.$adminlink.'options-general.php?page=admin-bar-plugin">Admin Bar Settings</a>';
		$links[] = '<a target="_blank" href="'.$fcmlink.'/global-hide-admin-bar-plugin/">FAQs</a>';
		$links[] = '<a target="_blank" href="'.$fcmlink.'/donate/">Donate</a>';
	}
	return $links;
}


function global_show_hide_admin_bar($showvar) {
	global $show_admin_bar;
	if(get_option('global-admin-bar-plugin-setting')=='1'){
		$show_admin_bar = false;
		return false;
	}else{
		return $showvar ;
	}
}

function global_profile_hide_admin_bar() {
	if(get_option('global-admin-bar-plugin-user-setting')=='1'){
		echo '<style type="text/css">.show-admin-bar { display: none !important; }</style>';
	}
	return;
}

function global_adminbar_menu(){
	add_options_page( 'Global Hide/Remove Admin Bar Plugin Options', 'Admin Bar Options',10, 'admin-bar-plugin', 'gabrhp_admin_bar_page' );
}

function global_adminbar_settings() {
	register_setting( 'global-admin-bar-group', 'global-admin-bar-plugin-setting' );
	register_setting( 'global-admin-bar-group', 'global-admin-bar-plugin-user-setting' );
}

function gabrhp_admin_bar_page(){
?>
<div class="wrap">
<div class="icon32" style="<?php echo 'background: url('.WP_PLUGIN_URL.'/global-admin-bar-hide-or-remove/settings-32-icon.png) no-repeat transparent;';?>"><br /></div>
<h2>Global Hide/Remove Admin Bar Plugin Options</h2>
<form method="post" action="options.php">
    <?php settings_fields( 'global-admin-bar-group' ); ?>
    <table class="form-table">
		<tr valign="top">
			<td style="text-align: left; vertical-align: top;" colspan="2">This plugin is designed to turn off the Admin Menu Bar that is displayed for logged in users in WordPress 3.1+. It may become obsolete if WordPress ever decides to add their own global option - but for now it is very helpful to have a way to turn it off or on.<br/><br/></td>
		</tr>
		<tr valign="top">
			<td style="text-align: right; vertical-align: top;width:25px;"><input type="checkbox" name="global-admin-bar-plugin-setting" value="1" <?php if(get_option('global-admin-bar-plugin-setting')=='1'){echo 'checked="checked"' ;} ?> /></td><td style="text-align:left; vertical-align: top;line-height:14px;"><strong>Hide/Remove Admin Bar?</strong></td>
		</tr>
		<tr valign="top">
			<td style="text-align: right; vertical-align: top;width:25px;"><input type="checkbox" name="global-admin-bar-plugin-user-setting" value="1" <?php if(get_option('global-admin-bar-plugin-user-setting')=='1'){echo 'checked="checked"' ;} ?> /></td><td style="text-align:left; vertical-align: top;line-height:14px;"><strong>Hide/Remove "show admin bar" option on Profile Page?</strong></td>
		</tr>
    </table>
    <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
</form>
</div>
<?php  
}
?>