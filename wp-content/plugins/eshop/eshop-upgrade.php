<?php
if ('eshop-upgrade.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');

if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
} else {
    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
}
global $wpdb;
function eshop_option_upgrade() {
	$new_options = array(
		'addtocart_image'=>'',
		'base_brand'=>'',
		'base_condition'=>'',
		'base_expiry'=>'',
		'base_payment'=>'',
		'base_ptype'=>'',
		'business'=>'',
		'cart'=>'',
		'cart_cancel'=>'',
		'cart_nostock'=>'Out of Stock',
		'cart_shipping'=>'',
		'cart_success'=>'',
		'checkout'=>'',
		'credits'=> 'no',
		'cron_email'=>'',
		'currency_symbol'=>'&pound;',
		'currency'=>'GBP',
		'discount_shipping'=>'',
		'discount_spend1'=>'',
		'discount_value1'=>'',
		'discount_spend2'=>'',
		'discount_value2'=>'',
		'discount_spend3'=>'',
		'discount_value3'=>'',
		'downloads_hideall'=>'no',
		'downloads_num'=>'3',
		'downloads_only'=> 'no',
		'first_time'=> 'yes',
		'fold_menu'=> 'no',
		'from_email'=>'',
		'hide_addinfo'=>'yes',
		'hide_cartco'=>'',
		'hide_shipping'=>'',
		'image_in_cart'=>'',
		'location'=>'GB',
		'method'=>array('paypal'),
		'options_num'=>'3',
		'paypal_noemail'=>'',
		'records'=>'10',
		'search_img'=> 'no',
		'set_cacheability'=>'no',
		'shipping_state'=> 'GB',
		'shipping_zone'=> 'country',
		'shipping'=> '1',
		'shop_page'=>'',
		'show_allstates'=>'',
		'show_downloads'=>'',
		'show_forms'=>'',
		'show_zones'=>'no',
		'status'=> 'testing',
		'stock_control'=>'no',
		'style' => 'yes',
		'sysemails' =>'',
		'tandc'=>'',
		'tandc_id'=>'',
		'tandc_use'=>'',
		'unknown_state'=> '5',
		'version'=>'',
		'xtra_help' =>'',
		'xtra_privacy' =>'',
	);

	// if old options exist, update to new system only need pre 5.0
	foreach( $new_options as $key => $value ) {
		$existing='';
		$existing = get_option( 'eshop_' . $key );
		$newer_options[$key] = $existing;
		delete_option( 'eshop_' . $key );
	}
	delete_option( 'eshop_authorizenet');
	delete_option( 'eshop_cash');
	delete_option( 'eshop_epn');
	delete_option( 'eshop_ideallite');
	delete_option( 'eshop_payson');
	delete_option( 'eshop_show_sku');
	delete_option( 'eshop_show_stock');
	delete_option( 'eshop_webtopay');
	update_option( 'eshop_plugin_settings', $newer_options );
}
function eshop_postmeta_upgrade() {
	global $eshopoptions;
	$new_options = array(
	'_Sku'=>'sku',
	'_Product Description'=>'description',
	'_Shipping Rate'=>'shiprate',
	'_Featured Product'=>'featured',
	'_Stock Quantity'=>'qty',
	'_eshoposets'=>'optset');	
	//add on options and prices into the mix
	$numoptions=$eshopoptions['options_num'];
	if(!is_numeric($numoptions)) $numoptions='3';
	for($i=1;$i<=$numoptions;$i++){
		$new_options['_Option '.$i]=array($i=>'option');
		$new_options['_Price '.$i]=array($i=>'price');
		$new_options['_Download '.$i]=array($i=>'download');
	}
	//go through every page and post
	$args = array(
	'post_type' => 'any',
	'numberposts' => -1,
	); 
	//add in transfer from prod download to _download here
	$allposts = get_posts($args);
	foreach( $allposts as $postinfo) {
		//if(get_post_meta($postinfo->ID, '_eshop_product')!='')
		//	break;
		foreach($new_options as $oldfield=>$newfield){
			$eshopvalue=get_post_meta($postinfo->ID, $oldfield,true);
			if(is_array($newfield)){
				foreach($newfield as $k=>$v){
					$thenew_options['products'][$k][$v]=$eshopvalue;
				}
			}else{
				$thenew_options[$newfield]=$eshopvalue;
			}
			if($oldfield=='_Featured Product' && $eshopvalue=='Yes'){
				add_post_meta( $postinfo->ID, '_eshop_featured', 'Yes');
			}
		}
		if($thenew_options['sku']!='' && $thenew_options['description']!='' && $thenew_options['products']['1']['option']!='' && $thenew_options['products']['1']['price']!=''){
			add_post_meta( $postinfo->ID, '_eshop_product', $thenew_options);
		}
		$stock=get_post_meta($postinfo->ID, '_Stock Available',true);
		if(trim($stock)=='Yes'){
			add_post_meta( $postinfo->ID, '_eshop_stock', '1');
		}
	}
	//just make sure they are all gone
	foreach($new_options as $oldfield=>$newfield){
		delete_post_meta_by_key($oldfield);
	}
	delete_post_meta_by_key('_Stock Available');
	delete_post_meta_by_key('_eshop_prod_img');

	/* post meta end */
}
//update post meta if stock control is on only
function eshop_updatestockcontrol(){
	global $wpdb,$eshopoptions;
	$dib='_eshop_product';
	$querystr = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '$dib'" );
 	$metaResults = $wpdb->get_results($querystr);
	$stocktable=$wpdb->prefix ."eshop_stock";
	 if ($metaResults){
		//Loop through each result post to display appropriate contents
		foreach ($metaResults as $post){
			$eprod=get_post_meta($post->post_id, '_eshop_product', true);
			$stktableqty=$wpdb->get_row("SELECT available,purchases FROM $stocktable where post_id=$post->post_id");
			$available=$stktableqty->available;
			$purc=$stktableqty->purchases;
			if(isset($available) && is_numeric($available)) $newqty=$available;	
			else $newqty=0;
			unset($eprod['qty']);
			$numoptions=$eshopoptions['options_num'];
			if(!is_numeric($numoptions)) $numoptions='3';
			//update the first, add the rest
			$sql = "DELETE FROM $stocktable WHERE post_id = $post->post_id limit 1";
			$wpdb->query($wpdb->prepare($sql));
			$eprod['products'][1]['stkqty']=$newqty;
			for($i=1;$i<=$numoptions;$i++){
				$sql = "INSERT INTO $stocktable (post_id,option_id,available,purchases) VALUES ($post->post_id,$i,$newqty,$purc)";
				$wpdb->query($wpdb->prepare($sql));
				$eprod['products'][$i]['stkqty']=$newqty;
			}
			update_post_meta( $post->post_id, '_eshop_product', $eprod);		
		}
	}
}
$eshopoptions = get_option('eshop_plugin_settings');
if(get_option('eshop_version')!='')
	$eshopoptions['version']=get_option('eshop_version');



