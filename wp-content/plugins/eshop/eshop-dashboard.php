<?php
if ('eshop-dashboard.php' == basename($_SERVER['SCRIPT_FILENAME']))
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
function eshop_small_stats($stock,$limit=5){
	global $wpdb;
	$rand=eshop_random_code('3');
	$table = $wpdb->prefix ."eshop_downloads";
	$stktable=$wpdb->prefix.'eshop_stock';
	switch($stock){
		case 'dloads':
			$mypages=$wpdb->get_results("Select id,title,purchases,downloads From $table order by purchases DESC LIMIT $limit");
			if(!count($mypages)>0) return;
			echo '<table class="widefat"><caption>'.__('Top Download Purchases','eshop').'</caption>';
			echo '<thead><tr><th id="edtitle'.$rand.'">'.__('Download','eshop').'</th><th id="eddown'.$rand.'">'.__('Downloads','eshop').'</th><th id="edpurch'.$rand.'">'.__('Purchases','eshop').'</th></tr></thead><tbody>';
			$calt=0;
			foreach($mypages as $row){
				$calt++;
				$alt = ($calt % 2) ? '' : ' class="alternate"';
				echo '<tr'.$alt.'>';
				echo '<td id="redid'.$row->id.'" headers="edtitle'.$rand.'"><a href="?page=eshop-downloads.php&amp;edit='.$row->id.'" title="edit details for '.$row->title.'">'.$row->title."</a></td>\n";
				echo '<td headers="eddown'.$rand.' redid'.$row->id.'">'.$row->downloads."</td>\n";
				echo '<td headers="edpurch'.$rand.' redid'.$row->id.'">'.$row->purchases."</td>\n";
				echo '</tr>'."\n";
			}
			echo '</tbody></table>'."\n";
			break;
		case 'stock':
		default:
			$mypages=$wpdb->get_results("SELECT $wpdb->posts.ID,$wpdb->posts.post_title, stk.purchases, stk.option_id
			from $wpdb->postmeta,$wpdb->posts, $stktable as stk
			WHERE $wpdb->postmeta.meta_key='_eshop_stock' 
			AND $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status != 'trash' 
			AND $wpdb->posts.post_status != 'revision' AND stk.post_id=$wpdb->posts.ID
			order by stk.purchases DESC LIMIT $limit");
			if(!count($mypages)>0) return;
			echo '<table class="widefat"><caption>'.__('Top Sellers','eshop').'</caption>';
			echo '<thead><tr><th id="edprod'.$rand.'">'.__('Product','eshop').'</th><th id="edpurch'.$rand.'">'.__('Purchases','eshop').'</th></tr></thead><tbody>';
			$calt=0;
			foreach($mypages as $page){
				$calt++;
				$alt = ($calt % 2) ? '' : ' class="alternate"';
				echo '<tr'.$alt.'><td id="repid'.$page->ID.$rand.$calt.'" headers="edprod'.$rand.'"><a href="post.php?action=edit&amp;post='.$page->ID.'">'.$page->post_title.'</a> '.$page->option_id.'</td>
				<td headers="edpurch'.$rand.' repid'.$page->ID.$rand.$calt.'">'.$page->purchases.'</td></tr>'."\n";
			}
			echo '</tbody></table>';
			break;
	}
}
// Create the function to output the contents of our Dashboard Widget

function eshop_dashboard_products_widget() {
	global $wpdb,$eshopoptions;

	$dtable=$wpdb->prefix.'eshop_orders';
	$itable=$wpdb->prefix.'eshop_order_items';
	$metatable=$wpdb->prefix.'postmeta';
	$poststable=$wpdb->prefix.'posts';
	$count = $wpdb->get_var("SELECT COUNT(meta.post_id) FROM $metatable as meta, $poststable as posts where meta.meta_key='_eshop_product' AND meta.meta_value!='' AND posts.ID = meta.post_id	AND posts.post_status != 'trash' AND posts.post_status != 'revision'");
	$stocked = $wpdb->get_results("
	SELECT DISTINCT meta.post_id
	FROM $metatable as meta, $poststable as posts
	WHERE meta.meta_key = '_eshop_product'
	AND meta.meta_value != ''
	AND posts.ID = meta.post_id
	AND posts.post_status != 'trash' AND posts.post_status != 'revision'		
	ORDER BY meta.post_id");

	$countprod=$countfeat=0;
	foreach($stocked as $stock){
		$eshop_product=maybe_unserialize(get_post_meta( $stock->post_id, '_eshop_product',true ));
		if($eshop_product['featured']=='Yes')
			$countfeat++;
		$stkav=get_post_meta( $stock->post_id, '_eshop_stock',true );
		if($stkav=='1'){
			$countprod++;
		}
	}
	$stktable = $wpdb->prefix ."eshop_stock";
	$stkpurc=0;
	$stkpurc=$wpdb->get_var("Select SUM(purchases) From $stktable");
	if($stkpurc<1){
		$stkpurc=0;
	}
	//work out totals for quick stats
	$dltable = $wpdb->prefix ."eshop_downloads";
	$total=$purchased=0;
	$total=$wpdb->get_var("Select SUM(downloads) From $dltable");
	$purchased=$wpdb->get_var("Select SUM(purchases) From $dltable");
	if($total<1){
		$total=0;
	}
	if($purchased<1){
		$purchased=0;
	}
	?>
	<ul class="eshop-stats">
	<li><strong><?php echo $count; ?></strong> <?php _e('Products.','eshop'); ?></li>
	<li><strong><?php echo $countprod; ?></strong> <?php _e('Products in stock.','eshop'); ?></li>
	<li><strong><?php echo $countfeat; ?></strong> <?php _e('Featured products.','eshop'); ?></li>
	<li><strong><?php echo $stkpurc; ?></strong> <?php _e('Purchases','eshop'); ?>.</li>
	<li><strong><?php echo $total; ?></strong> <?php _e('Total Downloads','eshop'); ?></li>
	<li><strong><?php echo $purchased; ?></strong> <?php _e('Total Download Purchases','eshop'); ?></li>
	</ul>
	<?php eshop_small_stats('stock'); ?>
	<?php eshop_small_stats('dloads'); ?>
	<?php
} 
function eshop_dashboard_orders_widget() {
	global $wpdb,$eshopoptions;
	$dtable=$wpdb->prefix.'eshop_orders';
	$dtable=$wpdb->prefix.'eshop_orders';
	$itable=$wpdb->prefix.'eshop_order_items';
	$metatable=$wpdb->prefix.'postmeta';
	$poststable=$wpdb->prefix.'posts';
	$array=array('Pending','Waiting','Completed','Sent','Failed','Deleted');
	$secarray=array();
	foreach($array as $k=>$type){
		$max = $wpdb->get_var("SELECT COUNT(id) FROM $dtable WHERE id > 0 AND status='$type'");
		switch($type){
			case 'Completed':
				$type=__('Active','eshop');
				break;
			case 'Sent':
				$type=__('Shipped','eshop');
				break;
			case 'Waiting':
				$type=__('Awaiting Payment','eshop');
				break;
		}
		$secarray[$type]=$max;
	}
	?>
	<table class="widefat">
		<thead>
		<tr>
			<th id="eshopot"><?php _e('Order Type','eshop'); ?></th>
			<th id="eshopcount"><?php _e('# Orders','eshop'); ?></th>
			<th id="eshoptotal"><?php _e('Sub Total','eshop'); ?></th>
			<th id="eshoptaxtotal"><?php _e('Sales Tax','eshop'); ?></th>
			<th id="eshopototal"><?php _e('Total','eshop'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$calt=0;
		//Sales
		$currsymbol=$eshopoptions['currency_symbol'];
		$atotal=$btotal=$ctotal=$ordcount=0;
		foreach($array as $k=>$otype){
			$itotal = $wpdb->get_row("SELECT SUM(item_amt * item_qty) as total, SUM(tax_amt) as taxtotal FROM $itable as i, $dtable as ch WHERE  i.checkid=ch.checkid AND ch.status='$otype'");
			switch($otype){
				case 'Pending':
					$type=__('Pending','eshop');
					break;
				case 'Failed':
					$type=__('Failed','eshop');
					break;
				case 'Deleted':
					$type=__('Deleted','eshop');
					break;
				case 'Completed':
					$type=__('Active','eshop');
					break;
				case 'Sent':
					$type=__('Shipped','eshop');
					break;
				case 'Waiting':
					$type=__('Awaiting Payment','eshop');
					break;
			}
			$calt++;
			$alt = ($calt % 2) ? '' : ' class="alternate"';
			$etotal=$itotal->total + $itotal->taxtotal;
			echo '<tr'.$alt.'>';
			echo '<th id="eshoptype'.$calt.'" headers="eshopot"><a href="admin.php?page=eshop-orders.php&amp;action='.$otype.'">'.$type.'</a></th>
			<td class="right" headers="eshoptype'.$calt.' eshopcount">'.$secarray[$type].'</td>
			<td class="right" headers="eshoptype'.$calt.' eshoptotal">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($itotal->total, __('2','eshop'))).'</td>
			<td class="right" headers="eshoptype'.$calt.' eshoptaxtotal">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($itotal->taxtotal, __('2','eshop'))).'</td>
			<td class="right" headers="eshoptype'.$calt.' eshopototal">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($etotal, __('2','eshop'))).'</td>';
			echo "</tr>\n";
			$atotal += $itotal->total;
			$btotal += $itotal->taxtotal;
			$ctotal += $etotal;
			$ordcount += $secarray[$type];
		}
		$calt++;
		$alt = ($calt % 2) ? '' : ' class="alternate"';
		echo '<tr'.$alt.'>';
		echo '<th id="eshoptot'.$calt.'" style="text-align:center">'.__('Totals', 'eshop').'</th>
		<td class="right" headers="eshoptot'.$calt.' eshopcount">'.$ordcount.'</td>
		<td class="right" headers="eshoptot'.$calt.' eshoptotal">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($atotal, __('2','eshop'))).'</td>
		<td class="right" headers="eshoptot'.$calt.' eshoptaxtotal">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($btotal, __('2','eshop'))).'</td>
		<td class="right" headers="eshoptot'.$calt.' eshopototal">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($ctotal, __('2','eshop'))).'</td>';
		echo "</tr>\n";
		echo '</tbody></table>';
}
function eshop_dashboard_mg_widget() {
	global $wpdb,$eshopoptions;
	$dtable=$wpdb->prefix.'eshop_orders';
	$paytype=$eshopoptions['method'];

	if(is_array($paytype)){
	?>
	<p><?php _e('Includes all orders.','eshop'); ?></p>
	<ul class="eshop-stats">
	<?php
	foreach($paytype as $gatetype){
		$gatetype=str_replace(".","",$gatetype);//mainly for authorize.net
		$mcount=$wpdb->get_var("SELECT COUNT(id) FROM $dtable WHERE paidvia='$gatetype'");
		if(strtolower($gatetype)==__('cash','eshop')){
			$eshopcash = $eshopoptions['cash'];
			if($eshopcash['rename']!='')
				$gatetype=$eshopcash['rename'];
		}
		if(strtolower($gatetype)==__('bank','eshop')){
			$eshopbank = $eshopoptions['bank'];
			if($eshopbank['rename']!='')
				$gatetype=$eshopbank['rename'];
		}
		?>
		<li><strong><?php echo $mcount; ?></strong> <?php echo ucwords($gatetype).' '.eshop_plural($mcount,__('order','eshop'),__('orders','eshop')); ?></li>
		<?php
	}
	?>
	</ul>
	<?php
	}else{
	?>
	<p><?php _e('No active gateways..','eshop'); ?></p>

	<?php
	}
}
// Create the function use in the action hook
function eshop_dashboard_disc_widget(){
	global $wpdb,$eshopoptions;
	$disctable=$wpdb->prefix.'eshop_discount_codes';
	$row=$wpdb->get_row("SELECT COUNT(id) as ids, SUM(IF(live='yes',1,0)) as live, SUM(USED) as total FROM $disctable WHERE id>0");
	if($row->ids>0){
		?>
		<ul class="eshop-stats">
			<li><strong><?php echo $row->ids; ?></strong> <?php _e('Total Available','eshop'); ?></li>
			<li><strong><?php echo $row->live; ?></strong> <?php _e('Active','eshop'); ?></li>
			<li><strong><?php echo $row->total; ?></strong> <?php _e('Total codes used','eshop'); ?></li>
		</ul>
		<?php
	}
}
function eshop_dashboard_data_widget(){
	if(!isset($_GET['eshopdl']))
		$dlpage='admin.php?page=eshop-orders.php&amp;eshopdl=yes';
	else
		$dlpage='admin.php?page=eshop-orders.php';
	?>
	<ul>
	<li><a href="<?php echo $dlpage; ?>"><?php _e('Download all transactions','eshop'); ?></a></li>
	<li><a href="<?php echo $dlpage; ?>&amp;os=mac"><?php _e('Mac users Download all transactions','eshop'); ?></a></li>
	<?php
	if(!isset($_GET['eshopddata']))
		$dlpage='admin.php?page=eshop-orders.php&amp;eshopddata=yes';
	else
		$dlpage='admin.php?page=eshop-orders.php';
	?>
	<li><a href="<?php echo $dlpage; ?>"><?php _e('Delete all orders and reset all stats','eshop'); ?></a></li>
	</ul>
	<?php
}
function eshop_add_dashboard_widgets() {
	if(current_user_can('eShop_admin')) {
		wp_add_dashboard_widget('eshop_dashboard_products_widget', __('eShop Product Stats','eshop'), 'eshop_dashboard_products_widget');
		wp_add_dashboard_widget('eshop_dashboard_orders_widget', __('eShop Orders Stats','eshop'), 'eshop_dashboard_orders_widget');	
		wp_add_dashboard_widget('eshop_dashboard_mg_widget', __('eShop Merchant Gateways','eshop'), 'eshop_dashboard_mg_widget');
		wp_add_dashboard_widget('eshop_dashboard_disc_widget', __('eShop Discount Codes','eshop'), 'eshop_dashboard_disc_widget');
		wp_add_dashboard_widget('eshop_dashboard_data_widget', __('eShop Data','eshop'), 'eshop_dashboard_data_widget');
	}
} 
?>