<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the administration panels.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://butlerblog.com/wp-members
 * Copyright (c) 2006-2011  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2011
 */
 

add_filter('plugin_action_links', 'wpmem_admin_plugin_links', 10, 2); 
/**
 * filter to add link to settings from plugin panel
 */
function wpmem_admin_plugin_links($links, $file)
{
	static $wpmem_plugin;
	if( !$wpmem_plugin ) $wpmem_plugin = plugin_basename('wp-members/wp-members.php');
	if( $file == $wpmem_plugin ) {
		$settings_link = '<a href="options-general.php?page=wpmem-settings">' . __('Settings') . '</a>';
		$links = array_merge( array($settings_link), $links);
	}
	return $links;
}


/**
 * include contextual help
 * @todo finish writing the contextual help in wp-members-dialogs-admin.php
 */
// add_filter('contextual_help', 'wpmem_a_help_msg', 10, 2);
// include_once('wp-members-dialogs-admin.php');


/*****************************************************
	Manage User Detail Screen
*****************************************************/


add_action('edit_user_profile', 'wpmem_admin_fields');
/**
 * add WP-Members fields to the WP user profile screen
 */
function wpmem_admin_fields()
{
	$user_id = $_REQUEST['user_id']; ?>
	
	<h3><?php _e('WP-Members Additional Fields'); ?></h3>   
 	<table class="form-table">
		<?php
		$wpmem_fields = get_option('wpmembers_fields');
		for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		
			if( $wpmem_fields[$row][6] == "n" ) { ?>    
			
				<tr>
					<th><label><?php echo $wpmem_fields[$row][1]; ?></label></th>
					<td><?php
						$val = get_user_meta( $user_id, $wpmem_fields[$row][2], 'true' );
						if( $wpmem_fields[$row][3] == 'checkbox' ) { 
							$valtochk = $val; 
							$val = $wpmem_fields[$row][7];
						}
						echo wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $val,$valtochk );
						$valtochk = '';
					?></td>
				</tr>
			
			<?php } 
		
		}

		// see if reg is moderated, and if the user has been activated		
		if( WPMEM_MOD_REG == 1 ) { 
			if( get_user_meta( $user_id, 'active', 'true' ) != 1 ) { ?>

				<tr>
					<th><label><?php _e( 'Activate this user?', 'wp-members' ); ?></label></th>
					<td><input id="activate_user" type="checkbox" class="input" name="activate_user" value="1" /></td>
				</tr>

			<?php }
		} 
		
		// if using subscription model, show expiration
		// if registration is moderated, this doesn't show if user is not active yet.		
		if( WPMEM_USE_EXP == 1 ) {
			if( ( WPMEM_MOD_REG == 1 &&  get_user_meta( $user_id, 'active', 'true' ) == 1 ) || ( WPMEM_MOD_REG != 1 ) ) { 
				wpmem_a_extenduser( $user_id );
			} 
		} ?>
		<tr>
			<th><label><?php _e( 'IP @ registration', 'wp-members' ); ?></label></th>
			<td><?php echo get_user_meta( $user_id, 'wpmem_reg_ip', 'true' ); ?></td>
		</tr>
	</table><?php
}


add_action('profile_update', 'wpmem_admin_update');
/**
 * updates WP-Members fields from the WP user profile screen
 */
function wpmem_admin_update()
{
	$user_id = $_REQUEST['user_id'];	
	$wpmem_fields = get_option('wpmembers_fields');
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		// new in 2.4 - does not include custom fields that are not used (note: WP does include its own fields even if empty)
		if( $wpmem_fields[$row][6] == "n" ) {
			update_user_meta($user_id,$wpmem_fields[$row][2],$_POST[$wpmem_fields[$row][2]]);
		}
	}
	
	if (WPMEM_MOD_REG == 1) {

		$wpmem_activate_user = $_POST['activate_user'];
		if ($wpmem_activate_user == 1) {
			wpmem_a_activate_user($user_id);
		}
	}
	
	// new in 2.4 for user expiration
	if (WPMEM_USE_EXP == 1) { 
		wpmem_a_extend_user($user_id);
	}
}


/*****************************************************
	WP-Members Settings Screen
*****************************************************/


/**
 * builds the settings panel
 */