if(version_compare($eshopoptions['version'], '3.0.0' ,'<')){
	// lumping all changes prior to 3.0.0
	/* db changes */
	$table = $wpdb->prefix ."eshop_base_products";
	$tablefields = $wpdb->get_results("DESCRIBE {$table};");
	foreach($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='condition') {
			$sql="ALTER TABLE ".$table." CHANGE `condition` thecondition VARCHAR(255) NOT NULL default ''";
			$wpdb->query($sql);
		}
	}
	/* db changes */
	$table = $wpdb->prefix . "eshop_orders";
	$tablefields = $wpdb->get_results("DESCRIBE {$table};");
	foreach($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='memo') {
			$sql="ALTER TABLE ".$table." CHANGE `memo` thememo TEXT NOT NULL";
			$wpdb->query($sql);
		}
	}

	/* db changes 2.10.1 */

	$table = $wpdb->prefix . "eshop_orders";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='admin_note') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` ADD `admin_note` TEXT NOT NULL";
		$wpdb->query($sql);
	}

	/* db change 2.11.7 (2.12 release) */

	$table = $wpdb->prefix . "eshop_order_items";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	foreach ($tablefields as $tablefield) {
		 $add_field[]= $tablefield->Field;
	}
	if(!in_array('down_id',$add_field)) {
		$sql="ALTER TABLE `".$table."` ADD `down_id` int(11) NOT NULL default '0'";
		$wpdb->query($sql);
	}
	/* db changes 2.13.0 */
	/* state table recreated + */
	$table = $wpdb->prefix . "eshop_countries";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	foreach ($tablefields as $tablefield) {
		 $add_field[]= $tablefield->Field;
	}
	if(!in_array('list',$add_field)) {
		$sql="ALTER TABLE `".$table."` ADD `list` tinyint(1) NOT NULL default '1'";
		$wpdb->query($sql);
	}
	/*db change 3.0.0*/
	$table = $wpdb->prefix . "eshop_orders";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	foreach ($tablefields as $tablefield) {
		 $add_field[]= $tablefield->Field;
	}
	if(!in_array('paidvia',$add_field)) {
		$sql="ALTER TABLE `".$table."` ADD `paidvia` VARCHAR(255) NOT NULL default 'paypal'";
		$wpdb->query($sql);
	}

	$table = $wpdb->prefix . "eshop_orders";
	$sql="ALTER TABLE `".$table."` CHANGE `state` `state` VARCHAR(100) NOT NULL default ''";
	$sql="ALTER TABLE `".$table."` CHANGE `ship_state` `ship_state` VARCHAR(100) NOT NULL default ''";
	$wpdb->query($sql);
}
if(version_compare($eshopoptions['version'], '3.1.0' ,'<')){
	$table = $wpdb->prefix . "eshop_orders";
	$wpdb->query("ALTER TABLE ".$table." CHANGE `status` `status` SET( 'Sent', 'Completed', 'Pending', 'Failed', 'Deleted', 'Waiting' ) DEFAULT 'Pending' NOT NULL");
}

if(version_compare($eshopoptions['version'], '3.5.0' ,'<')){
	$table = $wpdb->prefix . "eshop_order_items";
	$wpdb->query("ALTER TABLE ".$table." CHANGE `item_id` `item_id` VARCHAR( 255 ) NOT NULL DEFAULT''");
}

if(version_compare($eshopoptions['version'], '3.9.0' ,'<')){
	$table = $wpdb->prefix . "eshop_order_items";
		$tablefields = $wpdb->get_results("DESCRIBE {$table}");
		$add_field = TRUE;
		foreach ($tablefields as $tablefield) {
			if(strtolower($tablefield->Field)=='optsets') {
				$add_field = FALSE;
			}
		}
		if ($add_field) {
			$sql="ALTER TABLE `".$table."` ADD `optsets` TEXT NOT NULL";
			$wpdb->query($sql);
	}
}
if(version_compare($eshopoptions['version'], '4.0.0' ,'<')){
	$table = $wpdb->prefix . "eshop_option_sets";
	if ($wpdb->get_var("show tables like '$table'") == $table) {
		$sql = "CREATE TABLE ".$table." (
		id int(11) NOT NULL auto_increment,
		optid int(11) NOT NULL default '0',
		name varchar(255) NOT NULL default '',
		price float(16,2) NOT NULL default '0.00',
		weight float(8,2) NOT NULL default '0.00',
		  PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta($sql);
	}
	$table = $wpdb->prefix . "eshop_option_names";
	if ($wpdb->get_var("show tables like '$table'") == $table) {
		$sql = "CREATE TABLE ".$table." (
		optid int(11) NOT NULL auto_increment,
		name varchar(255) NOT NULL default '',
		type tinyint(1) NOT NULL default '0',
		`description` TEXT NOT NULL  default '',
		  PRIMARY KEY  (optid)
		) $charset_collate;";

		dbDelta($sql);
	}
}

if(version_compare($eshopoptions['version'], '4.1.9' ,'<')){
	$table = $wpdb->prefix . "eshop_discount_codes";
	$wpdb->query("ALTER TABLE ".$table." CHANGE `percent` `percent` float(4,2) NOT NULL DEFAULT '0'");
}


if(version_compare($eshopoptions['version'], '4.2.5' ,'<')){
	$table = $wpdb->prefix . "eshop_option_names";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='description') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` ADD `description` TEXT NOT NULL";
		$wpdb->query($sql);
	}
}

