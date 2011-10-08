<?php
class eshop_setting_base_class extends eshop_metabox_class {
	function on_load_page() {
		add_meta_box('eshop-base-pages', __('eShop Base Options','eshop'), array(&$this, 'base_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-base-pages-2', __('Reset eShop Base','eshop'), array(&$this, 'base_box_2'), $this->pagehook, 'normal', 'core');
		add_filter('eshop_error_messages',array(&$this, 'eclass_errors'));
	}
  	function base_box($eshopoptions) {
		?>
		<fieldset>
			<label for="eshop_base_brand"><?php _e('Brand','eshop'); ?></label><input id="eshop_base_brand" name="eshop_base_brand" type="text" value="<?php echo $eshopoptions['base_brand']; ?>" size="30" /><br />
			<label for="eshop_base_condition"><?php _e('Condition','eshop'); ?></label>
			<select name="eshop_base_condition" id="eshop_base_condition">
			<?php
			$currentconditions=array('new', 'used', 'refurbished', 'second hand');
			foreach($currentconditions as $code){
				if($code == $eshopoptions['base_condition']){
					$sel=' selected="selected"';
				}else{
					$sel='';
				}
				echo '<option value="'. $code .'"'. $sel .'>'. $code .'</option>';
			}
			?>
			</select><br />
			<label for="eshop_base_expiry"><?php _e('Product expiry in days','eshop'); ?></label>
			<select name="eshop_base_expiry" id="eshop_base_expiry">
			<?php
			$currentexpiry=array('1', '7', '28', '180', '365','730');
			foreach($currentexpiry as $code){
				if($code == $eshopoptions['base_expiry']){
					$sel=' selected="selected"';
				}else{
					$sel='';
				}
				echo '<option value="'. $code .'"'. $sel .'>'. $code .'</option>';
			}
			?>
			</select><br />
			<label for="eshop_base_ptype"><?php _e('Product type','eshop'); ?></label><input id="eshop_base_ptype" name="eshop_base_ptype" type="text" value="<?php echo $eshopoptions['base_ptype']; ?>" size="30" /><br />
			<label for="eshop_base_payment"><?php _e('Payment Accepted <small> comma delimited list of payment methods available.</small>','eshop'); ?></label><input id="eshop_base_payment" name="eshop_base_payment" type="text" value="<?php echo $eshopoptions['base_payment']; ?>" size="30" /><br />
			<input type="hidden" name="page_options" value="eshop_base_brand,eshop_base_condition,eshop_base_expiry,eshop_base_ptype,eshop_base_payment" />
		</fieldset>
	<?php
	}
	function base_box_2($eshopoptions) {
		?>
		<p><?php _e('This resets all product data entered on the <a href="admin.php?page=eshop-base.php">eShop Base Products</a> page.','eshop'); ?></p>
		<input class="button" type="submit" name="eshopbasereset" value="<?php _e('Reset Now','eshop'); ?>" />
	<?php

	}
	function on_save_changes() {
		global $wpdb;
		$err='100';
		//user permission check
		if ( !current_user_can('eShop_admin') )
			wp_die( __('Cheatin&#8217; uh?') );			
		//cross check the given referer
		check_admin_referer('eshop-metaboxes-general');
		$eshopoptions = get_option('eshop_plugin_settings');
		$eshopoptions['base_brand']=$wpdb->escape($_POST['eshop_base_brand']);
		$eshopoptions['base_condition']=$wpdb->escape($_POST['eshop_base_condition']);
		$eshopoptions['base_expiry']=$wpdb->escape($_POST['eshop_base_expiry']);
		$eshopoptions['base_ptype']=$wpdb->escape($_POST['eshop_base_ptype']);
		$eshopoptions['base_payment']=$wpdb->escape($_POST['eshop_base_payment']);

		update_option('eshop_plugin_settings',$eshopoptions);
		if(isset($_POST['eshopbasereset'])){
			$table=$wpdb->prefix.'eshop_base_products';
			$wpdb->query("TRUNCATE TABLE $table"); 
			$err='1';
		}elseif(isset($_POST['submit'])){
			$err='100';
		}
		
		//lets redirect the post request into get request
		$_POST['_wp_http_referer']=add_query_arg('eshop_message',$err,$_POST['_wp_http_referer']);

		wp_redirect($_POST['_wp_http_referer']);
		exit;
	}
	function eclass_errors($messages){
		$messages=array(
		'1'=>__('eShop Base product data has been reset.','eshop'),
		'100'=>__('eShop Base settings updated.','eshop')
		);
		return $messages;
	}
}

class eshop_setting_pages_class extends eshop_metabox_class {
	function on_load_page() {
		add_meta_box('eshop-special-pages', __('Continue Shopping Link','eshop'), array(&$this, 'sp_pages_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-special-pages-2', __('Links to extra pages','eshop'), array(&$this, 'sp_pages_box_2'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-special-pages-3', __('Automatically created pages','eshop'), array(&$this, 'sp_pages_box_3'), $this->pagehook, 'normal', 'core');
		add_filter('eshop_error_messages',array(&$this, 'eclass_errors'));
	}
  
	function sp_pages_box($eshopoptions) {
		?>
		<fieldset>
			<p><?php _e('If you enter the page id of your main Shop page, then eShop will use that for the <strong>Continue Shopping</strong> link. Leave this blank and eShop will either link to the last product, or to the main page of your site automatically.','eshop'); ?></p>
			<label for="eshop_shop_page"><?php _e('Shop Page - page id number','eshop'); ?></label><input id="eshop_shop_page" name="eshop_shop_page" type="text" value="<?php echo $eshopoptions['shop_page']; ?>" size="5" /><br />
		</fieldset>
	<?php
	}
	function sp_pages_box_2($eshopoptions) {
		if(!isset($eshopoptions['details']['tax']))
			$eshopoptions['details']['tax']='';
		?>
		<fieldset>
			<p><?php _e('These links automatically appear on the checkout page.','eshop'); ?></p>
			<label for="eshop_cart_shipping"><?php _e('Shipping rates - page id number','eshop'); ?></label><input id="eshop_cart_shipping" name="eshop_cart_shipping" type="text" value="<?php echo $eshopoptions['cart_shipping']; ?>" size="5" /><br />
			<label for="eshop_xtra_privacy"><?php _e('Privacy Policy - page id number','eshop'); ?></label><input id="eshop_xtra_privacy" name="eshop_xtra_privacy" type="text" value="<?php echo $eshopoptions['xtra_privacy']; ?>" size="5" /><br />
			<label for="eshop_xtra_help"><?php _e('Help - page id number','eshop'); ?></label><input id="eshop_xtra_help" name="eshop_xtra_help" type="text" value="<?php echo $eshopoptions['xtra_help']; ?>" size="5" /><br />
			<label for="eshop_details_tax"><?php _e('Tax rates - page id number','eshop'); ?></label><input id="eshop_details_tax" name="eshop_details_tax" type="text" value="<?php echo $eshopoptions['details']['tax']; ?>" size="4" /><br />

		</fieldset>
	<?php
	}
	function sp_pages_box_3($eshopoptions) {
		?>
		<fieldset>
			<p class="warn"><?php _e('<strong>Warning:</strong> Changes made here amend the page id of the automatically created pages - change with extreme care.','eshop'); ?></p>
			<label for="eshop_cart"><?php _e('Cart - page id number','eshop'); ?></label><input id="eshop_cart" name="eshop_cart" type="text" value="<?php echo $eshopoptions['cart']; ?>" size="5" /><br />
			<label for="eshop_checkout"><?php _e('Checkout - page id number','eshop'); ?></label><input id="eshop_checkout" name="eshop_checkout" type="text" value="<?php echo $eshopoptions['checkout']; ?>" size="5" /><br />
			<label for="eshop_cart_success"><?php _e('Successful payment  - page id number','eshop'); ?></label><input id="eshop_cart_success" name="eshop_cart_success" type="text" value="<?php echo $eshopoptions['cart_success']; ?>" size="5" /><br />
			<label for="eshop_cart_cancel"><?php _e('Cancelled payment - page id number','eshop'); ?></label><input id="eshop_cart_cancel" name="eshop_cart_cancel" type="text" value="<?php echo $eshopoptions['cart_cancel']; ?>" size="5" /><br />
			<label for="eshop_show_downloads"><?php _e('Downloads - page id number','eshop'); ?></label><input id="eshop_show_downloads" name="eshop_show_downloads" type="text" value="<?php echo $eshopoptions['show_downloads']; ?>" size="5" /><br />
		</fieldset>
	<?php
	}
	function on_save_changes() {
		//user permission check
		global $wpdb;
		$err='100';

		if ( !current_user_can('eShop_admin') )
			wp_die( __('Cheatin&#8217; uh?') );			
		//cross check the given referer
		check_admin_referer('eshop-metaboxes-general');
		$eshopoptions = get_option('eshop_plugin_settings');

		if(is_numeric($_POST['eshop_xtra_privacy'])){
			$ptitle=get_post($_POST['eshop_xtra_privacy']);
			if($ptitle->post_title!=''){
				$eshopoptions['xtra_privacy']=$wpdb->escape($_POST['eshop_xtra_privacy']);
			}else{
				$err=1;
				$eshopoptions['xtra_privacy']='';
			}
		}elseif($_POST['eshop_xtra_privacy']!=''){
			$err=1;
		}else{
			$eshopoptions['xtra_privacy']='';
		}
		
		if(is_numeric($_POST['eshop_xtra_help'])){
			$ptitle=get_post($_POST['eshop_xtra_help']);
			if($ptitle->post_title!=''){
				$eshopoptions['xtra_help']=$wpdb->escape($_POST['eshop_xtra_help']);
			}else{
				$err=2;
			}	
		}elseif($_POST['eshop_xtra_help']!=''){
			$err=2;
		}else{
			$eshopoptions['xtra_help']='';
		}
		
		if(is_numeric($_POST['eshop_cart_shipping'])){
			$ptitle=get_post($_POST['eshop_cart_shipping']);
			if($ptitle->post_title!=''){
				$eshopoptions['cart_shipping']=$wpdb->escape($_POST['eshop_cart_shipping']);
			}else{
				$err=3;
			}	
		}elseif(trim($_POST['eshop_cart_shipping'])!=''){
			$err=3;
		}else{
			$eshopoptions['cart_shipping']='';
		}

		if(is_numeric($_POST['eshop_shop_page'])){
			$ptitle=get_post($_POST['eshop_shop_page']);
			if($ptitle->post_title!=''){
				$eshopoptions['shop_page']=$wpdb->escape($_POST['eshop_shop_page']);
			}else{
				$err=4;
			}	
		}elseif(trim($_POST['eshop_shop_page'])!=''){
				$err=4;
		}else{
			$eshopoptions['shop_page']='';
		}
		
		if(is_numeric($_POST['eshop_cart'])){
			$ptitle=get_post($_POST['eshop_cart']);
			if($ptitle->post_title!=''){
				$eshopoptions['cart']=$wpdb->escape($_POST['eshop_cart']);
			}else{
				$err=5;
			}	
		}else{
			$err=5;
		}

		if(is_numeric($_POST['eshop_cart_cancel'])){
			$ptitle=get_post($_POST['eshop_cart_cancel']);
			if($ptitle->post_title!=''){
				$eshopoptions['cart_cancel']=$wpdb->escape($_POST['eshop_cart_cancel']);
			}else{
				$err=6;
			}	
		}else{
			$err=6;
		}

		if(is_numeric($_POST['eshop_checkout'])){
			$ptitle=get_post($_POST['eshop_checkout']);
			if($ptitle->post_title!=''){
				$eshopoptions['checkout']=$wpdb->escape($_POST['eshop_checkout']);
			}else{
				$err=7;
			}	
		}else{
			$err=7;
		}

		if(is_numeric($_POST['eshop_cart_success'])){
			$ptitle=get_post($_POST['eshop_cart_success']);
			if($ptitle->post_title!=''){
				$eshopoptions['cart_success']=$wpdb->escape($_POST['eshop_cart_success']);
			}else{
				$err=8;
			}	
		}else{
			$err=8;
		}

		if(is_numeric($_POST['eshop_show_downloads'])){
			$ptitle=get_post($_POST['eshop_show_downloads']);
			if($ptitle->post_title!=''){
				$eshopoptions['show_downloads']=$wpdb->escape($_POST['eshop_show_downloads']);
			}else{
				$err=9;		
			}	
		}else{
			$err=9;		
		}
		if($_POST['eshop_details_tax']!=''){
			if(is_numeric($_POST['eshop_details_tax'])){
				$ptitle=get_post($_POST['eshop_details_tax']);
				if($ptitle->post_title!=''){
					$eshopoptions['details']['tax']=$wpdb->escape($_POST['eshop_details_tax']);
				}else{
					$err=10;		
				}	
			}else{
				$err=10;		
			}
		}else{
			$eshopoptions['details']['tax']=$wpdb->escape($_POST['eshop_details_tax']);
		}
		//lets redirect the post request into get request
		$_POST['_wp_http_referer']=add_query_arg('eshop_message',$err,$_POST['_wp_http_referer']);
		update_option('eshop_plugin_settings',$eshopoptions);
		wp_redirect($_POST['_wp_http_referer']);	
		exit;
	}
	function eclass_errors($messages){
		$messages=array(
		'1'=>__('The Privacy Policy page needs to be a valid page id number.','eshop'),
		'2'=>__('The Help page needs to be a valid page id number.','eshop'),
		'3'=>__('The Shipping rates page needs to be a valid page id number.','eshop'),
		'4'=>__('The Main Shop page needs to be a valid page id number.','eshop'),
		'5'=>__('The Cart page needs to be a valid page id number.','eshop'),
		'6'=>__('The Cancelled payment page needs to be a valid page id number.','eshop'),
		'7'=>__('The Checkout page needs to be a valid page id number.','eshop'),
		'8'=>__('The Successful payment page needs to be a page id number.','eshop'),
		'9'=>__('The Downloads page needs to be a page id number.','eshop'),
		'10'=>__('Tax rates page ID was not a valid page id number.','eshop'),
		'100'=>__('eShop Page settings updated.','eshop')
		);
		return $messages;
	}
}

class eshop_setting_downloads_class extends eshop_metabox_class {
	function on_load_page() {
		add_meta_box('eshop-downloads', __('eShop Downloads','eshop'), array(&$this, 'downloads_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-downloads-only', __('eShop Downloads Only','eshop'), array(&$this, 'downloads_box_2'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-downloads-emails', __('eShop emails','eshop'), array(&$this, 'downloads_box_3'), $this->pagehook, 'normal', 'core');
		add_filter('eshop_error_messages',array(&$this, 'eclass_errors'));
	}
	function downloads_box($eshopoptions) {
		?>
		<fieldset>
			<label for="eshop_downloads_num"><?php _e('Download attempts','eshop'); ?></label><input id="eshop_downloads_num" name="eshop_downloads_num" type="text" value="<?php echo $eshopoptions['downloads_num']; ?>" size="5" /><br />
		</fieldset>

		<fieldset>
			<p><?php _e('As some downloads can be quite large, people may experience errors if they try and download all files in one go.','eshop'); ?></p>
			<label for="eshop_downloads_hideall"><?php _e('Hide download all form','eshop'); ?></label>
			<select name="eshop_downloads_hideall" id="eshop_downloads_hideall">
			<?php
			if('yes' == $eshopoptions['downloads_hideall']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="no">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
			</select><br />
		</fieldset>
	<?php
	}
	function downloads_box_2($eshopoptions) {
		?>
		<fieldset>
			<p><?php _e('Change this setting only if you are using eShop for downloadable sales only, reduces the checkout form to bare minimum.','eshop'); ?></p>
			<label for="eshop_downloads_only"><?php _e('Downloads Only','eshop'); ?></label>
			<select name="eshop_downloads_only" id="eshop_downloads_only">
			<?php
			if('yes' == $eshopoptions['downloads_only']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="no">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
			</select><br />
		</fieldset>
		<?php
	}
	function downloads_box_3($eshopoptions) {
		?>
		<fieldset>
			<p><?php _e('By default the Downloads code and url is hidden from the Cash/Bank emails, as they have not been paid for.','eshop'); ?></p>
			<label for="eshop_downloads_email"><?php _e('Show Download details in emails','eshop'); ?></label>
			<select name="eshop_downloads_email" id="eshop_downloads_email">
			<?php
			if(isset($eshopoptions['downloads_email']) && 'yes' == $eshopoptions['downloads_email']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="no">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
			</select><br />
		</fieldset>
		<?php
	}
	function on_save_changes() {
		global $wpdb;
		$err='100';
		//user permission check
		if ( !current_user_can('eShop_admin') )
			wp_die( __('Cheatin&#8217; uh?') );			
		//cross check the given referer
		check_admin_referer('eshop-metaboxes-general');
		$eshopoptions = get_option('eshop_plugin_settings');
		//process here your on $_POST validation and / or option saving
		if(is_numeric($_POST['eshop_downloads_num'])){
			$eshopoptions['downloads_num']=$wpdb->escape($_POST['eshop_downloads_num']);
		}else{
			$err=1;
			$eshopoptions['downloads_num']='3';
		}
		$eshopoptions['downloads_email']=$wpdb->escape($_POST['eshop_downloads_email']);
		$eshopoptions['downloads_only']=$wpdb->escape($_POST['eshop_downloads_only']);
		$eshopoptions['downloads_hideall']=$wpdb->escape($_POST['eshop_downloads_hideall']);
		update_option('eshop_plugin_settings',$eshopoptions);
		//lets redirect the post request into get request
		
		$_POST['_wp_http_referer']=add_query_arg('eshop_message',$err,$_POST['_wp_http_referer']);
		
		wp_redirect($_POST['_wp_http_referer']);	
		exit;
	}
	function eclass_errors($messages){
		$messages=array(
		'1'=>__('Number of download attempts should be numeric, a default of 3 has been applied.','eshop'),
		'100'=>__('eShop Download settings updated.','eshop')
		);
		return $messages;
	}
}

class eshop_setting_discount_class extends eshop_metabox_class {
	function on_load_page() {
		add_meta_box('eshop-discounts', __('eShop Discounts','eshop'), array(&$this, 'admin_box'), $this->pagehook, 'normal', 'core');
		add_filter('eshop_error_messages',array(&$this, 'eclass_errors'));
	}
  
	function admin_box($eshopoptions) {
		?>
		<fieldset>
			<p><?php _e('In all cases deleting the entry will disable the discount.', 'eshop'); ?></p>
			<table class="hidealllabels widefat eshopdisc" summary="<?php _e('Discount for amount sold','eshop'); ?>">
			<caption><?php _e('Discount for amount sold','eshop'); ?></caption>
			<thead>
				<tr>
				<th id="elevel"><?php _e('Discounts','eshop'); ?></th>
				<th id="espend"><?php _e('Spend','eshop'); ?></th>
				<th id="ediscount"><?php _e('% Discount','eshop'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				for ($x=1;$x<=3;$x++){
				?>
				<tr>
				<th headers="elevel"  id="row<?php echo $x ?>"><?php echo $x ?></th>
				<td headers="elevel espend row<?php echo $x ?>"><label for="eshop_discount_spend<?php echo $x ?>"><?php _e('Spend','eshop'); ?></label><input id="eshop_discount_spend<?php echo $x ?>" name="eshop_discount_spend<?php echo $x ?>" type="text" value="<?php echo $eshopoptions['discount_spend'.$x]; ?>" size="5" /></td>
				<td headers="elevel ediscount row<?php echo $x ?>"><label for="eshop_discount_value<?php echo $x ?>"><?php _e('Discount','eshop'); ?></label><input id="eshop_discount_value<?php echo $x ?>" name="eshop_discount_value<?php echo $x ?>" type="text" value="<?php echo $eshopoptions['discount_value'.$x]; ?>" size="5" maxlength="5" /></td>
				</tr>
				<?php
				}
				?>
			</tbody>
			</table>
			<p><label for="eshop_discount_shipping"><?php _e('Spend over to get free shipping','eshop'); ?></label><input id="eshop_discount_shipping" name="eshop_discount_shipping" type="text" value="<?php echo $eshopoptions['discount_shipping']; ?>" size="5" /></p>
	</fieldset>
	<?php
	}
	function on_save_changes() {
		global $wpdb;
		$err='100';

		//user permission check
		if ( !current_user_can('eShop_admin') )
			wp_die( __('Cheatin&#8217; uh?') );			
		//cross check the given referer
		check_admin_referer('eshop-metaboxes-general');
		//process here your on $_POST validation and / or option saving
		$eshopoptions = get_option('eshop_plugin_settings');
		if(is_numeric($_POST['eshop_discount_shipping'])){
				$eshopoptions['discount_shipping']=$wpdb->escape($_POST['eshop_discount_shipping']);
		}elseif($_POST['eshop_discount_shipping']!=''){
			$err=1;
			$eshopoptions['discount_shipping']='';
		}else{
			$eshopoptions['discount_shipping']='';
		}

		for ($x=1;$x<=3;$x++){
			if(is_numeric($_POST['eshop_discount_spend'.$x]) && is_numeric($_POST['eshop_discount_value'.$x])){
				$eshopoptions['discount_spend'.$x]=$wpdb->escape($_POST['eshop_discount_spend'.$x]);
				$eshopoptions['discount_value'.$x]=$wpdb->escape($_POST['eshop_discount_value'.$x]);
			}elseif($_POST['eshop_discount_spend'.$x]=='' || $_POST['eshop_discount_value'.$x]=='') {
				$eshopoptions['discount_spend'.$x]='';
				$eshopoptions['discount_value'.$x]='';
				if(($_POST['eshop_discount_spend'.$x]!='' && $_POST['eshop_discount_value'.$x]=='') || ($_POST['eshop_discount_spend'.$x]=='' && $_POST['eshop_discount_value'.$x]!='')){
					$err=2;
				}
			}else{
				$err=3;
			}
			if($_POST['eshop_discount_value'.$x]>=100) {
				$err=4;
			}
		}
		update_option('eshop_plugin_settings',$eshopoptions);
		$_POST['_wp_http_referer']=add_query_arg('eshop_message',$err,$_POST['_wp_http_referer']);
		wp_redirect($_POST['_wp_http_referer']);
		exit;
	}
	function eclass_errors($messages){
		$messages=array(
		'1'=>__('"Spend over to get free shipping" must be numeric!','eshop'),
		'2'=>__('Either "Spend" or "% Discount" was not valid so both values were unset!','eshop'),
		'3'=>__('"Spend" and "% Discount" must be numeric!','eshop'),
		'4'=>__('<strong>Warning</strong> % Discount is equal to or over 100%!','eshop'),
		'100'=>__('eShop Discount settings updated.','eshop')
		);
		return $messages;
	}
}

class eshop_setting_general_class extends eshop_metabox_class {
	function on_load_page() {
		//add_meta_box('howto-metaboxes-sidebox-2', 'Sidebox 2 Title', array(&$this, 'on_sidebox_2_content'), $this->pagehook, 'side', 'core');
		add_meta_box('orders-display', __('Orders Display','eshop'), array(&$this, 'orders_box'), $this->pagehook, 'side', 'core');
		add_meta_box('eshop-cron', __('Cron','eshop'), array(&$this, 'cron_box'), $this->pagehook, 'side', 'low');
		add_meta_box('business-details', __('Business Details','eshop'), array(&$this, 'business_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('product-options', __('Product Options','eshop'), array(&$this, 'product_options_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('product-details', __('Product Details','eshop'), array(&$this, 'product_details_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('display-options', __('Display Options','eshop'), array(&$this, 'display_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('cart-options', __('Cart Options','eshop'), array(&$this, 'cart_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('checkout-options', __('Checkout Options','eshop'), array(&$this, 'checkout_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-cacheability', __('Cacheability','eshop'), array(&$this, 'cache_box'), $this->pagehook, 'side', 'low');
		add_filter('eshop_error_messages',array(&$this, 'eclass_errors'));
	}
	function cron_box ($eshopoptions){
		?>
		<fieldset>
		<label for="eshop_cron_email"><?php _e('Cron Email address','eshop'); ?></label><input id="eshop_cron_email" name="eshop_cron_email" type="text" value="<?php echo $eshopoptions['cron_email']; ?>" size="30" /><br />
		</fieldset>
		<?php
	}
	function business_box ($eshopoptions){
		?>
		<fieldset>
		<label for="eshop_from_email"><?php _e('eShop From email address','eshop'); ?></label><input id="eshop_from_email" name="eshop_from_email" type="text" value="<?php echo $eshopoptions['from_email']; ?>" size="30" /><br />
		<label for="eshop_sysemails"><?php _e('Available business email addresses','eshop'); ?></label>
		<textarea id="eshop_sysemails" name="eshop_sysemails" rows="5" cols="50"><?php echo $eshopoptions['sysemails']; ?></textarea>
		<br />
		</fieldset>
		<?php
	}
	function product_options_box ($eshopoptions){
		?>
		<fieldset>
		<label for="eshop_options_num"><?php _e('Options per product','eshop'); ?></label><input id="eshop_options_num" name="eshop_options_num" type="text" value="<?php echo $eshopoptions['options_num']; ?>" size="5" /><br />
		<label for="eshop_stock_control"><?php _e('Stock Control','eshop')._e(' <small>(Warning: setting this will make all products have zero stock, each one will have to be set manually.</small>)','eshop'); ?></label>
			<select name="eshop_stock_control" id="eshop_stock_control">
				<option value="yes" <?php selected($eshopoptions['stock_control'],'yes'); ?>><?php _e('Yes','eshop'); ?></option>
				<option value="no" <?php selected($eshopoptions['stock_control'],'no'); ?>><?php _e('No','eshop'); ?></option>
			</select><br />
			<strong><?php _e('Min/Max Quantities','eshop'); ?></strong>
			<?php 
				if(!isset($eshopoptions['min_qty'])) $eshopoptions['min_qty']='';
				if(!isset($eshopoptions['max_qty'])) $eshopoptions['max_qty']='';
			?>
			<label for="eshop_min_qty"><?php _e('Minimum purchase quantity per product (leave blank for no limit)','eshop'); ?></label><input id="eshop_min_qty" name="eshop_min_qty" type="text" value="<?php echo $eshopoptions['min_qty']; ?>" size="5" /><br />
			<label for="eshop_max_qty"><?php _e('Maximum purchase quantity per product (leave blank for no limit)','eshop'); ?></label><input id="eshop_max_qty" name="eshop_max_qty" type="text" value="<?php echo $eshopoptions['max_qty']; ?>" size="5" /><br />
			<?php if(!isset($eshopoptions['sale_prices'])) $eshopoptions['sale_prices']=0; ?>
			<label for="eshop_sale_prices"><?php _e('Use Sale prices','eshop'); ?></label>
			<select name="eshop_sale_prices" id="eshop_sale_prices">
				<option value="1" <?php selected($eshopoptions['sale_prices'],'1'); ?>><?php _e('Yes','eshop'); ?></option>
				<option value="0" <?php selected($eshopoptions['sale_prices'],'0'); ?>><?php _e('No','eshop'); ?></option>
			</select><br />
		</fieldset>
	
		<?php
	}
	function product_details_box ($eshopoptions){
		?>
		<fieldset>
			<?php
			if(!isset($eshopoptions['details'])){
				$eshopoptions['details']['show']='';
				$eshopoptions['details']['class']='';
				$eshopoptions['details']['hide']='';
				$eshopoptions['details']['display']='';
			}
			if(!isset($eshopoptions['details']['show']))
				$eshopoptions['details']['show']='';
			if(!isset($eshopoptions['details']['class']))
				$eshopoptions['details']['class']='';
			if(!isset($eshopoptions['details']['hide']))
				$eshopoptions['details']['hide']='';
			if(!isset($eshopoptions['details']['display']))
				$eshopoptions['details']['display']='';
			?>
			<p><strong><?php _e('For site wide display of Product Details, can be amended per product by the addition of the shortcode <code>[eshop_details]</code>','eshop'); ?></strong></p>
			<label for="eshop_details_display"><?php _e('Add product details section to every page with details to display. <small>(adding the shortcode will override settings here)</small>','eshop'); ?></label>
			<select name="eshop_details_display" id="eshop_details_display">
			<option value="yes" <?php selected($eshopoptions['details']['display'],'yes'); ?>><?php _e('Yes','eshop'); ?></option>
			<option value="no" <?php selected($eshopoptions['details']['display'],'no'); ?>><?php _e('No','eshop'); ?></option>
			</select><br />
			<label for="eshop_details_class"><?php _e('Class','eshop'); ?> <small><?php _e('(changes the standard class)','eshop'); ?></small></label><input id="eshop_details_class" name="eshop_details_class" type="text" value="<?php echo $eshopoptions['details']['class']; ?>" size="30" /><br />
			<label for="eshop_details_show"><?php _e('Show','eshop'); ?> <small><?php _e('(which details, separated by commas, to show and in which order - acceptable values and the default order: sku, description, options, optionset, shipping)','eshop'); ?></small></label><input id="eshop_details_show" name="eshop_details_show" type="text" value="<?php echo $eshopoptions['details']['show']; ?>" size="60" /><br />
			<label for="eshop_details_hide"><?php _e('Option Hide','eshop'); ?> <small><?php _e('(which details, separated by commas, to hide from the options and options sets and in which order - acceptable values: price, saleprice, tax, download, weight, filesize, stockqty)','eshop'); ?></small></label><input id="eshop_details_hide" name="eshop_details_hide" type="text" value="<?php echo $eshopoptions['details']['hide']; ?>" size="60" /><br />
			<label for="eshop_cart_nostock"><?php _e('Out of Stock message','eshop'); ?></label><input id="eshop_cart_nostock" name="eshop_cart_nostock" type="text" value="<?php echo $eshopoptions['cart_nostock']; ?>" size="30" /><br />

			
			<p><strong><?php _e('General product settings.','eshop'); ?></strong></p>
			<label for="eshop_currency_symbol"><?php _e('Symbol','eshop'); ?></label><input id="eshop_currency_symbol" name="eshop_currency_symbol" type="text" value="<?php echo $eshopoptions['currency_symbol']; ?>" size="10" /><br />
			<label for="eshop_addtocart_image"><?php _e('Use an add to cart image or button?','eshop'); ?></label>
			<select name="eshop_addtocart_image" id="eshop_addtocart_image">
			<?php
			if('img' == $eshopoptions['addtocart_image']){
				echo '<option value="img" selected="selected">'.__('Image','eshop').'</option>';
				echo '<option value="">'.__('Button','eshop').'</option>';
			}else{
				echo '<option value="img">'.__('Image','eshop').'</option>';
				echo '<option value="" selected="selected">'.__('Button','eshop').'</option>';
			}
			?>
			</select>
			<p><label for="eshop_ajax_cart"><?php _e('Add to cart, without going to cart page. This may not be suitable for all themes. Works best when displaying the full cart from the eShop Cart Widget.','eshop'); ?></label>
				<select name="eshop_ajax_cart" id="eshop_ajax_cart">
				<?php
				if(isset($eshopoptions['ajax_cart']) && 'yes' == $eshopoptions['ajax_cart']){
					echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
					echo '<option value="no">'.__('No','eshop').'</option>';
				}else{
					echo '<option value="yes">'.__('Yes','eshop').'</option>';
					echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
				}
			?>
			</select></p>
			<?php
			if(!isset($eshopoptions['cart_text'])) $eshopoptions['cart_text'] =''
			?>
			<p><strong><?php _e('Add text before or after addtocart form (will not appear in listings) - will accept HTML','eshop'); ?></strong></p>
			<label for="eshop_cart_text"><?php _e('Text to add','eshop'); ?></label><input id="eshop_cart_text" name="eshop_cart_text" type="text" value="<?php echo stripslashes(esc_attr($eshopoptions['cart_text'])); ?>" size="60" /><br />
			<label for="eshop_cart_text_where"><?php _e('Placement','eshop'); ?></label>
			<select name="eshop_cart_text_where" id="eshop_cart_text_where">
			<?php
			if(!isset($eshopoptions['cart_text_where'])) $eshopoptions['cart_text_where'] =''
			?>
				<option value="1" <?php selected($eshopoptions['cart_text_where'],'1'); ?>><?php _e('Before','eshop'); ?></option>
				<option value="2" <?php selected($eshopoptions['cart_text_where'],'2'); ?>><?php _e('After','eshop'); ?></option>
			</select>
		</fieldset>
		<?php
	}
	function display_box ($eshopoptions){
		?>
		<fieldset>
			<label for="eshop_show_forms"><?php _e('Show add to cart forms on WordPress post listings. <span class="warn"><span>Warning</span> this can invalidate your html!</span>','eshop'); ?></label>
			<select name="eshop_show_forms" id="eshop_show_forms">
			<?php
			if('yes' == $eshopoptions['show_forms']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="no">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
			</select><br />
			<label for="eshop_fold_menu"><?php _e('Hide sub pages from menu until top level page is visited.','eshop'); ?></label>
			<select name="eshop_fold_menu" id="eshop_fold_menu">
			<?php
			if('yes' == $eshopoptions['fold_menu']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="no">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
			</select><br />
			<label for="eshop_hide_cartco"><?php _e('Hide cart and checkout pages until items are in cart.','eshop'); ?></label>
			<select name="eshop_hide_cartco" id="eshop_hide_cartco">
			<?php
			if('yes' == $eshopoptions['hide_cartco']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="no">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
			</select><br />
			<label for="eshop_search_img"><?php _e('Add image to search results','eshop'); ?></label>
			<select name="eshop_search_img" id="eshop_search_img">
			<?php
			if('yes' == $eshopoptions['search_img']){
				echo '<option value="no">'.__('No','eshop').'</option>';
				echo '<option value="all">'.__('All pages and posts','eshop').'</option>';
				echo '<option value="yes" selected="selected">'.__('eShop products pages and posts only','eshop').'</option>';
			}elseif('all' == $eshopoptions['search_img']){
				echo '<option value="no">'.__('No','eshop').'</option>';
				echo '<option value="all" selected="selected">'.__('All pages and posts','eshop').'</option>';
				echo '<option value="yes">'.__('eShop products pages and posts only','eshop').'</option>';
			}else{
				echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
				echo '<option value="all">'.__('All pages and posts','eshop').'</option>';
				echo '<option value="yes">'.__('eShop products pages and posts only','eshop').'</option>';
			}
			?>
			</select><br />
			<label for="eshop_credits"><?php _e('Display eShop credits','eshop'); ?></label>
			<select name="eshop_credits" id="eshop_credits">
			<?php
			if('yes' == $eshopoptions['credits']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="no">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
			</select><br />
		</fieldset>
		<?php
	}
	function cart_box ($eshopoptions){
		?>
		<fieldset>
			<label for="eshop_image_in_cart"><?php _e('Percentage size of thumbnail image shown in cart - leave blank to not show the image.','eshop'); ?></label><input id="eshop_image_in_cart" name="eshop_image_in_cart" type="text" value="<?php echo $eshopoptions['image_in_cart']; ?>" size="5" /><br />
		</fieldset>
		<?php
	}
	function checkout_box ($eshopoptions){
		?>
		<fieldset>
		<label for="eshop_hide_addinfo"><?php _e('Hide the Additional information form fields.','eshop'); ?></label>
			<select name="eshop_hide_addinfo" id="eshop_hide_addinfo">
			<?php
			if('yes' == $eshopoptions['hide_addinfo']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
			</select><br />
		<label for="eshop_hide_shipping"><?php _e('Hide the shipping address form fields.','eshop'); ?></label>
			<select name="eshop_hide_shipping" id="eshop_hide_shipping">
			<?php
			if('yes' == $eshopoptions['hide_shipping']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
		</select><br />
		<label for="eshop_downloads_only"><?php _e('Minimal checkout form. (this also disables shipping costs)','eshop'); ?></label>
		<select name="eshop_downloads_only" id="eshop_downloads_only">
		<?php
		if('yes' == $eshopoptions['downloads_only']){
			echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
			echo '<option value="no">'.__('No','eshop').'</option>';
		}else{
			echo '<option value="yes">'.__('Yes','eshop').'</option>';
			echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
		}
		?>
		</select><br />
		<label for="eshop_tandc_use"><?php _e('Add a required checkbox to the checkout.','eshop'); ?></label>
		<select name="eshop_tandc_use" id="eshop_tandc_use">
			<?php
			if('yes' == $eshopoptions['tandc_use']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
		</select><br />
		<label for="eshop_tandc"><?php _e('Text for the required checkbox.','eshop'); ?></label><input id="eshop_tandc" name="eshop_tandc" type="text" value="<?php echo $eshopoptions['tandc']; ?>" size="60" /><br />
		<label for="eshop_tandc_id"><?php _e('Page id (transforms text above into a link).','eshop'); ?></label><input id="eshop_tandc_id" name="eshop_tandc_id" type="text" value="<?php echo $eshopoptions['tandc_id']; ?>" size="6" /><br />
		<?php if (eshop_wp_version('3')){ ?>
		<label for="eshop_users"><?php _e('Allow users to sign up to your site.','eshop'); ?></label>
			<select name="eshop_users" id="eshop_users">
				<?php
				if(isset($eshopoptions['users']) && 'yes' == $eshopoptions['users']){
					echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
					echo '<option value="">'.__('No','eshop').'</option>';
				}else{
					echo '<option value="yes">'.__('Yes','eshop').'</option>';
					echo '<option value="" selected="selected">'.__('No','eshop').'</option>';
				}
				?>
			</select><br />
			<?php
			if(!isset($eshopoptions['users_text'])) $eshopoptions['users_text']='';
			?>
			<label for="eshop_users_text"><?php _e('Change sign up text.','eshop'); ?></label><input id="eshop_users_text" name="eshop_users_text" type="text" value="<?php echo $eshopoptions['users_text']; ?>" size="60" /><br />
		
		<label for="eshop_zero"><?php _e('Allow zero cost orders.','eshop'); ?></label>
			<select name="eshop_zero" id="eshop_zero">
				<?php
				if(!isset($eshopoptions['zero'])) $eshopoptions['zero']='';
				if('1' == $eshopoptions['zero']){
					echo '<option value="1" selected="selected">'.__('Yes','eshop').'</option>';
					echo '<option value="0">'.__('No','eshop').'</option>';
				}else{
					echo '<option value="1">'.__('Yes','eshop').'</option>';
					echo '<option value="0" selected="selected">'.__('No','eshop').'</option>';
				}
				?>
			</select><br />
		
		<?php } ?>
		</fieldset>
		<?php
	}
	function cache_box ($eshopoptions){
		?>
		<fieldset>
		<label for="eshop_set_cacheability"><?php _e('Disable WP Supercache for eShop pages including cart, checkout and pages using shortcodes.','eshop'); ?></label>
		<select name="eshop_set_cacheability" id="eshop_set_cacheability">
		<?php
		if('yes' == $eshopoptions['set_cacheability']){
			echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
			echo '<option value="no">'.__('No','eshop').'</option>';
		}else{
			echo '<option value="yes">'.__('Yes','eshop').'</option>';
			echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
		}
		?>
		</select><br />
		</fieldset>
		<?php
	}
	function admin_box($eshopoptions) {
		?>
		<fieldset>
			<label for="eshop_status"><?php _e('Status','eshop'); ?></label>
			<select name="eshop_status" id="eshop_status">
			<?php
			if('live' == $eshopoptions['status']){
				echo '<option value="live" selected="selected">'.__('Live','eshop').'</option>';
				echo '<option value="testing">'.__('Testing','eshop').'</option>';
			}else{
				echo '<option value="live">'.__('Live','eshop').'</option>';
				echo '<option value="testing" selected="selected">'.__('Testing','eshop').'</option>';
			}
			?>
			</select><br />
			<label for="eshop_sale"><?php _e('Sale','eshop'); ?></label>
			<select name="eshop_sale" id="eshop_sale">
			<?php
			if(isset($eshopoptions['sale']) && 'yes' == $eshopoptions['sale']){
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="no">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
			}
			?>
			</select><br />
		</fieldset>
	<?php
	}
	function orders_box($eshopoptions) {
			?>
			<fieldset>
				<label for="eshop_records"><?php _e('Orders per page','eshop'); ?></label><input id="eshop_records" name="eshop_records" type="text" value="<?php echo $eshopoptions['records']; ?>" size="5" /><br />
			</fieldset>
		<?php
	}
	function on_show_page() {
			//we need the global screen column value to beable to have a sidebar in WordPress 2.8
			global $screen_layout_columns, $wpdb;
			$eshopoptions = get_option('eshop_plugin_settings');
	
			//set up submenu here so it can accessed in the code
			if(isset($_POST['eshop-action-status']))
				$_GET['mstatus']=$_POST['eshop-action-status'];
			if (isset($_GET['mstatus']) )
				$action_status = esc_attr($_GET['mstatus']);
			else
				$_GET['mstatus']=$action_status = 'General';
			$stati=array('General'=>__('General','eshop'),'Merchant' => __('Merchant Gateways','eshop'),'Tax' => __('Sales Tax','eshop'),'Discounts' => __('Discounts','eshop'),'Downloads' => __('Downloads','eshop'),'Pages' => __('Special Pages','eshop'),'Base'=>__('eShop Base','eshop'));
			foreach ( $stati as $status => $label ) {
				$class = '';
				if ( $status == $action_status )
					$class = ' nav-tab-active';
				$dalink=add_query_arg('page',ESHOP_SETTINGS_PAGE_NAME,'options-general.php');
				$dalink=add_query_arg('mstatus',$status,$dalink);
				$dalink=esc_url($dalink);
				$status_links[] = '<li><a href="'.$dalink.'" class="nav-tab'.$class.'">'. $label . '</a>';
			}
			//end submenu
		
			//add a 3rd content box now for demonstration purpose, boxes added at start of page rendering can't be switched on/off, 
			//may be needed to ensure that a special box is always available
			add_meta_box('eshop-admin', __('eShop Status','eshop'), array(&$this, 'admin_box'), $this->pagehook, 'side', 'high');

			//add_meta_box('howto-metaboxes-contentbox-3', 'Contentbox 3 Title (impossible to hide)', array(&$this, 'on_contentbox_3_content'), $this->pagehook, 'normal', 'core');
			//define some data can be given to each metabox during rendering
			$data = $eshopoptions;
	
			echo '<div id="eshop-metaboxes-general" class="wrap">';
			echo '<div id="eshopicon" class="icon32"></div><h2>'.__('eShop Settings','eshop').'</h2>'."\n";
			//info:
			eshop_check_error();
			eshop_admin_mode();
			//the submenu 
			echo '<ul class="nav-tab-wrapper">';
			echo implode('</li>', $status_links) . '</li>';
			echo '</ul><br class="clear" />';
			?>
			<form method="post" action="admin-post.php" id="eshop-settings">
			<input type='hidden' name='option_page' value='eshop_settings' />
				<?php wp_nonce_field('eshop-metaboxes-general'); ?>
				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
				<input type="hidden" name="action" value="save_eshop_metaboxes_general" />
				
				<input type="hidden" name="eshop-action-status" value="<?php echo $action_status; ?>" />
	
				<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
					<div id="side-info-column" class="inner-sidebar">
					<p class="submit">
						<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes','eshop') ?>" />
					</p>
						<?php do_meta_boxes($this->pagehook, 'side', $data); ?>
					</div>
					<div id="post-body" class="has-sidebar">
						<div id="post-body-content" class="has-sidebar-content">
							<?php do_meta_boxes($this->pagehook, 'normal', $data); ?>
							<?php 
							//don't intend to use this at this time
							//do_meta_boxes($this->pagehook, 'additional', $data); 
							?>
							<p class="submit">
								<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes','eshop') ?>" />
							</p>
						</div>
					</div>
					<br class="clear"/>
									
				</div>	
			</form>
			</div>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			});
			//]]>
		</script>
			
			<?php
	}
	function on_save_changes() {
		global $wpdb;

		//user permission check
		if ( !current_user_can('eShop_admin') )
			wp_die( __('Cheatin&#8217; uh?') );			
		//cross check the given referer
		check_admin_referer('eshop-metaboxes-general');
		$err='100';
		$eshopoptions = get_option('eshop_plugin_settings');
		//process here your on $_POST validation and / or option saving
		$eshopoptions['sale']=$wpdb->escape($_POST['eshop_sale']);
		$eshopoptions['from_email']=$wpdb->escape($_POST['eshop_from_email']);
		$eshopoptions['cron_email']=$wpdb->escape($_POST['eshop_cron_email']);
		$eshopoptions['sysemails']=$wpdb->escape($_POST['eshop_sysemails']);
		$eshopoptions['currency_symbol']=$wpdb->escape($_POST['eshop_currency_symbol']);
		$eshopoptions['cart_nostock']=$wpdb->escape($_POST['eshop_cart_nostock']);
		$eshopoptions['credits']=$wpdb->escape($_POST['eshop_credits']);
		$eshopoptions['fold_menu']=$wpdb->escape($_POST['eshop_fold_menu']);
		$eshopoptions['hide_cartco']=$wpdb->escape($_POST['eshop_hide_cartco']);
		$eshopoptions['stock_control']=$wpdb->escape($_POST['eshop_stock_control']);
		$eshopoptions['min_qty']=$wpdb->escape($_POST['eshop_min_qty']);
		$eshopoptions['max_qty']=$wpdb->escape($_POST['eshop_max_qty']);
		$eshopoptions['search_img']=$wpdb->escape($_POST['eshop_search_img']);
		$eshopoptions['show_forms']=$wpdb->escape($_POST['eshop_show_forms']);
		$eshopoptions['addtocart_image']=$wpdb->escape($_POST['eshop_addtocart_image']);
		$eshopoptions['hide_addinfo']=$wpdb->escape($_POST['eshop_hide_addinfo']);
		$eshopoptions['hide_shipping']=$wpdb->escape($_POST['eshop_hide_shipping']);
		$eshopoptions['tandc']=$wpdb->escape($_POST['eshop_tandc']);
		$eshopoptions['tandc_use']=$wpdb->escape($_POST['eshop_tandc_use']);
		$eshopoptions['tandc_id']=$wpdb->escape($_POST['eshop_tandc_id']);
		$eshopoptions['set_cacheability']=$wpdb->escape($_POST['eshop_set_cacheability']);
		$eshopoptions['cart_text']=$_POST['eshop_cart_text'];
		$eshopoptions['cart_text_where']=$wpdb->escape($_POST['eshop_cart_text_where']);
		$eshopoptions['sale_prices']=$wpdb->escape($_POST['eshop_sale_prices']);

		if (eshop_wp_version('3')){
			$eshopoptions['users']=$wpdb->escape($_POST['eshop_users']);
			$eshopoptions['users_text']=$wpdb->escape($_POST['eshop_users_text']);
		}
		$eshopoptions['zero']=$wpdb->escape($_POST['eshop_zero']);
		$remove = array("&#039;", '&quot;', '"',"'","!"," ");

		$eshopoptions['details']['show']=$wpdb->escape(str_replace($remove, "", $_POST['eshop_details_show']));
		$eshopoptions['details']['class']=$wpdb->escape(str_replace($remove, "", $_POST['eshop_details_class']));
		$eshopoptions['details']['hide']=$wpdb->escape(str_replace($remove, "", $_POST['eshop_details_hide']));
		
		$eshopoptions['details']['display']=$wpdb->escape($_POST['eshop_details_display']);
		//minimal form
		$eshopoptions['downloads_only']=$wpdb->escape($_POST['eshop_downloads_only']);
		//ajax
		$eshopoptions['ajax_cart']=$wpdb->escape($_POST['eshop_ajax_cart']);
		//error grabbing
		if(is_numeric($_POST['eshop_records'])){
			$eshopoptions['records']=$wpdb->escape($_POST['eshop_records']);
		}else{
			$err='1';
			$eshopoptions['records']='10';
		}
		if(is_numeric($_POST['eshop_options_num']) && $_POST['eshop_options_num']>'0'){
			$eshopoptions['options_num']=$wpdb->escape($_POST['eshop_options_num']);
		}else{
			$err='2';
			$eshopoptions['options_num']='3';
		}

		if(is_numeric($_POST['eshop_image_in_cart']) || $_POST['eshop_image_in_cart']==''){
			$eshopoptions['image_in_cart']=$wpdb->escape($_POST['eshop_image_in_cart']);
		}else{
			$err='3';
			$eshopoptions['image_in_cart']='75';
		}
		if($_POST['eshop_currency_symbol']==''){
			$err='4';
			$eshopoptions['currency_symbol']='$';
		}
		
		if($_POST['eshop_status']=='live'){
			if(!is_array($eshopoptions['method']))
				$err='5';
			if($eshopoptions['from_email']=='')
				$err='6';
			if($err=='100')
				$eshopoptions['status']=$wpdb->escape($_POST['eshop_status']);
		}else{
			$eshopoptions['status']=$wpdb->escape($_POST['eshop_status']);
		}

		update_option('eshop_plugin_settings',$eshopoptions);
		//lets redirect the post request into get request 
		$_POST['_wp_http_referer']=add_query_arg('eshop_message',$err,$_POST['_wp_http_referer']);
		
		wp_redirect($_POST['_wp_http_referer']);		
	}
	function eclass_errors($messages){
		$messages=array(
		'1'=>__('Orders per page should be numeric, a default of 10 has been applied.','eshop'),
		'2'=>__('Options per product should be numeric and be greater than 0, a default of 3 has been applied.','eshop'),
		'3'=>__('The number entered for the image in the cart must be numeric, a default of 75 has been applied.','eshop'),
		'4'=>__('Currency Symbol was missing, the default $ has been applied.','eshop'),
		'5'=>__('You must have a Merchant Gateway selected before you can go live!','eshop'),
		'6'=>__('You must have set an eShop from email address before you can go live!','eshop'),
		'100'=>__('eShop general settings updated.','eshop')
		);
		return $messages;
	}
}


class eshop_setting_merchant extends eshop_metabox_class {
	function on_load_page() {
		add_meta_box('eshop-m-general', __('General Settings','eshop'), array(&$this, 'general_box'), $this->pagehook, 'side', 'core');
		add_meta_box('eshop-m-paypal', __('Paypal','eshop'), array(&$this, 'paypal_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-m-payson', __('Payson','eshop'), array(&$this, 'payson_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-m-ideal', __('iDeal Lite','eshop'), array(&$this, 'ideal_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-m-epn', __('eProcessingNetwork','eshop'), array(&$this, 'epn_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-m-cash', __('Cash','eshop'), array(&$this, 'cash_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-m-bank', __('Bank (wire transfer)','eshop'), array(&$this, 'bank_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-m-webtopay', __('Webtopay','eshop'), array(&$this, 'webtopay_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-m-authorizenet', __('Authorize.net','eshop'), array(&$this, 'authorizenet_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-m-ogone', __('ogone','eshop'), array(&$this, 'ogone_box'), $this->pagehook, 'normal', 'core');
		add_filter('eshop_error_messages',array(&$this, 'eclass_errors'));
		do_action('eshop_setting_merchant_load', array(&$this));

	}
	function general_box($eshopoptions) {
		global $wpdb;
		?>
		<fieldset>
			<label for="eshop_location"><?php _e('Business Location','eshop'); ?></label>
			<select name="eshop_location" id="eshop_location">
			<?php
			$ctable=$wpdb->prefix.'eshop_countries';
			$currentlocations=$wpdb->get_results("SELECT * from $ctable ORDER BY country");
			//$currentlocations=array('GB', 'US', 'JP', 'CA', 'DE');
			foreach ($currentlocations as $row){
				if($row->code == $eshopoptions['location']){
					$sel=' selected="selected"';
				}else{
					$sel='';
				}
				echo '<option value="'. $row->code .'"'. $sel .'>'. $row->country .'</option>';
			}
			?>
			</select><br />
			<p><?php printf(__('Don\'t forget to set the<br /><a href="%s">State/County/Province</a><br />on the shipping pages.','eshop'),'admin.php?page=eshop-shipping.php&amp;action=states'); ?></p>
			<label for="eshop_currency"><?php _e('Currency Code','eshop'); ?></label>
			<select name="eshop_currency" id="eshop_currency">
			<?php
			$currencycodes=array(
			'GBP'=>__('Pounds Sterling','eshop'),
			'USD'=>__('U.S. Dollars','eshop'),
			'EUR'=>__('Euros','eshop'),
			'ARS'=>__('Argentinean Pesos','eshop'),
			'AUD'=>__('Australian Dollars','eshop'),
			'BRL'=>__('Brazilian Real','eshop'),
			'BGN'=>__('Bulgarian Lev'),
			'CAD'=>__('Canadian Dollars','eshop'),
			'CZK'=>__('Czech Koruna','eshop'),
			'DKK'=>__('Danish Krone','eshop'),
			'HKD'=>__('Hong Kong Dollar','eshop'),
			'HUF'=>__('Hungarian Forint','eshop'),
			'INR'=>__('Indian Rupee','eshop'),
			'ILS'=>__('Israeli Shekel','eshop'),
			'JPY'=>__('Japan Yen','eshop'),
			'LVL'=>__('Latvijas lats','eshop'),
			'LTL'=>__('Lithuanian Litas','eshop'),
			'MXN'=>__('Mexican Peso','eshop'),
			'NZD'=>__('New Zealand Dollar','eshop'),
			'NOK'=>__('Norwegian Krone','eshop'),
			'PHP'=>__('Philippine Pesos','eshop'),
			'PLN'=>__('Polish Zloty','eshop'),
			'MYR'=>__('Ringgit Malaysia','eshop'),
			'SGD'=>__('Singapore Dollar','eshop'),
			'ZAR'=>__('South African Rand','eshop'),
			'SEK'=>__('Swedish Krona','eshop'),
			'CHF'=>__('Swiss Franc','eshop'),
			'TRY' =>__('Turkish Lira','eshop')
			);
			$currencycodes=apply_filters('eshop_currency_codes',$currencycodes);
			foreach($currencycodes as $code=>$codename){
				if($code == $eshopoptions['currency']){
					$sel=' selected="selected"';
				}else{
					$sel='';
				}
				echo '<option value="'. $code .'"'. $sel .'>'. $codename.' ('.$code.')' .'</option>';
			}
			?>
			</select><br />
		</fieldset>
	<?php
	}
	function paypal_box($eshopoptions) {
	?>
		<fieldset>
			<?php 		
			$this->show_img('paypal');
			if(!isset($eshopoptions['business_sec'])) $eshopoptions['business_sec']='';
			?>
			<p class="cbox"><input id="eshop_method" name="eshop_method[]" type="checkbox" value="paypal"<?php if(in_array('paypal',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_method" class="eshopmethod"><?php _e('Accept payment by Paypal','eshop'); ?></label></p>
			<label for="eshop_business"><?php _e('Main account Email address','eshop'); ?></label><input id="eshop_business" name="eshop_business" type="text" value="<?php echo $eshopoptions['business']; ?>" size="30" /><br />
			<label for="eshop_business_sec"><?php _e('Secondary Email address','eshop'); ?></label><input id="eshop_business_sec" name="eshop_business_sec" type="text" value="<?php echo $eshopoptions['business_sec']; ?>" size="30" /><br />

			<label for="eshop_paypal_noemail"><?php _e('Send buyers email address to paypal?','eshop'); ?> <small>(<?php _e('This affects the form that Paypal shows to the customer.','eshop'); ?>)</small></label>
			<select name="eshop_paypal_noemail" id="eshop_paypal_noemail">
			<?php
			if('no' == $eshopoptions['paypal_noemail']){
				echo '<option value="yes">'.__('Yes','eshop').'</option>';
				echo '<option value="no" selected="selected">'.__('No','eshop').'</option>';
			}else{
				echo '<option value="yes" selected="selected">'.__('Yes','eshop').'</option>';
				echo '<option value="no">'.__('No','eshop').'</option>';
			}
			?>
			</select><br />
		</fieldset>
		<?php
	}
	function payson_box($eshopoptions) {
		?>
		<fieldset>
			<?php 		
			$this->show_img('payson');
			?>
			<p><?php _e('<strong>Warning:</strong> Payson has a minimum purchase value of 4 SEK (when last checked). All payments to Payson are in SEK, irrespective of settings above.','eshop'); ?></p>
			<?php 
			if(isset($eshopoptions['payson']))
				$payson = $eshopoptions['payson']; 
			else
				$payson['email']=$payson['id'] = $payson['key']= $payson['description']=$payson['minimum']='';	
			?>
			<p class="cbox"><input id="eshop_methodb" name="eshop_method[]" type="checkbox" value="payson"<?php if(in_array('payson',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_methodb" class="eshopmethod"><?php _e('Accept payment by Payson','eshop'); ?></label></p>
			<label for="eshop_paysonemail"><?php _e('Email address','eshop'); ?></label><input id="eshop_paysonemail" name="payson[email]" type="text" value="<?php echo $payson['email']; ?>" size="30" maxlength="50" /><br />
			<label for="eshop_paysonid"><?php _e('Agent ID','eshop'); ?></label><input id="eshop_paysonid" name="payson[id]" type="text" value="<?php echo $payson['id']; ?>" size="20" /><br />
			<label for="eshop_paysonkey"><?php _e('Secret Key','eshop'); ?></label><input id="eshop_paysonkey" name="payson[key]" type="text" value="<?php echo $payson['key']; ?>" size="40" /><br />
			<label for="eshop_paysondesc"><?php _e('Cart Description','eshop'); ?></label><input id="eshop_paysondesc" name="payson[description]" type="text" value="<?php echo $payson['description']; ?>" size="50" maxlength="200" /><br />
			<label for="eshop_paysonmin"><?php _e('Min. Cart value','eshop'); ?></label>
			<select name="payson[minimum]" id="eshop_paysonmin">
			<?php
			for($i=1;$i<=20;$i++){
			?>
			<option value="<?php echo $i; ?>"<?php if($payson['minimum']==$i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
			<?php
			}
			?>
			</select><br />
		</fieldset>
		<?php
	}
	function ideal_box($eshopoptions) {
		?>
		<fieldset>
			<?php 		
			$this->show_img('ideallite');
 
			if(isset($eshopoptions['ideallite']))
				$ideallite = $eshopoptions['ideallite']; 
			else
				$ideallite['idealdescription']=$ideallite['idealownermail'] = $ideallite['IDEAL_AQUIRER']=$ideallite['IDEAL_HASH_KEY'] = $ideallite['IDEAL_MERCHANT_ID']= $ideallite['IDEAL_SUB_ID']=$ideallite['IDEAL_TEST_MODE']='';
			?>
			<p class="cbox"><input id="eshop_methodc" name="eshop_method[]" type="checkbox" value="ideallite"<?php if(in_array('ideallite',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_methodc" class="eshopmethod"><?php _e('Accept payment by iDeal Lite','eshop'); ?></label></p>
			<label for="eshop_IDEAL_AQUIRER"><?php _e('Aquirer','eshop'); ?></label><input id="eshop_IDEAL_AQUIRER" name="ideallite[IDEAL_AQUIRER]" type="text" value="<?php echo $ideallite['IDEAL_AQUIRER']; ?>" size="40" maxlength="50" /><em><?php _e('Use Rabobank, ING Bank or Simulator','eshop'); ?></em><br />
			<label for="eshop_IDEAL_HASH_KEY"><?php _e('Hash Key','eshop'); ?></label><input id="eshop_IDEAL_HASH_KEY" name="ideallite[IDEAL_HASH_KEY]" type="text" value="<?php echo $ideallite['IDEAL_HASH_KEY']; ?>" size="20" /><em><?php _e('For Simulator use "Password"','eshop'); ?></em><br />
			<label for="eshop_IDEAL_MERCHANT_ID"><?php _e('Merchant ID','eshop'); ?></label><input id="eshop_IDEAL_MERCHANT_ID" name="ideallite[IDEAL_MERCHANT_ID]" type="text" value="<?php echo $ideallite['IDEAL_MERCHANT_ID']; ?>" size="40" /><em><?php _e('For Simulator use "123456789"','eshop'); ?></em><br />
			<label for="eshop_IDEAL_SUB_ID"><?php _e('Sub ID','eshop'); ?></label><input id="eshop_IDEAL_SUB_ID" name="ideallite[IDEAL_SUB_ID]" type="text" value="<?php echo $ideallite['IDEAL_SUB_ID']; ?>" size="40" /><em><?php _e('Unless you know what you\'re doing. Leave this to "0"','eshop'); ?></em><br />
			<label for="eshop_IDEAL_TEST_MODE"><?php _e('Test Mode','eshop'); ?></label><input id="eshop_IDEAL_TEST_MODE" name="ideallite[IDEAL_TEST_MODE]" type="text" value="<?php echo $ideallite['IDEAL_TEST_MODE']; ?>" size="20" maxlength="20" /><em><?php _e('Use "true" or "false"','eshop'); ?></em><br />
			<br />
			<label for="eshop_idealownermail"><?php _e('Email address','eshop'); ?></label><input id="eshop_idealownermail" name="ideallite[idealownermail]" type="text" value="<?php echo $ideallite['idealownermail']; ?>" size="40" maxlength="30" /><em><?php _e('Order notifications are sent to this address.','eshop'); ?></em><br />
			<label for="eshop_idealdescription"><?php _e('Description','eshop'); ?></label><input id="eshop_idealdescription" name="ideallite[idealdescription]" type="text" value="<?php echo $ideallite['idealdescription']; ?>" size="40" maxlength="30" /><em><?php _e('Description for the iDEAL payment','eshop'); ?></em><br />
			<br />
		</fieldset>
		<?php
	}
	function epn_box($eshopoptions) {
		global $wpdb;
		?>
		<fieldset>
			<?php 		
			$this->show_img('epn');
			?>
			<p><?php _e('<strong>Warning:</strong> All payments to eProcessingNetwork are in USD, irrespective of settings above. In test mode totals ending in a single cent are always failed.','eshop'); ?></p>
			<?php 
			if(isset($eshopoptions['epn']))
				$epn = $eshopoptions['epn']; 
			else
				$epn['email']=$epn['id'] = $epn['description']='';	
			?>
			<p class="cbox"><input id="eshop_methodd" name="eshop_method[]" type="checkbox" value="epn"<?php if(in_array('epn',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_methodd" class="eshopmethod"><?php _e('Accept payment by eProcessingNetwork','eshop'); ?></label></p>
			<label for="eshop_epnemail"><?php _e('Email address','eshop'); ?></label><input id="eshop_epnemail" name="epn[email]" type="text" value="<?php echo $epn['email']; ?>" size="30" /><br />
			<label for="eshop_epnid"><?php _e('User ID','eshop'); ?></label><input id="eshop_epnid" name="epn[id]" type="text" value="<?php echo $epn['id']; ?>" size="20" /><br />
			<label for="eshop_epndesc"><?php _e('Cart Description','eshop'); ?></label><input id="eshop_epndesc" name="epn[description]" type="text" value="<?php echo $epn['description']; ?>" size="50" maxlength="200" /><br />
		</fieldset>
	<?php
	}
	function cash_box($eshopoptions) {
		global $wpdb;
		?>
		<fieldset>
			<?php 		
			$this->show_img('cash');
			?>
			<p><?php _e('<strong>Note:</strong> payment by other means, usually used for offline payments.','eshop'); ?></p>
			<?php 
			if(isset($eshopoptions['cash']))
				$eshopcash = $eshopoptions['cash']; 
			else
				$eshopcash['email']=$eshopcash['rename'] = '';	
			?>
			<p class="cbox"><input id="eshop_methode" name="eshop_method[]" type="checkbox" value="cash"<?php if(in_array('cash',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_methode" class="eshopmethod"><?php _e('Accept cash payments','eshop'); ?></label></p>
			<label for="eshop_cashemail"><?php _e('Email address','eshop'); ?></label><input id="eshop_cashemail" name="cash[email]" type="text" value="<?php echo $eshopcash['email']; ?>" size="30" maxlength="50" /><br />
			<label for="eshop_cashrename"><?php _e('Change Cash name to','eshop'); ?></label><input id="eshop_cashrename" name="cash[rename]" type="text" value="<?php echo $eshopcash['rename']; ?>" size="30" maxlength="50" /><br />

		</fieldset>
	<?php
	}
	function bank_box($eshopoptions) {
		global $wpdb;
		?>
		<fieldset>
			<?php 		
			$this->show_img('bank');
			?>
			<p><?php _e('<strong>Note:</strong> payment by other means, such as wire transfer - behaves just like Cash.','eshop'); ?></p>
			<?php 
			if(isset($eshopoptions['bank']))
				$eshopbank = $eshopoptions['bank']; 
			else
				$eshopbank['email']=$eshopbank['rename'] = '';	
			?>
			<p class="cbox"><input id="eshop_methodbk" name="eshop_method[]" type="checkbox" value="bank"<?php if(in_array('bank',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_methodbk" class="eshopmethod"><?php _e('Accept bank payments','eshop'); ?></label></p>
			<label for="eshop_bankemail"><?php _e('Email address','eshop'); ?></label><input id="eshop_bankemail" name="bank[email]" type="text" value="<?php echo $eshopbank['email']; ?>" size="30" maxlength="50" /><br />
			<label for="eshop_bankrename"><?php _e('Change bank name to','eshop'); ?></label><input id="eshop_bankrename" name="bank[rename]" type="text" value="<?php echo $eshopbank['rename']; ?>" size="30" maxlength="50" /><br />

		</fieldset>	
		<?php
	}
	function webtopay_box($eshopoptions) {
		global $wpdb;
		?>
		<fieldset>
			<?php 		
			$this->show_img('webtopay');

			if(isset($eshopoptions['webtopay']))
				$eshopwebtopay = $eshopoptions['webtopay']; 
			else
				$eshopwebtopay['password']=$eshopwebtopay['id'] = $eshopwebtopay['lang']= $eshopwebtopay['projectid']=$eshopwebtopay['signature']=$eshopwebtopay['email']='';	
			?>
			<p class="cbox"><input id="eshop_methodf" name="eshop_method[]" type="checkbox" value="webtopay"<?php if(in_array('webtopay',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_methodf" class="eshopmethod"><?php _e('Accept webtopay payments','eshop'); ?></label></p>
			<label for="eshop_webtopayid"><?php _e('Webtopay user ID','eshop'); ?></label>
			<input id="eshop_webtopayid" name="webtopay[id]" type="text" value="<?php echo $eshopwebtopay['id']; ?>" size="30" maxlength="50" /><br />
			<label for="eshop_webtopaylang"><?php _e('Webtopay language (ENG ESP EST FIN FRE GEO GER ITA LAV LIT NOR POL ROU RUS SPA SWE)','eshop'); ?></label>
			<input id="eshop_webtopaylang" name="webtopay[lang]" type="text" value="<?php echo $eshopwebtopay['lang']; ?>" size="30" maxlength="50" /><br />
			<label for="eshop_webtopayprojectid"><?php _e('Webtopay project ID','eshop'); ?></label>
			<input id="eshop_webtopayprojectid" name="webtopay[projectid]" type="text" value="<?php echo $eshopwebtopay['projectid']; ?>" size="30" maxlength="50" /><br />
			<label for="eshop_webtopaysignature"><?php _e('Webtopay signature password','eshop'); ?></label>
			<input id="eshop_webtopaysignature" name="webtopay[signature]" type="text" value="<?php echo $eshopwebtopay['signature']; ?>" size="40" maxlength="50" /><br />
			<label for="eshop_webtopayemail"><?php _e('Email address','eshop'); ?></label>
			<input id="eshop_webtopayemail" name="webtopay[email]" type="text" value="<?php echo $eshopwebtopay['email']; ?>" size="30" maxlength="50" /><br />

		</fieldset>
		<?php
	}
	function authorizenet_box($eshopoptions) {
		global $wpdb;
		?>
		<fieldset>
			<?php 
			$this->show_img('authorizenet');

			if(isset($eshopoptions['authorizenet']))
				$authorizenet = $eshopoptions['authorizenet']; 
			else
				$authorizenet['developer']=$authorizenet['email']=$authorizenet['id'] = $authorizenet['key']= $authorizenet['desc']=$authorizenet['secret']='';	
			?>
			<p class="cbox"><input id="eshop_methodg" name="eshop_method[]" type="checkbox" value="authorize.net"<?php if(in_array('authorize.net',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_methodg" class="eshopmethod"><?php _e('Accept payment by Authorize.net','eshop'); ?></label></p>
			<label for="eshop_authorizenetemail"><?php _e('Email address','eshop'); ?></label><input id="eshop_authorizenetemail" name="authorizenet[email]" type="text" value="<?php echo $authorizenet['email']; ?>" size="30" maxlength="50" /><br />
			<label for="eshop_authorizenetid"><?php _e('API Login ID','eshop'); ?></label><input id="eshop_authorizenetid" name="authorizenet[id]" type="text" value="<?php echo $authorizenet['id']; ?>" size="20" /><br />
			<label for="eshop_authorizenetkey"><?php _e('Transaction Key','eshop'); ?></label><input id="eshop_authorizenetkey" name="authorizenet[key]" type="text" value="<?php echo $authorizenet['key']; ?>" size="40" /><br />
			<label for="eshop_authorizenetsecret"><?php _e('MD5-Hash Phrase(was Secret Answer)','eshop'); ?></label><input id="eshop_authorizenetsecret" name="authorizenet[secret]" type="text" value="<?php echo $authorizenet['secret']; ?>" size="40" /><br />
			<label for="eshop_authorizenetdesc"><?php _e('Cart description','eshop'); ?></label><input id="eshop_authorizenetdesc" name="authorizenet[desc]" type="text" value="<?php echo $authorizenet['desc']; ?>" size="40" /><br />
			<p class="cbox"><input id="eshop_authorizenetdev" name="authorizenet[developer]" type="checkbox" value="1"<?php if(isset($authorizenet['developer']) && $authorizenet['developer']=='1') echo ' checked="checked"'; ?> /><label for="eshop_authorizenetdev"><?php _e('Use Developer test account when in test mode.','eshop'); ?></label></p>
		</fieldset>
		<?php
	}
	function ogone_box($eshopoptions) {
		global $wpdb;
		?>
		<fieldset>
			<?php 
			if(isset($eshopoptions['ogone']))
				$ogone = $eshopoptions['ogone'];
			else
				$ogone['email']=$ogone['COM'] = $ogone['PSPID']= $ogone['secret']='';
			
			$this->show_img('ogone');
			?>
			<p class="cbox"><input id="eshop_methodh" name="eshop_method[]" type="checkbox" value="ogone"<?php if(in_array('ogone',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_methodh" class="eshopmethod"><?php _e('Accept payment by ogone','eshop'); ?></label></p>
			<p><em><?php _e('All fields are required.','eshop'); ?></em></p>
			<div class="ogonehi">
			<h3><?php _e('The following information must be entered at ogone','eshop'); ?></h3>
			<p><?php _e('The following 2 links need to be entered into the Technical information > transaction feedback page.','eshop'); ?></p>
			<p><strong><?php _e('HTTP redirection in the browser/URL of the merchant\'s post-payment page:','eshop'); ?></strong></p>
			<p><?php _e('If the payment\'s status is "accepted", "on hold" or "uncertain".','eshop'); ?><br />
			<code><?php echo add_query_arg('eshopaction','ogoneipn',get_permalink($eshopoptions['cart_success'])); ?></code></p>
			<p><?php _e('If the payment\'s status is "cancelled by the client" or "too many rejections by the acquirer".','eshop'); ?><br />
			<code><?php echo add_query_arg('eshopaction','cancel',get_permalink($eshopoptions['cart_cancel'])); ?></code></p>
			<p><?php _e('The following link needs to be entered into the Technical information > Data and origin verification page.','eshop'); ?></p>
			<p><strong><?php _e('Checks for e-Commerce:','eshop'); ?></strong></p>
			<p><?php _e('URL of the merchant page containing the payment form that will call the page:orderstandard.asp','eshop'); ?><br />
			<code><?php echo add_query_arg('eshopaction','redirect',get_permalink($eshopoptions['checkout'])); ?></code></p>
			</div>
			<label for="eshop_ogonepspid"><?php _e('PSPID','eshop'); ?></label><input id="eshop_ogonepspid" name="ogone[PSPID]" type="text" value="<?php echo $ogone['PSPID']; ?>" size="20" /><br />
			<label for="eshop_ogonesecret"><?php _e('SHA Passphrases - set SHA-OUT &amp; SHA-IN to the same value.','eshop'); ?></label><input id="eshop_ogonesecret" name="ogone[secret]" type="text" value="<?php echo $ogone['secret']; ?>" size="40" /><br />
			<label for="eshop_ogonedesc"><?php _e('Cart description','eshop'); ?></label><input id="eshop_ogonedesc" name="ogone[COM]" type="text" value="<?php echo $ogone['COM']; ?>" size="40" /><br />
			<label for="eshop_ogoneemail"><?php _e('Email address','eshop'); ?></label><input id="eshop_ogoneemail" name="ogone[email]" type="text" value="<?php echo $ogone['email']; ?>" size="30" maxlength="50" /><br />

		</fieldset>
	<?php

	}
	function show_img($eshoppayment){
		global $eshopoptions;
		$replace = array(".");
		$eshopfiles=eshop_files_directory();
		$eshoppayment_text=$eshoppayment;
		$eshoppayment = str_replace($replace, "", $eshoppayment);
		if($eshoppayment_text=='cash' && isset($eshopoptions['cash'])){
			$eshopcash = $eshopoptions['cash'];
			if($eshopcash['rename']!='')
				$eshoppayment_text=$eshopcash['rename'];
		}
		if($eshoppayment_text=='bank'  && isset($eshopoptions['bank'])){
			$eshopbank = $eshopoptions['bank'];
			if($eshopbank['rename']!='')
				$eshoppayment_text=$eshopbank['rename'];
		}
		$eshopmi=apply_filters('eshop_merchant_img_'.$eshoppayment,array('path'=>$eshopfiles['0'].$eshoppayment.'.png','url'=>$eshopfiles['1'].$eshoppayment.'.png'));
		$eshopmerchantimgpath=$eshopmi['path'];
		$eshopmerchantimgurl=$eshopmi['url'];
		$dims[3]='';
		if(file_exists($eshopmerchantimgpath))
			$dims=getimagesize($eshopmerchantimgpath);
		echo '<p class="eshopgateway"><img src="'.$eshopmerchantimgurl.'" '.$dims[3].' alt="'.$eshoppayment_text.'" title="'.$eshoppayment_text.'" /></p>'."\n";
	}
	function on_save_changes() {
		global $wpdb;
		//user permission check
		if ( !current_user_can('eShop_admin') )
			wp_die( __('Cheatin&#8217; uh?') );	

		//cross check the given referer
		check_admin_referer('eshop-metaboxes-general');
		$err='100';
		if(!isset($_POST['eshop_method'])) $_POST['eshop_method']='';
		//process here your on $_POST validation and / or option saving
		$eshopoptions = get_option('eshop_plugin_settings');
		$eshopoptions['method']=$wpdb->escape($_POST['eshop_method']);

		//these are all for paypal
		$eshopoptions['currency']=$wpdb->escape($_POST['eshop_currency']);
		$eshopoptions['location']=$wpdb->escape($_POST['eshop_location']);
		$eshopoptions['business']=$wpdb->escape(trim($_POST['eshop_business']));
		$eshopoptions['business_sec']=$wpdb->escape(trim($_POST['eshop_business_sec']));
		$eshopoptions['paypal_noemail']=$wpdb->escape($_POST['eshop_paypal_noemail']);

		//payson
		$paysonpost['email']=$wpdb->escape($_POST['payson']['email']);
		$paysonpost['id']=$wpdb->escape($_POST['payson']['id']);
		$paysonpost['key']=$wpdb->escape($_POST['payson']['key']);
		$paysonpost['description']=$wpdb->escape($_POST['payson']['description']);
		$paysonpost['minimum']=$wpdb->escape($_POST['payson']['minimum']);
		$eshopoptions['payson']=$paysonpost;

		//ideallite
		$ideallitepost['IDEAL_AQUIRER']=$wpdb->escape($_POST['ideallite']['IDEAL_AQUIRER']);
		$ideallitepost['IDEAL_HASH_KEY']=$wpdb->escape($_POST['ideallite']['IDEAL_HASH_KEY']);
		$ideallitepost['IDEAL_MERCHANT_ID']=$wpdb->escape($_POST['ideallite']['IDEAL_MERCHANT_ID']);
		$ideallitepost['IDEAL_SUB_ID']=$wpdb->escape($_POST['ideallite']['IDEAL_SUB_ID']);
		$ideallitepost['IDEAL_TEST_MODE']=$wpdb->escape($_POST['ideallite']['IDEAL_TEST_MODE']);
		$ideallitepost['idealownermail']=$wpdb->escape($_POST['ideallite']['idealownermail']);
		$ideallitepost['idealdescription']=$wpdb->escape($_POST['ideallite']['idealdescription']);
		$eshopoptions['ideallite']=$ideallitepost;

		//authorize.net
		if(isset($_POST['authorizenet']['developer']))
			$authorizenetpost['developer']='1';
		else
			$authorizenetpost['developer']='0';
		$authorizenetpost['email']=$wpdb->escape($_POST['authorizenet']['email']);
		$authorizenetpost['id']=$wpdb->escape($_POST['authorizenet']['id']);
		$authorizenetpost['key']=$wpdb->escape($_POST['authorizenet']['key']);
		$authorizenetpost['secret']=$wpdb->escape($_POST['authorizenet']['secret']);
		$authorizenetpost['desc']=$wpdb->escape($_POST['authorizenet']['desc']);
		$eshopoptions['authorizenet']=$authorizenetpost;

		//epn
		$epnpost['email']=$wpdb->escape($_POST['epn']['email']);
		$epnpost['id']=$wpdb->escape($_POST['epn']['id']);
		$epnpost['description']=$wpdb->escape($_POST['epn']['description']);
		$eshopoptions['epn']=$epnpost;

		//cash
		$cashpost['email']=$wpdb->escape($_POST['cash']['email']);
		$cashpost['rename']=$wpdb->escape($_POST['cash']['rename']);
		$eshopoptions['cash']=$cashpost;

		//bank
		$bankpost['email']=$wpdb->escape($_POST['bank']['email']);
		$bankpost['rename']=$wpdb->escape($_POST['bank']['rename']);
		$eshopoptions['bank']=$bankpost;

		//webtopay
		$webtopaypost['id']=$wpdb->escape($_POST['webtopay']['id']);
		$webtopaypost['lang']=$wpdb->escape($_POST['webtopay']['lang']);
		$webtopaypost['signature']=$wpdb->escape($_POST['webtopay']['signature']);
		$webtopaypost['projectid']=$wpdb->escape($_POST['webtopay']['projectid']);
		$webtopaypost['email']=$wpdb->escape($_POST['webtopay']['email']);

		$eshopoptions['webtopay']=$webtopaypost;
		if(!is_array($eshopoptions['method'])){
			$eshopoptions['status']=$wpdb->escape('testing');
			$err='1';
		}

		//ogone
		$ogonepost['PSPID']=$wpdb->escape($_POST['ogone']['PSPID']);
		$ogonepost['COM']=$wpdb->escape($_POST['ogone']['COM']);
		$ogonepost['secret']=$wpdb->escape($_POST['ogone']['secret']);
		$ogonepost['email']=$wpdb->escape($_POST['ogone']['email']);
		$eshopoptions['ogone']=$ogonepost;
		
		$eshopoptions=apply_filters('eshop_setting_merchant_save',$eshopoptions,$_POST);
		if(isset($eshopoptions['err'])){
			$err=$eshopoptions['err'];
			unset ($eshopoptions['err']);
		}
		
		update_option('eshop_plugin_settings',$eshopoptions);
		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		$_POST['_wp_http_referer']=add_query_arg('eshop_message',$err,$_POST['_wp_http_referer']);
		wp_redirect($_POST['_wp_http_referer']);	
		exit;
	}
	function eclass_errors($messages){
		$messages=array(
		'1'=>__('No Merchant Gateway selected, eShop has been put in Test Mode','eshop'),
		'100'=>__('eShop Merchant Gateways updated.','eshop')
		);
		$messages=apply_filters('eshop_setting_merchant_messages',$messages);
		return $messages;
	}
}
class eshop_setting_tax extends eshop_metabox_class {
	function on_load_page() {
		add_meta_box('eshop-tax-action', __('Enable Tax','eshop'), array(&$this, 'taxenable_box'), $this->pagehook, 'side', 'core');
		add_meta_box('eshop-tax-setup', __('eShop Tax Settings','eshop'), array(&$this, 'tax_box'), $this->pagehook, 'normal', 'core');
		add_meta_box('eshop-downloads-only', __('eShop Tax Rates','eshop'), array(&$this, 'tax_box_2'), $this->pagehook, 'normal', 'core');
		add_filter('eshop_error_messages',array(&$this, 'eclass_errors'));
	}
	function taxenable_box($eshopoptions) {
		if(!isset($eshopoptions['tax'])) $eshopoptions['tax']='0';
		$etax = $eshopoptions['tax'];
		?>
		<fieldset>
		<label for="eshop_tax"><?php _e('Enable Tax','eshop'); ?></label>
		<select name="eshop_tax" id="eshop_tax">
			<option value="1" <?php selected($etax,'1'); ?>><?php _e('Yes','eshop'); ?></option>
			<option value="0" <?php selected($etax,'0'); ?>><?php _e('No','eshop'); ?></option>
		</select>
		</fieldset>
		<?php
	}
	function tax_box($eshopoptions) {
		global $wpdb;
		if(!isset($eshopoptions['etax']))$eshopoptions['etax']=array();
		$etax = $eshopoptions['etax'];
		if(!isset($etax['bands'])) $etax['bands']='0';
		if(!isset($etax['unknown'])) $etax['unknown']='';
		if(!isset($etax['zonal'])) $etax['zonal']='0';
		if(!isset($etax['shipping'])) $etax['shipping']='';
		?>
		<fieldset>
			<label for="eshop_tax_zonal"><?php _e('Tax by (Shipping) Zones','eshop'); ?></label>
			<select name="eshop_tax_zonal" id="eshop_tax_zonal">
				<option value="1" <?php selected($etax['zonal'],'1'); ?>><?php _e('Yes','eshop'); ?></option>
				<option value="0" <?php selected($etax['zonal'],'0'); ?>><?php _e('No','eshop'); ?></option>
			</select><br />
			<label for="eshop_tax_bands"><?php _e('Number of Tax bands','eshop'); ?></label><input type="text" id="eshop_tax_bands" name="eshop_tax_bands" value="<?php echo $etax['bands']; ?>" size="4" />
			<?php if( $etax['bands'] > 0 ) : ?>
			<label for="eshop_tax_shipping"><?php _e('Tax shipping?','eshop'); ?></label>
			<select name="eshop_tax_shipping" id="eshop_tax_shipping">
				<option value="" <?php selected($etax['shipping'],''); ?>><?php _e('No, or select tax band','eshop'); ?></option>
				<?php
				for($x=1;$x <= $etax['bands']; $x++){
					$tzone=sprintf(__('Band %1$d','eshop'),$x);
					$disptzone=apply_filters('eshop_rename_tax_zone',array());
					if(isset($disptzone[$x]))
						$tzone=$disptzone[$x];
				?>
				<option value="<?php echo $x; ?>" <?php selected($etax['shipping'],$x); ?>><?php echo $tzone; ?></option>
				<?php
				}
				?>
			</select><br />
			<label for="eshop_tax_unknown"><?php _e('Default tax % for downloads only stores','eshop'); ?></label><input type="text" id="eshop_tax_unknown" name="eshop_tax_unknown" value="<?php echo $etax['unknown']; ?>" size="4" />

			<?php endif; ?>
		</fieldset>
	<?php
	}
	function tax_box_2($eshopoptions) {
		global $wpdb;
		if(!isset($eshopoptions['etax'])) $eshopoptions['etax']=array();
		$etax = $eshopoptions['etax'];
		if(!isset($etax['bands']) || $etax['bands']=='' ) $etax['bands']='0';
		$dtable=$wpdb->prefix.'eshop_rates';
		if($etax['bands']!='0'){
			?>
			<fieldset>
			<?php
			$eshopletter = "A";
			for($i=1;$i<=2;$i++){
				if($i==1){
					$area='state';
					echo '<h4>'.__('Local Tax rates (for your Country).','eshop').'</h4>';
				}else{
					$area='country';
					echo '<h4>'.__('Tax rates for Other Countries.','eshop').'</h4>';
				}
				$query=$wpdb->get_results("SELECT * from $dtable where rate_type='tax' && area='$area' ORDER BY class ASC");
				$k=0;
				?>
				<table class="hidealllabels widefat">
					<thead>
					<tr>
						<th id="<?php echo $eshopletter; ?>tax"><?php _e('Tax Band','eshop'); ?></th>
					<?php
					if($etax['zonal']=='1'){
						for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
							$echozone=sprintf(__('Zone %1$d','eshop'),$z);
							$dispzone=apply_filters('eshop_rename_ship_zone',array());
							if(isset($dispzone[$z]))
								$echozone=$dispzone[$z];
						?>
							<th id="<?php echo $eshopletter.'zone'. $z; ?>" class="zone<?php echo $z; ?>"><?php echo $echozone; ?></th>
						<?php
						}
					}else{
						$z=1;
					?>
						<th id="<?php echo $eshopletter.'zone'. $z; ?>" class="zone<?php echo $z; ?>"><?php _e('All Zones','eshop'); ?></th>

					<?php
					}
					?>
					</tr>
					</thead>
					<tbody>
					<?php
					$x=0;
					foreach ($query as $row){
						$x++;
						$alt = ($x % 2) ? '' : ' class="alt"';
						echo '<tr'.$alt.'>';
						$tzone=sprintf(__('Band %1$d','eshop'),$x);
						$disptzone=apply_filters('eshop_rename_tax_zone',array());
						if(isset($disptzone[$x]))
							$tzone=$disptzone[$x];
						echo '<td id="'.$eshopletter.'cname'.$x.'" headers="'.$eshopletter.'tax">'.$tzone.'</td>'."\n";
						if($etax['zonal']=='1'){
							for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
								$y='zone'.$z;
								$echozone=sprintf(__('Zone %1$d','eshop'),$z);
								$dispzone=apply_filters('eshop_rename_ship_zone',array());
								if(isset($dispzone[$z]))
									$echozone=$dispzone[$z];
								echo '<td headers="'.$eshopletter.'zone'.$z.' '.$eshopletter.'cname'.$x.'" class="zone'.$z.'"><label for="'.$eshopletter.'zone'.$z.$x.'">'.$echozone.'</label><input id="'.$eshopletter.'zone'.$z.$x.'" name="'.$area.'['.$x.']['.$y.']" type="text" value="'.$row->$y.'" size="6" maxlength="16" /></td>'."\n";
							}
						}else{
							$z=1;
							$y='zone'.$z;
							echo '<td headers="'.$eshopletter.'zone'.$z.' '.$eshopletter.'cname'.$x.'" class="zone'.$z.'"><label for="'.$eshopletter.'zone'.$z.$x.'">'.__('All Zones','eshop').'</label><input id="'.$eshopletter.'zone'.$z.$x.'" name="'.$area.'['.$x.']" type="text" value="'.$row->$y.'" size="6" maxlength="16" /></td>'."\n";

						}
						echo '</tr>';
						if($x >= $etax['bands']) 
							break;
					}
					
					//add on spare zones
					if($x != $etax['bands']) {
						$z=1;
						$x++;
						while ($x < $etax['bands']+1){
							$alt = ($x % 2) ? '' : ' class="alt"';
							echo '<tr'.$alt.'>';
							$tzone=sprintf(__('Band %1$d','eshop'),$x);
							$disptzone=apply_filters('eshop_rename_tax_zone',array());
							if(isset($disptzone[$x]))
								$tzone=$disptzone[$x];
							echo '<td id="'.$eshopletter.'cname'.$x.'" headers="'.$eshopletter.'tax">'.$tzone.'</td>'."\n";
							if($etax['zonal']=='1'){
								for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
									$y='zone'.$z;
									$echozone=sprintf(__('Zone %1$d','eshop'),$z);
									$dispzone=apply_filters('eshop_rename_ship_zone',array());
									if(isset($dispzone[$z]))
										$echozone=$dispzone[$z];
									if(isset($row->$y)) 
										$val=$row->$y;
									else 
										$val='0.00';
									echo '<td headers="'.$eshopletter.'zone'.$z.' '.$eshopletter.'cname'.$x.'" class="zone'.$z.'"><label for="'.$eshopletter.'zone'.$z.$x.'">'.$echozone.'</label><input id="'.$eshopletter.'zone'.$z.$x.'" name="'.$area.'['.$x.']['.$y.']" type="text" value="'.$val.'" size="6" maxlength="16" /></td>'."\n";
								}
							}else{
								$y='zone'.$z;
								echo '<td headers="'.$eshopletter.'zone'.$z.' '.$eshopletter.'cname'.$x.'" class="zone'.$z.'"><label for="'.$eshopletter.'zone'.$z.$x.'">'.__('All Zones','eshop').'</label><input id="'.$eshopletter.'zone'.$z.$x.'" name="'.$area.'['.$x.']" type="text" value="" size="6" maxlength="16" /></td>'."\n";
								$z++;
							}
							echo '</tr>';
							$x++;
						}
					}
					
					$eshopletter++;
					
					?>
					</tbody>
					</table>
				<?php
			}
			?>
			<input type="hidden" value="1" name="eshoptaxables" />
			</fieldset>
		<?php
		}else{
			echo '<p>'.__('Will appear here once number of bands has been set.','eshop').'</p>';
		}
	}
	
	function on_save_changes() {
		global $wpdb;
		$dtable=$wpdb->prefix.'eshop_rates';
		$err='100';
		//user permission check
		if ( !current_user_can('eShop_admin') )
			wp_die( __('Cheatin&#8217; uh?') );			
		//cross check the given referer
		check_admin_referer('eshop-metaboxes-general');
		$eshopoptions = get_option('eshop_plugin_settings');
		//process here your on $_POST validation and / or option saving
		if(is_numeric($_POST['eshop_tax_bands']) || $_POST['eshop_tax_bands']==''){
			$eshopoptions['etax']['bands']=$wpdb->escape($_POST['eshop_tax_bands']);
		}else{
			$err=1;
			$_POST['eshop_tax_bands']=$eshopoptions['etax']['bands']='';
		}
		if(is_numeric($_POST['eshop_tax_unknown']) || $_POST['eshop_tax_unknown']==''){
			$eshopoptions['etax']['unknown']=$wpdb->escape($_POST['eshop_tax_unknown']);
		}else{
			$err=2;
			$_POST['eshop_tax_unknown']=$eshopoptions['etax']['unknown']='';
		}
		if(isset($eshopoptions['etax']['zonal']))
			$eshopiszonal=$eshopoptions['etax']['zonal'];
		else
			$eshopiszonal='0';
		$eshopisbands=$eshopoptions['etax']['bands'];
		$eshopoptions['tax']=$wpdb->escape($_POST['eshop_tax']);
		$eshopoptions['etax']['zonal']=$wpdb->escape($_POST['eshop_tax_zonal']);
		$eshopoptions['etax']['bands']=$wpdb->escape($_POST['eshop_tax_bands']);
		$eshopoptions['etax']['unknown']=$wpdb->escape($_POST['eshop_tax_unknown']);

		if( isset($_POST['eshop_tax_shipping']))
			$eshopoptions['etax']['shipping']=$wpdb->escape($_POST['eshop_tax_shipping']);
	
		update_option('eshop_plugin_settings',$eshopoptions);
		$etax=$eshopoptions['etax'];
		//update the tax rates - unless we are changing settings
		if($eshopisbands=='0'){
			unset($_POST['eshoptaxables']);
			$wpdb->query("DELETE from $dtable where rate_type='tax'");
		}
		if(isset($_POST['eshoptaxables']) && $eshopiszonal==$eshopoptions['etax']['zonal']){
			$darray=array('state','country');
			$barray=array();
			if($etax['zonal']=='1'){
				for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
					$barray[]='zone'.$z;
				}
			}else{
				$barray[]='zone1';
			}
			$build="INSERT INTO $dtable (`".implode("`, `",$barray)."`,`class`,`rate_type`, `area`) VALUES";
			foreach($darray as $area){
				
				foreach($_POST[$area] as $k=>$v){
					if($etax['zonal']=='1'){
						$bvarray=array();
						foreach($v as $f=>$value){
							$bvarray[]=$value;
						}
						$build.="('".implode("', '",$bvarray)."','".$k."','tax','$area'),";
					}else{
						$build.="('".$v."','".$k."','tax','$area'),";
					}
				}

			}
			$queri=trim($build,',');
			$wpdb->query("DELETE from $dtable where rate_type='tax'");
			$wpdb->query($queri);
		}
		
		//lets redirect the post request into get request
		$_POST['_wp_http_referer']=add_query_arg('eshop_message',$err,$_POST['_wp_http_referer']);
		wp_redirect($_POST['_wp_http_referer']);
		exit;
	}
	function eclass_errors($messages){
		$messages=array(
		'1'=>__('Number of tax bands should be numeric.','eshop'),
		'2' => __('Unknown tax rate should be numeric','eshop'),
		'100'=>__('eShop Sales Tax settings updated.','eshop')
		);
		return $messages;
	}
}
?>