function wpmem_a_build_options($wpmem_settings)
{ ?>
	<h3><?php _e('Manage Options'); ?></h3>
		<form name="updatesettings" id="updatesettings" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
		<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-settings'); } ?>
		<table class="form-table">
		<?php $arr = array(
			array(__('Block Posts by default','wp-members'),'wpmem_settings_block_posts',__('Note: Posts can still be individually blocked or unblocked at the article level','wp-members')),
			array(__('Block Pages by default','wp-members'),'wpmem_settings_block_pages',__('Note: Pages can still be individually blocked or unblocked at the article level','wp-members')),
			array(__('Show excerpts','wp-members'),'wpmem_settings_show_excerpts',__('Shows excerpted content above the login/registration on both Posts and Pages','wp-members')),
			array(__('Notify admin','wp-members'),'wpmem_settings_notify',__('Sends email to admin for each new registration?','wp-members')),
			array(__('Moderate registration','wp-members'),'wpmem_settings_moderate',__('Holds new registrations for admin approval','wp-members')),
			array(__('Use reCAPTCHA','wp-members'),'wpmem_settings_captcha',__('Turns on CAPTCHA for registration','wp-members')),
			array(__('Turn off registration','wp-members'),'wpmem_settings_turnoff',__('Turns off the registration process, only allows login','wp-members')),
			// NEW in 2.5.1 - legacy forms
			array(__('Legacy forms','wp-members'),'wpmem_settings_legacy',__('Uses the pre-2.5.1 table-based forms (leave off to use CSS table-less forms)','wp-members')),
			array(__('Time-based expiration','wp-members'),'wpmem_settings_time_exp',__('Allows for access to expire','wp-members')),
			array(__('Trial period','wp-members'),'wpmem_settings_trial',__('Allows for a trial period','wp-members')),
			array(__('Ignore warning messages','wp-members'),'wpmem_settings_ignore_warnings',__('Ignores WP-Members warning messages in the admin panel','wp-members'))
			); ?>
		<?php for ($row = 0; $row < count($arr); $row++) { ?>
		<?php if ( ( $row < 8 || $row > 9 ) || ( WPMEM_EXP_MODULE == true ) ) { ?>
		  <tr valign="top">
			<th align="left" scope="row"><?php echo $arr[$row][0]; ?></th>
			<td><?php if (WPMEM_DEBUG == true) { echo $wpmem_settings[$row+1]; } ?>
				<input name="<?php echo $arr[$row][1]; ?>" type="checkbox" id="<?php echo $arr[$row][1]; ?>" value="1" <?php if ($wpmem_settings[$row+1]==1) {echo "checked";}?> />
				<?php if($arr[$row][2]) { ?><span class="description"><?php echo $arr[$row][2]; ?></span><?php } ?>
			</td>
		  </tr>
		  <?php } ?>
		  <?php } ?>
		  
		  <?php // new in 2.5
		  $wpmem_msurl = get_option('wpmembers_msurl');
		  if (!$wpmem_msurl) { $wpmem_msurl = "http://"; } ?>
		  <tr>
			<th align="left" scope="row"><?php _e('Members Area URL:', 'wp-members'); ?></th>
			<td><input type="text" name="wpmem_settings_msurl" value="<?php echo $wpmem_msurl; ?>" size="50" />&nbsp;<span class="description"><?php _e('Optional', 'wp-members'); ?></span></td>
		  </tr><?php // new in 2.5.1
		  $wpmem_regurl = get_option('wpmembers_regurl');
		  if (!$wpmem_regurl) { $wpmem_regurl = "http://"; } ?>
		  <tr>
			<th align="left" scope="row"><?php _e('Register Page URL:', 'wp-members'); ?></th>
			<td><input type="text" name="wpmem_settings_regurl" value="<?php echo $wpmem_regurl; ?>" size="50" />&nbsp;<span class="description"><?php _e('Optional', 'wp-members'); ?></span></td>
		  </tr><?php // new in 2.5.1
		  $wpmem_cssurl = get_option('wpmembers_cssurl');
		  if (!$wpmem_cssurl) { $wpmem_cssurl = "http://"; } ?>
		  <tr>
			<th align="left" scope="row"><?php _e('Custom CSS:', 'wp-members'); ?></th>
			<td><input type="text" name="wpmem_settings_cssurl" value="<?php echo $wpmem_cssurl; ?>" size="50" />&nbsp;<span class="description"><?php _e('Optional', 'wp-members'); ?></span></td>
		  </tr>
		  <tr valign="top">
			<td>&nbsp;</td>
			<td><input type="hidden" name="wpmem_admin_a" value="update_settings">
				<input type="submit" name="UpdateSettings"  class="button-primary" value="<?php _e('Update Settings', 'wp-members'); ?> &raquo;" /> 
			</td>
		  </tr>
		</table>
	</form>
	<?php
}


/**
 * builds the fields panel
 */
function wpmem_a_build_fields( $wpmem_fields ) 
{ ?>
	<h3><?php _e('Manage Fields', 'wp-members'); ?></h3>
    <p><?php _e('Determine which fields will display and which are required.  This includes all fields, both native WP fields and WP-Members custom fields.', 'wp-members'); ?>
		&nbsp;<strong><?php _e('(Note: Email is always mandatory. and cannot be changed.)', 'wp-members'); ?></strong></p>
    <form name="updatefieldform" id="updatefieldform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-fields'); } ?>
	<table class="widefat" id="wpmem-fields">
		<thead><tr class="head">
        	<th scope="col"><?php _e( 'Add/Delete',  'wp-members' ) ?></th>
            <th scope="col"><?php _e( 'Field Label', 'wp-members' ) ?></th>
            <th scope="col"><?php _e( 'Option Name', 'wp-members' ) ?></th>
            <th scope="col"><?php _e( 'Field Type',  'wp-members' ) ?></th>
			<th scope="col"><?php _e( 'Display?',    'wp-members' ) ?></th>
            <th scope="col"><?php _e( 'Required?',   'wp-members' ) ?></th>
            <th scope="col"><?php _e( 'Checked?',   'wp-members' ) ?></th>
            <th scope="col"><?php _e( 'WP Native?',  'wp-members' ) ?></th>
        </tr></thead>
	<?php
	// order, label, optionname, input type, display, required, native
	$class = '';
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		if ($chkreq == "err" && $wpmem_fields[$row][5] == 'y' && $wpmem_fields[$row][4] != 'y') {
			$class = "updated fade";
		} else {
			$class = ($class == 'alternate') ? '' : 'alternate';
		}
		?><tr class="<?php echo $class; ?>" valign="top" id="<?php echo $wpmem_fields[$row][0];?>">
        	<td width="80"><?php 
				if( $wpmem_fields[$row][6] != 'y' ) {  ?><input type="checkbox" name="<?php echo "del_".$wpmem_fields[$row][2]; ?>" value="delete" /> <?php _e( 'Delete', 'wp-members' ); } ?></td>
            <td width="180"><?php 
				echo $wpmem_fields[$row][1];
				if( $wpmem_fields[$row][5] == 'y' ){ ?><font color="red">*</font><?php }
				?>
            </td>
            <td width="180"><?php echo $wpmem_fields[$row][2]; ?></td>
            <td width="80"><?php echo $wpmem_fields[$row][3]; ?></td>
		  <?php if( $wpmem_fields[$row][2]!='user_email' ) { ?>
			<td width="80"><?php echo wpmem_create_formfield($wpmem_fields[$row][2]."_display", 'checkbox', 'y', $wpmem_fields[$row][4]); ?></td>
            <td width="80"><?php echo wpmem_create_formfield($wpmem_fields[$row][2]."_required",'checkbox', 'y', $wpmem_fields[$row][5]); ?></td>
		  <?php } else { ?>
			<td colspan="2" width="160"><small><i><?php _e('(Email cannot be removed)', 'wp-members'); ?></i></small></td>
		  <?php } ?>
          	<td width="80"><?php if( $wpmem_fields[$row][3] == 'checkbox' ) { 
				echo wpmem_create_formfield( $wpmem_fields[$row][2]."_checked", 'checkbox', 'y', $wpmem_fields[$row][8]); } ?>
            </td>
			<td width="80"><?php if( $wpmem_fields[$row][6] == 'y' ) { echo "yes"; } ?></td>
          </tr><?php
	}	?>
	</table><br />
    <table class="widefat">	
        <tr>
        	<td width="80"><input type="checkbox" name="add_field" value="add" /> <?php _e( 'Add', 'wp-members' ); ?></td>
            <td width="180"><input type="text" name="add_name" value="New field label" /></td>
            <td width="180"><input type="text" name="add_option" value="new_option_name" /></td>
            <td width="80">
            	<select name="add_type">
            		<option value="text"><?php _e( 'text', 'wp-members' ); ?></option>
                    <option value="textarea"><?php _e( 'textarea', 'wp-members' ); ?></option>
                    <option value="checkbox"><?php _e( 'checkbox', 'wp-members' ); ?></option>
                </select>
            </td>
			<td width="80"><?php echo wpmem_create_formfield("add_display", 'checkbox', 'y', $wpmem_fields[$row][4]); ?></td>
            <td width="80"><?php echo wpmem_create_formfield("add_required",'checkbox', 'y', $wpmem_fields[$row][5]); ?></td>
			<td width="80"><input type="checkbox" name="add_checked_default" value="y" /></td>
            <td width="80">&nbsp;</td>
          </tr>
          <tr>
        	<td colspan="3" align="right"><?php _e('For checkbox, stored value if checked:', 'wp-members' ); ?></td>
            <td><input type="text" name="add_checked_value" value="value" class="small-text" /></td>
			<td colspan="3">&nbsp;</td>
          </tr>

    </table><br />
	<input type="hidden" name="wpmem_admin_a" value="update_fields" />
    <input type="submit" name="save"  class="button-primary" value="<?php _e('Update Fields', 'wp-members'); ?> &raquo;" /> 
    </form>
	<?php
}