if(version_compare($eshopoptions['version'], '5.0.0' ,'<')){
	$table = $wpdb->prefix . "eshop_option_names";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='description') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
		$wpdb->query($sql);
	}
	
	
	$dirs=wp_upload_dir();
	$upload_dir=$dirs['basedir'];
	$plugin_dir=WP_PLUGIN_DIR;
	//new_files
	$eshop_goto=$upload_dir.'/eshop_files';
	$eshop_from=$plugin_dir.'/eshop/files';
	copy($eshop_from.'/noimage.png',$eshop_goto.'/noimage.png');
	chmod($eshop_goto.'/noimage.png',0666);
	copy($eshop_from.'/eshop-onload.js',$eshop_goto.'/eshop-onload.js');
	chmod($eshop_goto.'/eshop-onload.js',0666);
}
if(version_compare($eshopoptions['version'], '5.2.0' ,'<')){
	eshop_option_upgrade();
	eshop_postmeta_upgrade();
	$table = $wpdb->prefix . "eshop_orders";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='affiliate') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` ADD `affiliate` varchar(255) NOT NULL default ''";
		$wpdb->query($sql);
	}
}
$eshopoptions = get_option('eshop_plugin_settings');

if(version_compare($eshopoptions['version'], '5.3.0' ,'<')){
	$table = $wpdb->prefix . "eshop_order_items";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='weight') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` ADD `weight` float(16,2) NOT NULL default '0.00'";
		$wpdb->query($sql);
	}
	$table = $wpdb->prefix . "eshop_shipping_rates";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='weight') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` ADD `weight` float(16,2) NOT NULL default '0.00', ADD ship_type int(11) NOT NULL default '0'";
		$wpdb->query($sql);
	}
}
if(version_compare($eshopoptions['version'], '5.3.1' ,'<')){
	$table = $wpdb->prefix . "eshop_orders";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='user_id') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` ADD `user_id` int(11) NOT NULL , ADD `user_notes` TEXT NOT NULL ";
		$wpdb->query($sql);
	}
}

