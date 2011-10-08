<?php
//class that reperesent the complete plugin - only in use for settings page.
class eshop_metabox_class {
	//constructor of class, PHP4 compatible construction for backward compatibility
	function eshop_metabox_class() {
		//add filter for WordPress 2.8 changed backend box system !
		add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
		//register callback for admin menu  setup
		add_action('admin_menu', array(&$this, 'on_admin_menu')); 
		//register the callback been used if options of page been submitted and needs to be processed
		add_action('admin_post_save_eshop_metaboxes_general', array(&$this, 'on_save_changes'));
	}
	
	//for WordPress 2.8 we have to tell, that we support 2 columns !
	function on_screen_layout_columns($columns, $screen) {
		if ($screen == $this->pagehook) {
			$columns[$this->pagehook] = 2;
		}
		return $columns;
	}
	
	//extend the admin menu
	function on_admin_menu() {
		//add our own option page, you can also add it to different sections or use your own one
		$this->pagehook = add_options_page(__('eShop Settings','eshop'), __('eShop','eshop'), 'eShop_admin', ESHOP_SETTINGS_PAGE_NAME, array(&$this, 'on_show_page'));
		//register  callback gets call prior your own page gets rendered
		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page_scripts'));
		$help='
		<p><strong>' . __('eShop help:') . '</strong></p>
		<ul>
		<li>'.__('<a href="http://quirm.net/wiki/eshop/">eShop Wiki</a>','eshop').'</li>
		<li>'.__('<a href="http://wordpress.org/tags/eshop">Wordpress forums</a>','eshop').'</li>
		<li>'.__('<a href="http://quirm.net/forum/forum.php?id=14">Quirm.net</a>','eshop').'</li>
		</ul>';
		if($this->pagehook != '')
			add_contextual_help($this->pagehook,$help); 

	}
	function on_load_page_scripts() {
		//ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
	}
	//will be executed if wordpress core detects this page has to be rendered
	function on_load_page() {
		//alowing this to be extended - examples:
		//add several metaboxes now, all metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
	//	add_meta_box('howto-metaboxes-sidebox-1', 'Sidebox 1 Title', array(&$this, 'on_sidebox_1_content'), $this->pagehook, 'side', 'core');
		add_meta_box('howto-metaboxes-sidebox-2', 'Sidebox 2 Title', array(&$this, 'on_sidebox_2_content'), $this->pagehook, 'side', 'core');
		add_meta_box('eshop-admin', __('eShop Admin','eshop'), array(&$this, 'on_contentbox_1_content'), $this->pagehook, 'normal', 'core');
	//	add_meta_box('howto-metaboxes-contentbox-2', 'Contentbox 2 Title', array(&$this, 'on_contentbox_2_content'), $this->pagehook, 'normal', 'core');
	//	add_meta_box('howto-metaboxes-contentbox-additional-1', 'Contentbox Additional 1 Title', array(&$this, 'on_contentbox_additional_1_content'), $this->pagehook, 'additional', 'core');
	//	add_meta_box('howto-metaboxes-contentbox-additional-2', 'Contentbox Additional 2 Title', array(&$this, 'on_contentbox_additional_2_content'), $this->pagehook, 'additional', 'core');
	}
	
	//executed to show the plugins complete admin page
	function on_show_page() {
		//we need the global screen column value to beable to have a sidebar in WordPress 2.8
		global $screen_layout_columns;
		$eshopoptions = get_option('eshop_plugin_settings');

		$err='';
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
		if(isset($_GET['resetbase']) && $_GET['resetbase']=='yes'){
			$table=$wpdb->prefix.'eshop_base_products';
			$wpdb->query("TRUNCATE TABLE $table"); 
			echo '<div id="message" class="updated fade"><p>'.__('eShop Base product data has been reset.','eshop').'</p></div>'."\n";
		}elseif(isset($_POST['submit'])){
			echo'<div id="message" class="updated fade"><p>'.__('eshop Settings have been updated.','eshop').'</p></div>'."\n";
		}
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

	//executed if the post arrives initiated by pressing the submit button of form
	function on_save_changes() {
		//user permission check
		if ( !current_user_can('manage_options') )
			wp_die( __('Cheatin&#8217; uh?') );			
		//cross check the given referer
		check_admin_referer('eshop-metaboxes-general');
		
		//process here your on $_POST validation and / or option saving
		
		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		wp_redirect($_POST['_wp_http_referer']);
		exit;
	}

	//none of these are actually used, just here as examples
	
	function on_sidebox_1_content($data) {
		?>
		<ul style="list-style-type:disc;margin-left:20px;">
			<?php foreach($data as $item) { echo "<li>$item</li>"; } ?>
		</ul>
		<?php
	}
	function on_sidebox_2_content($data) {
		?>
		<p>You can also use static text or any markup to be shown inside the boxes.</p>
		<?php
	}
	function on_contentbox_1_content($eshopoptions) {
		?>
		<fieldset>assdsdddd
		<label for="eshop_status"><?php _e('eShop status','eshop'); ?></label>
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
		<label for="eshop_records"><?php _e('Orders per page','eshop'); ?></label><input id="eshop_records" name="eshop_records" type="text" value="<?php echo $eshopoptions['records']; ?>" size="5" /><br />
	</fieldset>
	<?php
	}
}
?>