/**
 * builds the dialogs panel
 */
function wpmem_a_build_dialogs($wpmem_dialogs)
{ 
	$wpmem_dialog_title_arr = array(
    	__("Restricted post (or page), displays above the login/registration form", 'wp-members'),
        __("Username is taken", 'wp-members'),
        __("Email is registered", 'wp-members'),
        __("Registration completed", 'wp-members'),
        __("User update", 'wp-members'),
        __("Passwords did not match", 'wp-members'),
        __("Password changes", 'wp-members'),
        __("Username or email do not exist when trying to reset forgotten password", 'wp-members'),
        __("Password reset", 'wp-members') 
    ); ?>
	<h3>WP-Members <?php _e('Dialogs and Error Messages', 'wp-members'); ?></h3>
	<p><?php printf(__('You can customize the text for dialogs and error messages. Simple HTML is allowed %s etc.', 'wp-members'), '- &lt;p&gt;, &lt;b&gt;, &lt;i&gt;,'); ?></p>
	<form name="updatedialogform" id="updatedialogform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>"> 
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-dialogs'); } ?>
		<table class="form-table">        
        <?php for ($row = 0; $row < count($wpmem_dialog_title_arr); $row++) { ?>
			<tr valign="top"> 
				<th scope="row"><?php echo $wpmem_dialog_title_arr[$row]; ?></th> 
				<td><textarea name="<?php echo "dialogs_".$row; ?>" rows="3" cols="50" id="" class="large-text code"><?php echo stripslashes($wpmem_dialogs[$row]); ?></textarea></td> 
			</tr>
		<?php } ?>
		
		<?php
		// new in 2.4, adding TOS dialog - this could be long so it will be its own entry
		$wpmem_tos = get_option('wpmembers_tos'); ?>
			<tr valign="top"> 
				<th scope="row"><?php _e('Terms of Service (TOS)', 'wp-members'); ?></th> 
				<td><textarea name="dialogs_tos" rows="3" cols="50" id="" class="large-text code"><?php echo $wpmem_tos; ?></textarea></td> 
			</tr>		
			<tr valign="top"> 
				<th scope="row">&nbsp;</th> 
				<td>
					<input type="hidden" name="wpmem_admin_a" value="update_dialogs" />
                    <input type="submit" name="save" class="button-primary" value="<?php _e('Update Dialogs', 'wp-members'); ?> &raquo;" />
				</td> 
			</tr>	
		</table> 
	</form>
	<?php
}


/**
 * primary admin function
 *
 * @todo check for duplicate field names in the add field process
 */