if(version_compare($eshopoptions['version'], '5.5.8' ,'<')){
	$table = $wpdb->prefix . "eshop_discount_codes";
	$wpdb->query("ALTER TABLE ".$table." CHANGE `percent` `percent` float(5,2) NOT NULL DEFAULT '0'");
}
if(version_compare($eshopoptions['version'], '5.5.9' ,'<')){
	if ( isset($eshopoptions['stock_control']) && $eshopoptions['stock_control']=='yes'){
		eshop_updatestockcontrol();
	}
}
if(version_compare($eshopoptions['version'], '5.6.0' ,'<')){
	$table = $wpdb->prefix ."eshop_stock";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='option_id') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` ADD option_id int(11) NOT NULL default '0'";
		$wpdb->query($sql);
	}
	$table = $wpdb->prefix ."eshop_order_items";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='option_id') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` ADD option_id int(11) NOT NULL default '0'";
		$wpdb->query($sql);
	}
}

if(version_compare($eshopoptions['version'], '5.6.1' ,'<')){
	$table = $wpdb->prefix . "eshop_orders";
	$sql="ALTER TABLE $table CHANGE `comments` `comments` TEXT NULL DEFAULT '',
	CHANGE `thememo` `thememo` TEXT NULL DEFAULT '',
	CHANGE `admin_note` `admin_note` TEXT NULL DEFAULT '',
	CHANGE `user_notes` `user_notes` TEXT NULL DEFAULT ''";
	$wpdb->query($sql);
	
	$table = $wpdb->prefix . "eshop_order_items";
	$sql="ALTER TABLE $table CHANGE `optsets` `optsets` TEXT NULL DEFAULT ''";
	$wpdb->query($sql);
	
	$table = $wpdb->prefix . "eshop_base_products";
	$sql="ALTER TABLE $table CHANGE `img` `img` TEXT NULL DEFAULT '',
	CHANGE `xtra` `xtra` TEXT NULL DEFAULT ''";
	$wpdb->query($sql);
	
	$table = $wpdb->prefix . "eshop_option_names";
	$sql="ALTER TABLE $table CHANGE `description` `description` TEXT NULL DEFAULT ''";
	$wpdb->query($sql);
	
	$table = $wpdb->prefix . "eshop_emails";
	$sql="ALTER TABLE $table CHANGE `emailType` `emailType` VARCHAR( 50 ) NULL DEFAULT '',
	CHANGE `emailSubject` `emailSubject` VARCHAR( 255 ) NULL DEFAULT '',
	CHANGE `emailContent` `emailContent` TEXT NULL DEFAULT ''";
	$wpdb->query($sql);
}

if(version_compare($eshopoptions['version'], '5.7.0' ,'<')){
//can't recall when this was all added in, so doing check here
	$esubject=__('Your order from ','eshop').get_bloginfo('name');
	$table = $wpdb->prefix ."eshop_emails";
	if($wpdb->get_var("select id from ".$table." where id=3 limit 1")!='3')
		$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('3','".__('Automatic Paypal email','eshop')."','$esubject')"); 
	if($wpdb->get_var("select id from ".$table." where id=4 limit 1")!='4')
		$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('4','".__('Automatic Payson email','eshop')."','$esubject')");
	if($wpdb->get_var("select id from ".$table." where id=5 limit 1")!='5')
		$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('5','".__('Automatic Cash email','eshop')."','$esubject')"); 

	if($wpdb->get_var("select id from ".$table." where id=6 limit 1")!='6')
		$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('6','".__('Automatic ePN email','eshop')."','$esubject')"); 
	if($wpdb->get_var("select id from ".$table." where id=7 limit 1")!='7')
		$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('7','".__('Automatic webtopay email','eshop')."','$esubject')"); 

	if($wpdb->get_var("select id from ".$table." where id=8 limit 1")!='8')
		$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('8','".__('Automatic Authorize.net email','eshop')."','$esubject')"); 
	if($wpdb->get_var("select id from ".$table." where id=9 limit 1")!='9')
		$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('9','".__('Automatic iDeal Lite email','eshop')."','$esubject')"); 

	if($wpdb->get_var("select id from ".$table." where id=10 limit 1")!='10')
		$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('10','".__('Automatic ogone email','eshop')."','$esubject')"); 
	if($wpdb->get_var("select id from ".$table." where id=11 limit 1")!='11')
		$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('11','".__('Automatic Bank email','eshop')."','$esubject')"); 
}