function wpmem_admin()
{
	$wpmem_settings = get_option('wpmembers_settings');
	$wpmem_fields   = get_option('wpmembers_fields');
	$wpmem_dialogs  = get_option('wpmembers_dialogs');
	
	if (WPMEM_EXP_MODULE == true) {
		$wpmem_experiod = get_option('wpmembers_experiod');
	}
	
	$did_update = false;

	switch ($_POST['wpmem_admin_a']) {

	case ("update_settings"):

		//check nonce
		check_admin_referer('wpmem-update-settings');

		//keep things clean
		$post_arr = array(
			'WPMEM_VERSION',
			'wpmem_settings_block_posts',
			'wpmem_settings_block_pages',
			'wpmem_settings_show_excerpts',
			'wpmem_settings_notify',
			'wpmem_settings_moderate',
			'wpmem_settings_captcha',
			'wpmem_settings_turnoff',
			'wpmem_settings_legacy',
			'wpmem_settings_time_exp',
			'wpmem_settings_trial',
			'wpmem_settings_ignore_warnings'
			);
			
		$wpmem_newsettings = array();
		for ($row = 0; $row < count($post_arr); $row++) {
			if ($post_arr == 'WPMEM_VERSION') {
				$wpmem_newsettings[$row] = 'WPMEM_VERSION';
			} else {
				if ($_POST[$post_arr[$row]] != 1) {
					$wpmem_newsettings[$row] = 0;
				} else {
					$wpmem_newsettings[$row] = $_POST[$post_arr[$row]];
				}
			}
			
			if (WPMEM_DEBUG == true) {
				echo $post_arr[$row]." ".$_POST[$post_arr[$row]]."<br />";
			}
			
			/* 	
				if we are setting registration to be moderated, 
				check to see if the current admin has been 
				activated so they don't accidentally lock themselves
				out later 
			*/
			if ($row == 5) {
				if ($_POST[$post_arr[$row]] == 1) {
					global $current_user;
					get_currentuserinfo();
					$user_ID = $current_user->ID;
					update_user_meta( $user_ID, 'active', 1 );
				}
			}			
		}
		
		// new in 2.5
		$wpmem_settings_msurl = $_POST['wpmem_settings_msurl'];
		if ( $wpmem_settings_msurl != 'http://' ) {
			update_option('wpmembers_msurl', $wpmem_settings_msurl);
		}
		$wpmem_settings_regurl = $_POST['wpmem_settings_regurl'];
		if ( $wpmem_settings_regurl != 'http://' ) {
			update_option('wpmembers_regurl', $wpmem_settings_regurl);
		} 
		$wpmem_settings_cssurl = $_POST['wpmem_settings_cssurl'];
		if ( $wpmem_settings_cssurl != 'http://' ) {
			update_option('wpmembers_cssurl', $wpmem_settings_cssurl);
		}

		update_option('wpmembers_settings',$wpmem_newsettings);
		$wpmem_settings = $wpmem_newsettings;
		$did_update = __('WP-Members settings were updated', 'wp-members');
		
		// sets the options tab as active - can remove if we change to another layout
		$active_tab = 'options';
		
		break;

	case ("update_fields"):

		//check nonce
		check_admin_referer('wpmem-update-fields');

		// rebuild the array, don't touch user_email - it's always mandatory
		$nrow = 0;
		for( $row = 0; $row < count($wpmem_fields); $row++ ) {

			// check to see if the field is checked for deletion, and if not, add it to the new array.
			$delete_field = "del_".$wpmem_fields[$row][2];
			$delete_field = $_POST[$delete_field];
			if( $delete_field != "delete" ) {

				for( $i = 0; $i < 4; $i++ ) {
					$wpmem_newfields[$nrow][$i] = $wpmem_fields[$row][$i];
				}
				
				$wpmem_newfields[$nrow][0] = $nrow + 1;
	
				$display_field = $wpmem_fields[$row][2]."_display"; 
				$require_field = $wpmem_fields[$row][2]."_required";
				$checked_field = $wpmem_fields[$row][2]."_checked";
	
				if( $wpmem_fields[$row][2] != 'user_email' ){
					//if ($_POST[$display_field] == "on") {$wpmem_newfields[$row][4] = 'y';}
					//if ($_POST[$require_field] == "on") {$wpmem_newfields[$row][5] = 'y';}
					$wpmem_newfields[$nrow][4] = $_POST[$display_field];
					$wpmem_newfields[$nrow][5] = $_POST[$require_field];
				} else {
					$wpmem_newfields[$nrow][4] = 'y';
					$wpmem_newfields[$nrow][5] = 'y';		
				}
	
				if( $wpmem_newfields[$nrow][4] != 'y' && $wpmem_newfields[$nrow][5] == 'y' ) { $chkreq = "err"; }
				$wpmem_newfields[$nrow][6] = $wpmem_fields[$row][6];
				if( $wpmem_fields[$row][7] ) { $wpmem_newfields[$nrow][7] = $wpmem_fields[$row][7]; }
				if( $wpmem_fields[$row][3] == 'checkbox' ) { 
					if( $_POST[$checked_field] == 'y' ) { echo "checked: " . $_POST[$checked_field];
						$wpmem_newfields[$nrow][8] = 'y';
					} else {
						$wpmem_newfields[$nrow][8] = 'n';
					}
				}
			
				$nrow = $nrow + 1;
			}
			
		}
		
		if( $_POST['add_field'] == 'add' ) {
		
			// error check that field label and option name are included and unique
			if( ! $_POST['add_name'] )   { $add_field_err_msg = __( 'Field Label is required for adding a new field. Nothing was updated.', 'wp-members' ); }
			if( ! $_POST['add_option'] ) { $add_field_err_msg = __( 'Option Name is required for adding a new field. Nothing was updated.', 'wp-members' ); }
			// @todo check for duplicate field names

		
			// error check option name for spaces and replace with underscores
			$us_option = $_POST['add_option'];
			$us_option = preg_replace("/ /", '_', $us_option);
				
				$wpmem_newfields[$nrow][0] = $nrow + 1;
				$wpmem_newfields[$nrow][1] = $_POST['add_name'];
				$wpmem_newfields[$nrow][2] = $us_option;
				$wpmem_newfields[$nrow][3] = $_POST['add_type'];
				$wpmem_newfields[$nrow][4] = $_POST['add_display'];
				$wpmem_newfields[$nrow][5] = $_POST['add_required'];
				$wpmem_newfields[$nrow][6] = 'n';
				
				if( $_POST['add_type'] == 'checkbox' ) { 
					$wpmem_newfields[$nrow][7] = $_POST['add_checked_value'];
					$wpmem_newfields[$nrow][8] = $_POST['add_checked_default'];
				}

		}
		
		if ( WPMEM_DEBUG == true ) { echo "<pre>"; print_r($wpmem_newfields); echo "</pre>"; }
		
		if( ! $add_field_err_msg ) {
			update_option('wpmembers_fields',$wpmem_newfields);
			$wpmem_fields = $wpmem_newfields; 
			$did_update = __('WP-Members fields were updated', 'wp-members');
		} else {
			$did_update = $add_field_err_msg;
		}
		
		// sets the fields tab as active - can remove if we change to another layout
		$active_tab = 'fields';
		
		break;

	case ("update_dialogs"):

		//check nonce
		check_admin_referer('wpmem-update-dialogs');

		for ($row = 0; $row < count($wpmem_dialogs); $row++) {
			$dialog = "dialogs_".$row;
			$wpmem_newdialogs[$row] = $_POST[$dialog];
		}

		update_option('wpmembers_dialogs',$wpmem_newdialogs);
		$wpmem_dialogs = $wpmem_newdialogs;
		
		// new in 2.4 for Terms of Service
		update_option('wpmembers_tos', $_POST['dialogs_tos']);		
		
		$did_update = __('WP-Members dialogs were updated', 'wp-members');
		
		// sets the dialogs tab as active - can remove if we change to another layout
		$active_tab = 'dialogs';
				
		break;
		
	case ("update_captcha"):
	
		//check nonce
		check_admin_referer('wpmem-update-captcha');
		
		$wpmem_captcha = array(
			$_POST['wpmem_captcha_publickey'],
			$_POST['wpmem_captcha_privatekey'],
			$_POST['wpmem_captcha_theme']
			);
		
		update_option('wpmembers_captcha',$wpmem_captcha);
		$did_update = __('reCAPTCHA was updated for WP-Members', 'wp-members');
		
		// sets the captcha tab as active - can remove if we change to another layout
		$active_tab = 'captcha';
		
		break;

	case ("update_exp"):
	
		//check nonce
		check_admin_referer('wpmem-update-exp');
		
		$wpmem_newexperiod = wpmem_a_newexperiod();
		update_option('wpmembers_experiod',$wpmem_newexperiod);
		
		$wpmem_experiod = $wpmem_newexperiod; if (WPMEM_DEBUG == true) { var_dump($wpmem_experiod); }
		$did_update = __('WP-Members expiration periods were updated', 'wp-members');
		
		// sets the exp tab as active - can remove if we change to another layout
		$active_tab = 'exp';
		
		break;

	}

	?>
    <div class="wrap">
	<?php screen_icon( 'options-general' ); ?>
    <h2>WP-Members <?php _e('Settings', 'wp-members'); ?></h2>

    <?php
	if ($did_update != false) {

		if ($chkreq == "err") { ?>
			<div class="error"><p><strong><?php _e('Settings were saved, but you have required fields that are not set to display!', 'wp-members'); ?></strong><br /><br />
				<?php _e('Note: This will not cause an error for the end user, as only displayed fields are validated.  However, you should still check that 
				your displayed and required fields match up.  Mismatched fields are highlighted below.', 'wp-members'); ?></p></div>
		<?php } elseif( $add_field_err_msg ) { ?>
        	<div class="error"><p><strong><?php echo $add_field_err_msg; ?></p></div>
        <?php } else { ?>
			<div id="message" class="updated fade"><p><strong><?php echo $did_update; ?></strong></p></div>
		<?php }

	}


	/*************************************************************************
		WARNING MESSAGES
	**************************************************************************/

	// settings allow anyone to register
	if ( get_option('users_can_register') != 0 && $wpmem_settings[11] == 0 ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(1);
	}

	// settings allow anyone to comment
	if ( get_option('comment_registration') !=1 && $wpmem_settings[11] == 0 ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(2);
	} 
	
	// rss set to full text feeds
	if ( get_option('rss_use_excerpt') !=1 && $wpmem_settings[11] == 0 ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(3);
	} 

	// holding registrations but haven't changed default successful registration message
	if ( $wpmem_settings[11] == 0 && $wpmem_settings[5] == 1 && $wpmem_dialogs[3] == 'Congratulations! Your registration was successful.<br /><br />You may now login using the password that was emailed to you.' ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(4);
	}  

	// turned off registration but also have set to moderate and/or email new registrations
	if ( $wpmem_settings[11] == 0 && $wpmem_settings[7] == 1 ) { 
		if ( $wpmem_settings[5] == 1 || $wpmem_settings[4] ==1 ) { 
			include_once('wp-members-dialogs-admin.php');
			wpmem_a_warning_msg(5);
		}  
	}
	
	// haven't entered recaptcha api keys
	if ( $wpmem_settings[11] == 0 && $wpmem_settings[6] == 1 ) {
		$wpmem_captcha = get_option('wpmembers_captcha');
		if ( !$wpmem_captcha[0]  || !$wpmem_captcha[1] ) {
			include_once('wp-members-dialogs-admin.php');
			wpmem_a_warning_msg(6);
		}
	}
	
	/*************************************************************************
		END WARNING MESSAGES
	**************************************************************************/	?>


	<p><strong><a href="http://butlerblog.com/wp-members/" target="_blank">WP-Members</a> <?php _e('Version:', 'wp-members'); echo "&nbsp;".WPMEM_VERSION; ?></strong>
		[ <?php _e('Follow', 'wp-members'); ?> ButlerBlog: <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a> ]
		<br />
		<?php _e('If you find this plugin useful, please consider making a donation', 'wp-members'); ?> <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="QC2W6AM9WUZML">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
	</p>
	
	<?php 
	// check for which admin tabs need to be included
	if ($wpmem_settings[6] == 1) { $show_recaptcha = true; }
	if (WPMEM_EXP_MODULE == true) {
		$show_paypal = true;
		if  (($wpmem_settings[8] == 1 || $wpmem_settings[9] == 1) ) { $show_subscriptions = true; } 
	}
	?>
	
	<ul class="tabs">
		<li<?php if( $active_tab == 'options' || ! $active_tab ) { echo ' class="active"'; } ?>><a href="#tab1"><?php _e('Options', 'wp-members'); ?></a></li>
		<li<?php if( $active_tab == 'fields' ) { echo ' class="active"'; } ?>><a href="#tab2"><?php _e('Fields', 'wp-members'); ?></a></li>
		<li<?php if( $active_tab == 'dialogs' ) { echo ' class="active"'; } ?>><a href="#tab3"><?php _e('Dialogs', 'wp-members'); ?></a></li>
		<?php if ($show_recaptcha == true ) { ?>
		<li<?php if( $active_tab == 'captcha' ) { echo ' class="active"'; } ?>><a href="#tab4"><?php _e('reCAPTCHA', 'wp-members'); ?></a></li>
		<?php }
		if ($show_subscriptions == true ) { ?> 
		<li<?php if( $active_tab == 'exp' ) { echo ' class="active"'; } ?>><a href="#tab5"><?php _e('Subscriptions', 'wp-members'); ?></a></li>
		<?php }
		if ($show_paypal == true ) { ?>
		<li><a href="#tab6"><?php _e('PayPal Settings', 'wp-members'); ?></a></li>
		<?php } ?>
	</ul>

	<div class="tab_container">

		<div id="tab1" class="tab_content<?php if( $active_tab == 'options' || ! $active_tab ) { echo ' active'; } ?>">
			<?php wpmem_a_build_options($wpmem_settings); ?>
		</div>

		<div id="tab2" class="tab_content<?php if( $active_tab == 'fields' ) { echo ' active'; } ?>">
			<?php wpmem_a_build_fields($wpmem_fields); ?>
		</div>

		<div id="tab3" class="tab_content<?php if( $active_tab == 'dialogs' ) { echo ' active'; } ?>">
			<?php wpmem_a_build_dialogs($wpmem_dialogs); ?>	
		</div>
		
		<?php if ($show_recaptcha == true ) { ?>
		<div id="tab4" class="tab_content<?php if( $active_tab == 'captcha' ) { echo ' active'; } ?>">
			<?php wpmem_a_build_captcha_options(); ?>
		</div>
		<?php } 
		
		if ($show_subscriptions == true ) { ?>
		<div id="tab5" class="tab_content<?php if( $active_tab == 'exp' ) { echo ' active'; } ?>">
			<?php wpmem_a_build_expiration( $wpmem_experiod, $wpmem_settings[9], $wpmem_settings[8] ); ?>
		</div>
		<?php }
		
		if ($show_paypal == true) { ?>
		<div id="tab6" class="tab_content">
			<?php wpmem_a_build_paypal(); ?>
		</div>
		<?php } ?>

	</div>
	<p>&nbsp;</p>
		<p><i>
		<?php printf(__('Thank you for using WP-Members! You are using version %s', 'wp-members'), WPMEM_VERSION); ?>.
		<?php printf(__('If you find this plugin useful, please consider a %s donation %s', 'wp-members'), '<a href="http://butlerblog.com/wp-members">', '</a>'); ?>.<br />
		WP-Members is copyright &copy; 2006-<?php echo date("Y"); ?>  by Chad Butler, <a href="http://butlerblog.com">butlerblog.com</a> | 
		  <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a><br />
		WP-Members is a trademark of <a href="http://butlerblog.com">butlerblog.com</a>
		</i></p>
		<p>&nbsp;</p>
	</div>
<?php
}


/**
 * builds the captcha options
 */
function wpmem_a_build_captcha_options()
{ 
	$wpmem_captcha = get_option('wpmembers_captcha');
	$url           = home_url();
	?>

	<h3><?php _e('Manage reCAPTCHA Options'); ?></h3>
    	<form name="updatecaptchaform" id="updatecaptchaform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>"> 
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-captcha'); } ?>
	<table class="form-table">
		<tr>
			<td colspan="2">
            	<p><?php _e('reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog.', 'wp-members'); ?></p>
				<p><?php printf(__('reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot while also correcting the automatic scans of old books. So you get less spam, and the world gets accurately digitized books. Everybody wins! For details, visit the %s reCAPTCHA website%s', 'wp-members'), '<a href="http://recaptcha.net/" target="_blank">', '</a>'); ?>.</p>
                <p>
            </td>
		</tr>        
		<tr valign="top"> 
			<th scope="row"><?php _e('reCAPTCHA Keys', 'wp-members'); ?></th> 
			<td>
            	<?php printf(__('reCAPTCHA requires an API key, consisting of a "public" and a "private" key. You can sign up for a %s free reCAPTCHA key%s', 'wp-members'), "<a href=\"http://recaptcha.net/api/getkey?domain=$url&amp;app=wordpress\" target=\"_blank\">", '</a>'); ?>.<br />
            	<?php _e('Public Key', 'wp-members'); ?>:&nbsp;&nbsp;&nbsp;<input type="text" name="wpmem_captcha_publickey" size="50" value="<?php echo $wpmem_captcha[0]; ?>" /><br />
                <?php _e('Private Key', 'wp-members'); ?>:&nbsp;<input type="text" name="wpmem_captcha_privatekey" size="50" value="<?php echo $wpmem_captcha[1]; ?>" />
             </td> 
		</tr>
        <tr valign="top">
        	<th scope="row"><?php _e('Choose Theme'); ?></th>
            <td>
            	<select name="wpmem_captcha_theme">
					<!--<?php echo wpmem_create_formfield(__('WP-Members', 'wp-members'), 'option', 'custom', $wpmem_captcha[2]); ?>--><?php
					echo wpmem_create_formfield(__('Red', 'wp-members'), 'option', 'red', $wpmem_captcha[2]); 
					echo wpmem_create_formfield(__('White', 'wp-members'), 'option', 'white', $wpmem_captcha[2]);
					echo wpmem_create_formfield(__('Black Glass', 'wp-members'), 'option', 'blackglass', $wpmem_captcha[2]); 
					echo wpmem_create_formfield(__('Clean', 'wp-members'), 'option', 'clean', $wpmem_captcha[2]); ?>
					<!--<?php echo wpmem_create_formfield(__('Custom', 'wp-members'), 'option', 'custom', $wpmem_captcha[2]); ?>-->
                </select>
            </td>
        </tr><!--
		<tr valign="top"> 
			<th scope="row">Custom reCAPTCHA theme</th> 
			<td><input type="text" name="wpmem_settings_regurl" value="<?php echo $wpmem_regurl; ?>" size="50" />&nbsp;<span class="description"><?php _e('Optional', 'wp-members'); ?></span></td> 
		</tr>-->
		<tr valign="top"> 
			<th scope="row">&nbsp;</th> 
			<td>
				<input type="hidden" name="wpmem_admin_a" value="update_captcha" />
                <input type="submit" name="save"  class="button-primary" value="<?php _e('Update reCAPTCHA Settings', 'wp-members'); ?> &raquo;" />
			</td> 
		</tr> 
	</table> 
	</form>
	<?php 
}


/*****************************************************
	End WP-Members Settings Screen
*****************************************************/


/*****************************************************
	Bulk User Management Screen
*****************************************************/

/**
 * handles user management
 */
function wpmem_admin_users()
{	
	// check to see if we need phone and country columns
	$wpmem_fields = get_option('wpmembers_fields');
	for ($row = 0; $row < count($wpmem_fields); $row++)
	{ 
		if ($wpmem_fields[$row][2] == 'country' && $wpmem_fields[$row][4] == 'y') { $col_country = true; }
		if ($wpmem_fields[$row][2] == 'phone1' && $wpmem_fields[$row][4] == 'y') { $col_phone = true; }
	}
	
	// should run other checks for expiration, activation, etc...
	
	
	// here is where we handle actions on the table...
	
	if ( $_POST['doaction'] ) { 
		$action = $_POST['action'];
		$doaction = true;
	} elseif ( $_POST['doaction2'] ) {
		$action = $_POST['action2'];
		$doaction = true;
	}
	
	if ($doaction) {	
		
		$users = $_POST['users'];

		switch ($action) {
		
		case "activate":
			$x = 0;
			foreach ($users as $user) {
				// check to see if the user is already activated, if not, activate
				if ( ! get_user_meta($user, 'active', true) ) {
					wpmem_a_activate_user($user);
					$x++;
				}
			}
			$user_action_msg = sprintf( __('%d users were activated.', 'wp-members'), $x );
			break;
			
		case "export":
			update_option('wpmembers_export',$users);
			$user_action_msg = sprintf( __('Users ready to export, %s click here %s to generate and download a CSV.', 'wp-members'),  "<a href=\"".WP_PLUGIN_URL."/wp-members/wp-members-export.php\" target=\"_blank\">", "</a>" );
			break;
		
		}
		
	} ?>

	<div class="wrap">

		<div id="icon-users" class="icon32"><br /></div>
		<h2><?php _e('WP-Members Users', 'wp-members'); ?>  <a href="user-new.php" class="button add-new-h2"><?php _e('Add New', 'wp-members'); ?></a></h2>
		
	<?php if ($user_action_msg) { ?>

		<div id="message" class="updated fade"><p><strong><?php echo $user_action_msg; ?></strong></p></div>

	<?php } ?>
		
		<div class="filter">
			<form id="" action="" method="get">
			<ul class="subsubsub">
			
			<?php
			
			// For now, I don't see a good way of working this for localization without a 
			// huge amount of additional programming (like a multi-dimensional array)
			
			$tmp  = array("All", "Not Active", "Trial", "Subscription", "Expired", "Not Exported");
			for ($row = 0; $row < count($tmp); $row++)
			{
				
				$link = "users.php?page=wpmem-users";
				if ($row != 0) {
				
					$lcas = strtolower($tmp[$row]);
					$lcas = str_replace (" ", "", $lcas);
					$link.= "&#038;show=";
					$link.= $lcas;
					
					$curr = "";
					if ($_GET['show'] == $lcas) { $curr = " class=\"current\""; }
					
				} else {
				
					if (!$_GET['show']) { $curr = " class=\"current\""; }
					
				}
				
				$end = "";
				if ($row != 5) { $end = " |"; }

				$echolink = true;
				if ($lcas == "notactive" && WPMEM_MOD_REG != 1) { $echolink = false; }
				if ($lcas == "trial"     && WPMEM_USE_TRL != 1) { $echolink = false; }
				
				if (($lcas == "subscription" || $lcas == "expired") && WPMEM_USE_EXP != 1) { $echolink = false; }
				
				if ($echolink) { echo "<li><a href=\"$link\"$curr>$tmp[$row] <span class=\"count\"></span></a>$end</li>"; }
			}
			
			?>
			</ul>
			</form>
		</div>

		<?php // NOT YET... ?><!--
		<form class="search-form" action="" method="get">
			<p class="search-box">
				<label class="screen-reader-text" for="user-search-input">Search Users:</label>
				<input type="text" id="user-search-input" name="usersearch" value="" />

				<input type="submit" value="Search Users" class="button" />
			</p>
		</form>-->
		
		<form id="posts-filter" action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">

		<?php wpmem_a_build_user_action(); ?>

		<table class="widefat fixed" cellspacing="0">
			<thead>
			<?php $colspan = wpmem_a_build_user_tbl_head($col_phone,$col_country); ?>
			</thead>

			<tfoot>
			<?php $colspan = wpmem_a_build_user_tbl_head($col_phone,$col_country); ?>
			</tfoot>

			<tbody id="users" class="list:user user-list">

			<?php	

			$blogusers = get_users_of_blog();

			if(WPMEM_DEBUG == true) { echo "<pre>\n";print_r($blogusers);echo "</pre>\n"; }
			$show = $_GET['show']; $x = 0;
			for ($row = 0; $row < count($blogusers); $row++)
			{
				// are we filtering results? (active, trials, etc...)
				
				$chk_show = false; 
				switch ($show) {
				case "notactive":
					if (get_user_meta($blogusers[$row]->user_id,'active','true') != 1) { $chk_show = true; }
					break;
				case "trial":
					$chk_exp_type = get_user_meta($blogusers[$row]->user_id,'exp_type','true');
					if ($chk_exp_type == 'trial') { $chk_show = true; }
					break;
				case "subscription":
					$chk_exp_type = get_user_meta($blogusers[$row]->user_id,'exp_type','true');
					if ($chk_exp_type == 'subscription') { $chk_show = true; }
					break;
				case "expired":
					if (wpmem_chk_exp($blogusers[$row]->user_id)) { $chk_show = true; }
					break;
				case "notexported":
					if (get_user_meta($blogusers[$row]->user_id,'exported','true') != 1) { $chk_show = true; }
					break;
				}

				if (!$show || $chk_show == true) {
					
					$class = ($class == 'alternate') ? '' : 'alternate';
					
					$theid = $blogusers[$row]->user_id;
					$fname = get_user_meta($blogusers[$row]->user_id,'first_name','true');
					$lname = get_user_meta($blogusers[$row]->user_id,'last_name','true');

					echo "<tr id=\"".$blogusers[$row]->user_id."\" class=\"$class\">\n";
					echo "	<th scope='row' class='check-column'><input type='checkbox' name='users[]' id=\"user_$theid\" class='administrator' value=\"$theid\" /></th>\n";
					echo "	<td class=\"username column-username\" nowrap>\n";
					echo "		<strong><a href=\"user-edit.php?user_id=$theid&#038;wp_http_referer=%2Fwp%2Fwp-admin%2Fusers.php\">".$blogusers[$row]->user_login."</a></strong><br />\n";
					echo "	</td>\n";
					echo "	<td class=\"name column-name\" nowrap>$fname $lname</td>\n";
					echo "	<td class=\"email column-email\" nowrap><a href='mailto:".$blogusers[$row]->user_email."' title='E-mail: ".$blogusers[$row]->user_email."'>".$blogusers[$row]->user_email."</a></td>\n";
					
					if ($col_phone == true) {
						$phone = get_user_meta($blogusers[$row]->user_id,'phone1','true');
						echo "	<td class=\"email column-email\" nowrap>$phone</td>\n";
					}
					
					if ($col_country == true) {
						$country = get_user_meta($blogusers[$row]->user_id,'country','true');
						echo "	<td class=\"email column-email\" nowrap>$country</td>\n";
					}
					
					if (WPMEM_MOD_REG == 1) { 
						echo "	<td class=\"role column-role\" nowrap>";
						if (get_user_meta($theid,'active','true') != 1) { _e('No'); }
						echo "</td>\n";
					}
					
					if (WPMEM_USE_EXP == 1) {
						if (WPMEM_USE_TRL == 1) {
							echo "	<td class=\"email column-email\" nowrap>";echo ucfirst( get_user_meta($theid, 'exp_type', 'true') );echo "</td>\n";
						}
						echo "	<td class=\"email column-email\" nowrap>";echo get_user_meta($theid, 'expires', 'true');echo "</td>\n";
					}
					echo "</tr>\n"; $x++;
				}
			} 
			
			if ($x == 0) { echo "<tr><td colspan=\"$colspan\">"; _e('No users matched your criteria', 'wp-members'); echo "</td></tr>"; } ?>

		</table>
		
		<?php wpmem_a_build_user_action('2'); ?>
		
		</form>
	</div>
<?php
}


/**
 * activates a user
 */
function wpmem_a_activate_user($user_id)
{
	$new_pass = substr( md5( uniqid( microtime() ) ), 0, 7);
	$hashpassword = md5($new_pass);

	global $wpdb;
	$wpdb->update( $wpdb->users, array( 'user_pass' => $hashpassword ), array( 'ID' => $user_id ), array( '%s' ), array( '%d' ) );

	// new in 2.4 for user expiration
	if (WPMEM_USE_EXP == 1) { wpmem_set_exp($user_id); }
	
	require_once('wp-members-email.php');

	wpmem_inc_regemail($user_id,$new_pass,2);
	update_user_meta($user_id,'active',1); 
}


/**
 * builds the user action dropdown
 */
function wpmem_a_build_user_action($x = '')
{ ?>

		<div class="tablenav">
			<div class="alignleft actions">
				<select name="action<?php echo $x; ?>">
					<option value="" selected="selected"><?php _e('Bulk Actions', 'wp-members'); ?></option>
				<?php if (WPMEM_MOD_REG == 1) { ?>
					<option value="activate"><?php _e('Activate', 'wp-members'); ?></option>
				<?php } ?>
					<option value="export"><?php _e('Export', 'wp-members'); ?></option>
				</select>
				<input type="submit" value="<?php _e('Apply', 'wp-members'); ?>" name="doaction<?php echo $x; ?>" id="doaction<?php echo $x; ?>"" class="button-secondary action" />
			</div>
			<br class="clear" />
		</div>

<?php 	
}


/**
 * builds the user management table heading
 */
function wpmem_a_build_user_tbl_head($col_phone,$col_country)
{
	$tbl_head_arr = array('Username', 'Name', 'E-mail', 'Phone', 'Country', 'Activated?', 'Subscription', 'Expires'); ?>

	<tr class="thead">
		<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
	<?php $colspan = 1; 
	foreach ($tbl_head_arr as $val) { 

		$showcol = false;
		switch ($val) {
		case "Phone":
			if ($col_phone == true) { $showcol = true; $colspan++; }
			break;
		case "Country":
			if ($col_country == true) { $showcol = true; $colspan++; }
			break;
		case "Activated?":
			if (WPMEM_MOD_REG == 1) { $showcol = true; $colspan++; }
			break;
		case "Subscription":
			if (WPMEM_USE_EXP == 1 && WPMEM_USE_TRL == true) { $showcol = true; $colspan++; }
			break;
		case "Expires":
			if (WPMEM_USE_EXP == 1) { $showcol = true; $colspan++; }
			break;
		default:
			$showcol = true; $colspan++; 
			break;
		} 		
		if ($showcol == true) { ?>
		<th scope="col" class="manage-column" style=""><?php echo $val ?></th>
	<?php } 
	} ?>
	</tr><?php 
	return $colspan;
}


/*****************************************************
	End Bulk User Management Screen
*****************************************************/


/*****************************************************
	New features associated with field management
*****************************************************/


/**
 * loads the admin javascript file
 */
function wpmem_load_admin_js()
{
	// queue up admin ajax and styles 
	$plugin_path = plugin_dir_url ( __FILE__ );
	wp_enqueue_script( 'wpmem-admin-js',  $plugin_path.'js/wp-members-admin.js', '', WPMEM_VERSION ); 
	wp_enqueue_style ( 'wpmem-admin-css', $plugin_path.'css/wp-members-styles-admin.css', '', WPMEM_VERSION );
}


/**
 * reorders the fields on DnD
 */
add_action( 'wp_ajax_wpmem_a_field_reorder', 'wpmem_a_field_reorder' );
function wpmem_a_field_reorder()
{
	// start fresh
	$new_order = $wpmem_old_fields = $wpmem_new_fields = $key = $row = '';

	$new_order = $_REQUEST['orderstring'];
	$new_order = explode("&", $new_order);	
	
	// loop through $new_order to create new field array
	$wpmem_old_fields = get_option('wpmembers_fields');
	for ( $row = 0; $row < count( $new_order ); $row++ )  {
		if ($row > 0) {
			$key = $new_order[$row];
			$key = substr($key, 15); //echo $key.", ";
			
			for ( $x = 0; $x < count( $wpmem_old_fields ); $x++ )  {
				
				if ( $wpmem_old_fields[$x][0] == $key ) {
					$wpmem_new_fields[$row - 1] = $wpmem_old_fields[$x];
				}
			}
		}
	}
	
	update_option('wpmembers_fields', $wpmem_new_fields); 

	die(); // this is required to return a proper result
}

// end of the admin features...
?>