if(version_compare($eshopoptions['version'], '5.7.6' ,'<')){
	//changes for version 5.7.5 & 6
	$eshopoptions['first_time']='yes';
	//update_option('eshop_plugin_settings',$eshopoptions);
	$table = $wpdb->prefix ."eshop_option_names";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='admin_name') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` ADD admin_name varchar(255) NOT NULL default ''";
		$wpdb->query($sql);
	}
}

if(version_compare($eshopoptions['version'], '5.7.7' ,'<')){
	$table = $wpdb->prefix . "eshop_option_names";
	if ($wpdb->get_var("show tables like '$table'") == $table) {
		$sql = "CREATE TABLE ".$table." (
		optid int(11) NOT NULL auto_increment,
		name varchar(255) NOT NULL default '',
		admin_name varchar(255) NOT NULL default '',
		type tinyint(1) NOT NULL default '0',
		`description` TEXT NOT NULL  default '',
		  PRIMARY KEY  (optid)
		) $charset_collate;";

		$wpdb->query($sql);
	}
}
if(version_compare($eshopoptions['version'], '5.8.1' ,'<')){
	$charset_collate='';
	if ( ! empty($wpdb->charset) )
		$charset_collate = "CHARACTER SET $wpdb->charset";
	if ( ! empty($wpdb->collate) )
		$charset_collate .= " COLLATE $wpdb->collate";
		
	$table = $wpdb->prefix . "eshop_states";
		$wpdb->query("ALTER TABLE $table CHANGE `code` `code` char(4) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `stateName` `stateName` varchar(30) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `list` `list` char(2) $charset_collate NOT NULL default ''");
	$table = $wpdb->prefix . "eshop_shipping_rates";
		$wpdb->query("ALTER TABLE $table CHANGE `class` `class` char(1) $charset_collate NOT NULL default ''");
	$table = $wpdb->prefix . "eshop_order_items";
		$wpdb->query("ALTER TABLE $table CHANGE `checkid` `checkid` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `item_id` `item_id` varchar(255) $charset_collate NOT NULL default '0'");
		$wpdb->query("ALTER TABLE $table CHANGE `optname` `optname` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `optsets` `optsets` TEXT $charset_collate");
	$table = $wpdb->prefix . "eshop_orders";
		$wpdb->query("ALTER TABLE $table CHANGE `checkid` `checkid` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `first_name` `first_name` varchar(50) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `last_name` `last_name` varchar(50) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `company` `company` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `email` `email` varchar(100) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `phone` `phone` varchar(30) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `address1` `address1` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `address2` `address2` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `city` `city` varchar(100) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `state` `state` varchar(100) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `zip` `zip` varchar(20) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `country` `country` varchar(3) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `reference` `reference` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `ship_name` `ship_name` varchar(100) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `ship_company` `ship_company` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `ship_phone` `ship_phone` varchar(30) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `ship_address` `ship_address` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `ship_city` `ship_city` varchar(100) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `ship_state` `ship_state` varchar(100) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `ship_postcode` `ship_postcode` varchar(20) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `ship_country` `ship_country` varchar(3) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `custom_field` `custom_field` varchar(15) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `transid` `transid` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `paidvia` `paidvia` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `affiliate` `affiliate` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `comments` `comments` TEXT $charset_collate");
		$wpdb->query("ALTER TABLE $table CHANGE `thememo` `thememo` TEXT $charset_collate");
		$wpdb->query("ALTER TABLE $table CHANGE `admin_note` `admin_note` TEXT $charset_collate");
		$wpdb->query("ALTER TABLE $table CHANGE `user_notes` `user_notes` TEXT $charset_collate");
	$table = $wpdb->prefix ."eshop_downloads";
		$wpdb->query("ALTER TABLE $table CHANGE `title` `title` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `files` `files` varchar(255) $charset_collate NOT NULL default ''");
	$table = $wpdb->prefix ."eshop_download_orders";
		$wpdb->query("ALTER TABLE $table CHANGE `checkid` `checkid` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `title` `title` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `files` `files` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `code` `code` varchar(20) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `email` `email` varchar(255) $charset_collate NOT NULL default ''");
	$table = $wpdb->prefix . "eshop_countries";
		$wpdb->query("ALTER TABLE $table CHANGE `code` `code` char(2) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `country` `country` varchar(50) $charset_collate NOT NULL default ''");
	$table = $wpdb->prefix ."eshop_base_products";
		$wpdb->query("ALTER TABLE $table CHANGE `brand` `brand` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `ptype` `ptype` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `thecondition` `thecondition` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `ean` `ean` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `isbn` `isbn` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `mpn` `mpn` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `img` `img` TEXT $charset_collate");
		$wpdb->query("ALTER TABLE $table CHANGE `xtra` `xtra` TEXT $charset_collate");
	$table = $wpdb->prefix ."eshop_discount_codes";
		$wpdb->query("ALTER TABLE $table CHANGE `disccode` `disccode` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `remain` `remain` varchar(11) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `live` `live` char(3) $charset_collate NOT NULL default 'no'");
	$table = $wpdb->prefix . "eshop_option_sets";
		$wpdb->query("ALTER TABLE $table CHANGE `name` `name` varchar(255) $charset_collate NOT NULL default ''");
	$table = $wpdb->prefix . "eshop_option_names";
		$wpdb->query("ALTER TABLE $table CHANGE `name` `name` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `admin_name` `admin_name` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `description` `description` TEXT $charset_collate");
	$table = $wpdb->prefix ."eshop_emails";
		$wpdb->query("ALTER TABLE $table CHANGE `emailType` `emailType` varchar(50) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `emailSubject` `emailSubject` varchar(255) $charset_collate NOT NULL default ''");
		$wpdb->query("ALTER TABLE $table CHANGE `emailContent` `emailContent` TEXT $charset_collate");
}

if(version_compare($eshopoptions['version'], '6.0.0' ,'<')){
	$table = $wpdb->prefix ."eshop_shipping_rates";
	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='zone6') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$sql="ALTER TABLE `".$table."` 
		ADD zone6 FLOAT( 16, 2 ) NOT NULL AFTER zone5 ,
		ADD zone7 FLOAT( 16, 2 ) NOT NULL AFTER zone6 ,
		ADD zone8 FLOAT( 16, 2 ) NOT NULL AFTER zone7 ,
		ADD zone9 FLOAT( 16, 2 ) NOT NULL AFTER zone8 ";
		$wpdb->query($sql);
	}
	if(!isset($eshopoptions['numb_shipzones'])){
		$eshopoptions['numb_shipzones']='5';
	}
}


if(version_compare($eshopoptions['version'], '6.1.0' ,'<')){
	//updating database
	$shippingzone=$eshopoptions['shipping_zone'];
	$table = $wpdb->prefix ."eshop_shipping_rates";
	$newtable=$wpdb->prefix ."eshop_rates";
	$wpdb->query("RENAME TABLE $table TO $newtable");
	$wpdb->query("ALTER TABLE $newtable ADD `maxweight` varchar(16) CHARACTER SET utf8 NOT NULL DEFAULT ''");
	$wpdb->query("ALTER TABLE $newtable ADD `area` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT ''");
 	$wpdb->query("ALTER TABLE $newtable ADD `rate_type` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT 'shipping'");
 	$wpdb->query("ALTER TABLE $newtable CHANGE `class` `class` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''");
 	$wpdb->query("UPDATE $newtable set rate_type='ship_weight', area='$shippingzone', class=ship_type where ship_type>0");
 	$wpdb->query("ALTER TABLE $newtable DROP `ship_type`");
 	// add in drop old table
 	$table=$wpdb->prefix ."eshop_order_items";
	$wpdb->query("ALTER TABLE $table ADD `tax_rate` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `item_amt`");
 	$wpdb->query("ALTER TABLE $table ADD `tax_amt` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `tax_rate`");
}
if(version_compare($eshopoptions['version'], '6.2.1' ,'<')){
	//fixes issue for those that weren't using wp_ as the prefix, adjusted above also.
 	$table=$wpdb->prefix ."eshop_order_items";
 	$tablefields = $wpdb->get_results("DESCRIBE {$table}");
	$add_field = TRUE;
	foreach ($tablefields as $tablefield) {
		if(strtolower($tablefield->Field)=='tax_rate') {
			$add_field = FALSE;
		}
	}
	if ($add_field) {
		$wpdb->query("ALTER TABLE $table ADD `tax_rate` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `item_amt`");
 		$wpdb->query("ALTER TABLE $table ADD `tax_amt` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `tax_rate`");
	}
 	
}
//then do the necessary:
$eshopoptions['version']=ESHOP_VERSION;
update_option('eshop_plugin_settings', $eshopoptions);
// end of updates
?>