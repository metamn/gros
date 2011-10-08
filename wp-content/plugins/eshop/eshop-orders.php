<?php
if ('eshop-orders.php' == basename($_SERVER['SCRIPT_FILENAME']))
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

global $wpdb;
$eshopoptions = get_option('eshop_plugin_settings');

$eshopactionarray=array('Completed','Pending','Failed','Waiting','Sent','Deleted');

if (isset($_GET['action']) && in_array($_GET['action'],$eshopactionarray) )
	$action_status = esc_attr($_GET['action']);
else
	$_GET['action']=$action_status = 'Pending';

if(isset($_GET['eshop'])) $action_status='';

if(isset($_POST['eshopdeletedata'])){
	$etable[]=$wpdb->prefix.'eshop_orders';
	$etable[]=$wpdb->prefix.'eshop_download_orders';
	$etable[]=$wpdb->prefix.'eshop_order_items';
	foreach($etable as $detable){
		$wpdb->query("TRUNCATE $detable");
	}
	$dltable = $wpdb->prefix ."eshop_downloads";
	$wpdb->query("UPDATE $dltable set downloads='0',purchases='0'");
	$stktable = $wpdb->prefix ."eshop_stock";
	$wpdb->query("UPDATE $stktable set purchases='0'");
	echo '<p id="eshopddata">'.__('Records deleted','eshop').'</p>';
	unset($_GET['eshopddata']);
}
if(isset($_GET['eshopddata'])){
?>
	<form action="" method="post" id="eshopddata" name="eshopddata">
	<fieldset>
	<input type="hidden" name="eshopdeletedata" value='1' />
	<label for="esub"><?php _e('Delete all orders and reset all stats <small>(this action cannot be undone)</small>','eshop'); ?></label>
	<span class="submit eshop"><input type="submit" value="<?php _e('Delete','eshop'); ?>" id="esub" name="submit" /></span>
	</fieldset>
</form>
<?php
}
//admin note handling
if(isset($_POST['eshop-adnote'])){
	$dtable=$wpdb->prefix.'eshop_orders';
	if (isset($_GET['view']) && is_numeric($_GET['view'])){
		$view=$_GET['view'];
		$admin_note=$wpdb->escape($_POST['eshop-adnote']);
		if (isset($eshopoptions['users']) && $eshopoptions['users']=='yes'){
			$user_notes=$wpdb->escape($_POST['eshop-unote']);
		}else{
			$user_notes='';
		}
		$query2=$wpdb->query("UPDATE $dtable set admin_note='$admin_note',user_notes='$user_notes' where id='$view'");
		echo '<div class="updated fade"><p>'.__('Notes changed successfully.','eshop').'</p></div>';
	}else{
		echo '<div class="error fade"><p>'.__('Error: Notes were not changed.','eshop').'</p></div>';
	}
}

if (!function_exists('displayorders')) {
	function displayorders($type,$default){
		global $wpdb,$eshopoptions;
		//these should be global, but it wasn't working *sigh*
		$phpself=esc_url($_SERVER['REQUEST_URI']);
		$dtable=$wpdb->prefix.'eshop_orders';
		$itable=$wpdb->prefix.'eshop_order_items';
		if(!isset($_GET['by']))
			$_GET['by']=$default;
			
		$cda=$cdd=$ctn=$cca=$cna='';
		if(isset($_GET['by'])){
			switch ($_GET['by']) {
				case'dd'://date descending
					$sortby='ORDER BY custom_field DESC';
					$cdd=' class="current"';
					break;
				case'tn'://transaction id numerically
					$sortby='ORDER BY transid ASC';
					$ctn=' class="current"';
					break;
				case'na'://name alphabetically (last name)
					$sortby='ORDER BY last_name ASC';
					$cna=' class="current"';
					break;
				case'ca'://company name alphabetically
					$sortby='ORDER BY company ASC';
					$cca=' class="current"';
					break;
				case'da'://date ascending
				default:
					$sortby='ORDER BY custom_field ASC';
					$cda=' class="current"';
			}
		}else{
			$cda=' class="current"';
			$sortby='ORDER BY custom_field ASC';
		}
		
		$max = $wpdb->get_var("SELECT COUNT(id) FROM $dtable WHERE id > 0 AND status='$type'");
		if($max>0){
			if($eshopoptions['records']!='' && is_numeric($eshopoptions['records'])){
				$records=$eshopoptions['records'];
			}else{
				$records='10';
			}
			if(isset($_GET['_p']) && is_numeric($_GET['_p']))$epage=$_GET['_p'];
			else $epage='1';
			if(!isset($_GET['eshopall'])){
				$page_links = paginate_links( array(
					'base' => add_query_arg( '_p', '%#%' ),
					'format' => '',
					'total' => ceil($max / $records),
					'current' => $epage,
					'type'=>'array'
					));
				$offset=($epage*$records)-$records;
			}else{
				$page_links = paginate_links( array(
					'base' => add_query_arg( '_p', '%#%' ),
					'format' => '',
					'total' => ceil($max / $records),
					'current' => $epage,
					'type'=>'array',
					'show_all' => true,
				));
				$offset='0';
				$records=$max;
			}
			//
			$myrowres=$wpdb->get_results("Select * From $dtable where status='$type' $sortby LIMIT $offset, $records");
			$calt=0;
			$apge=get_admin_url().'admin.php?page='.$_GET['page'].'&amp;action='.$_GET['action'];
			echo '<ul id="eshopsubmenu" class="stuffbox">';
			echo '<li><span>'.__('Sort Orders by &raquo;','eshop').'</span></li>';
			echo '<li><a href="'.$apge.'&amp;by=da"'.$cda.'>'.__('Date Ascending','eshop').'</a></li>';
			echo '<li><a href="'.$apge.'&amp;by=dd"'.$cdd.'>'.__('Date Descending','eshop').'</a></li>';
			echo '<li><a href="'.$apge.'&amp;by=tn"'.$ctn.'>'.__('ID Number','eshop').'</a></li>';
			echo '<li><a href="'.$apge.'&amp;by=ca"'.$cca.'>'.__('Company','eshop').'</a></li>';
			echo '<li><a href="'.$apge.'&amp;by=na"'.$cna.'>'.__('Customer','eshop').'</a></li>';

			echo '</ul>';
		
			echo "<form id=\"orderstatus\" action=\"".$phpself."\" method=\"post\">";
			echo '<div class="orderlist tablecontainer">';
			echo '<table class="hidealllabels widefat" summary="order listing">
			<caption class="offset">'.__('eshop Order Listing','eshop').'</caption>
			<thead>
			<tr>
			<th id="line" title="'.__('reference number', 'eshop').'">#</th>
			<th id="date">'.__('Date/Time','eshop').'</th>
			<th id="customer">'.__('Customer','eshop').'</th>
			<th id="items">'.__('Items','eshop').'</th>
			<th id="price">'.__('Price','eshop').'</th>
			<th id="downloads">'.__('Contains Downloads','eshop').'</th>
			<th id="transid">'.__('Transaction ID','eshop').'</th>
			<th id="bulk"><input type="checkbox" value="" name="checkAllAuto" id="checkAllAuto" /><label for="checkAllAuto">'.__('Bulk','eshop').'</label></th></tr></thead><tbody>'."\n";
			$move=array();
			$c=0;
			foreach($myrowres as $myrow){
				//total + products
				$c++;//count for the  number of results.
				$checkid=$myrow->checkid;
				$itemrowres=$wpdb->get_results("Select * From $itable where checkid='$checkid'");
				$total=0;
				$x=0;
				foreach($itemrowres as $itemrow){
					$value=$itemrow->item_qty * $itemrow->item_amt;
					if($itemrow->tax_amt!=='' && is_numeric($itemrow->tax_amt))
						$value=$value + $itemrow->tax_amt;
					$total=$total + $value;
					$x++;
				}
				//
				$status=$type;
				//if($x>0){
					$thisdate = eshop_real_date($myrow->custom_field);
					
					$calt++;
					$alt = ($calt % 2) ? '' : ' class="alternate"';
					if($myrow->company!=''){
						$company=__(' of ','eshop').$myrow->company;
					}else{
						$company='';
					}
					$currsymbol=$eshopoptions['currency_symbol'];
					$ic=$x-1;
					$userlink='';
					if(isset($myrow->user_id) && $myrow->user_id!='0')
						$userlink=' (<a href="user-edit.php?user_id='.$myrow->user_id.'" title="'.esc_attr(sprintf(__('Profile for %1$s','eshop'),$myrow->first_name.' '.$myrow->last_name)).'" class="eshop-userlink">*</a>)';
					echo '<tr'.$alt.'>
					<td headers="line" id="numb'.$c.'">'.$myrow->id.'</td>
					<td headers="date numb'.$c.'">'.$thisdate.'</td>
					<td headers="customer numb'.$c.'"><a href="'.$phpself.'&amp;view='.$myrow->id.'" title="'.__('View complete order details','eshop').'">'.$myrow->first_name.' '.$myrow->last_name.$company.'</a>'.$userlink.'</td>
					<td headers="items numb'.$c.'">'.$ic.'</td>
					<td headers="price numb'.$c.'" class="right">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($total, __('2','eshop'))).'</td>
					<td headers="downloads numb'.$c.'" class="right">'.$myrow->downloads.'</td>
					<td headers="transid numb'.$c.'">'.$myrow->transid.'</td>'.
					'<td headers="bulk numb'.$c.'"><label for="move'.$c.'">Move #'.$c.'</label><input type="checkbox" value="'.$checkid.'" name="move[]" id="move'.$c.'" />'
					."</td></tr>\n";
				//}

			}
			echo "</tbody></table></div>\n";
			//paginate
				echo '<div class="paginate tablenav-pages stuffbox">';
				if($records!=$max){
					$eecho = $page_links;
				}
				echo sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>',
					number_format_i18n( ( $epage - 1 ) * $records + 1 ),
					number_format_i18n( min( $epage * $records, $max ) ),
					number_format_i18n( $max)
				);
				if(isset($eecho)){
					$thispage=esc_url(add_query_arg('eshopall', 'yes', $_SERVER['REQUEST_URI']));
					echo "<ul class='page-numbers'>\n\t<li>".join("</li>\n\t<li>", $eecho)."</li>\n<li>".'<a href="'.$thispage.'">'.__('View All','eshop').'</a>'."</li>\n</ul>\n";
				}
				echo '<br /></div>';
			//end
			
			//moved order status box
				?>
				<fieldset id="changestat"><legend><?php _e('Change Orders Status','eshop'); ?></legend>
				<p class="submit eshop"><label for="mark"><?php _e('Mark orders as:','eshop'); ?></label>
				<select name="mark" id="mark">
				<option value="Sent"><?php _e('Shipped','eshop'); ?></option>
				<option value="Completed"><?php _e('Active','eshop'); ?></option>
				<option value="Pending"><?php _e('Pending','eshop'); ?></option>
				<option value="Waiting"><?php _e('Awaiting Payment','eshop'); ?></option>
				<option value="Failed"><?php _e('Failed','eshop'); ?></option>
				<option value="Deleted"><?php _e('Deleted','eshop'); ?></option>
				</select>
				<input type="hidden" name="action" value="<?php echo $_GET['action']; ?>" />
				<input type="hidden" name="change" value="yes" />
				<input type="submit" id="submit1" value="<?php _e('Change','eshop'); ?>" /></p>
				</fieldset></form>
				<?php
	//order status box code end
			
			
			
			if($type=='Deleted'){
			?>
				<div id="eshopformleft"><form id="ordersdelete" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
				<fieldset><legend><?php _e('Complete Order Deletion','eshop'); ?></legend>
				<p class="submit eshop"><label for="dhours"><?php _e('Orders that are ','eshop'); ?>
				<select name="dhours" id="dhours">
				<option value="72" selected="selected">72</option>
				<option value="36">48</option>
				<option value="24">24</option>
				<option value="16">16</option>
				<option value="8">8</option>
				<option value="4">4</option>
				<option value="0">0</option>
				</select> <?php _e('hours old','eshop'); ?></label>
				<input type="hidden" name="dall" value="yes" />
				<input type="submit" id="submit2" value="Delete" /></p>
				</fieldset></form></div>
			<?php
			}
			
			
		}else{
			if($type=='Completed'){$disptype=__('Active','eshop');}
			if($type=='Sent'){$disptype=__('Shipped','eshop');}
			if($type=='Waiting'){$disptype=__('Awaiting Payment','eshop');}
			if($type=='Pending'){$disptype=__('Pending','eshop');}
			if($type=='Deleted'){$disptype=__('Deleted','eshop');}
			if($type=='Failed'){$disptype=__('Failed','eshop');}
			echo "<p class=\"notice\">";
			printf(__('There are no %s orders.','eshop'),"<span>" . __($disptype,'eshop') . "</span>");
			echo "</p>";
		}
	}
}
if (!function_exists('deleteorder')) {
	function deleteorder($delid){
		global $wpdb;
		$dtable=$wpdb->prefix.'eshop_orders';
		$itable=$wpdb->prefix.'eshop_order_items';
		$checkid=$wpdb->get_var("Select checkid From $dtable where id='$delid' && status='Deleted'");
		$delquery2=$wpdb->get_results("DELETE FROM $itable WHERE checkid='$checkid'");
		$delquery=$wpdb->get_results("DELETE FROM $dtable WHERE checkid='$checkid'");
		echo '<div class="updated fade">'.__('That order has now been deleted from the system.','eshop').'</div>';
	}
}

//sub sub menu - may change to a little form:
$phpself='?page='.$_GET['page'];
$dtable=$wpdb->prefix.'eshop_orders';
$itable=$wpdb->prefix.'eshop_order_items';
$stable=$wpdb->prefix.'eshop_states';
$ctable=$wpdb->prefix.'eshop_countries';
//$eshopoptions = get_option('eshop_plugin_settings');

/*
##########
##########
*/
if((isset($_GET['viewemail']) && is_numeric($_GET['viewemail']) ) || isset($_POST['thisemail'])){
	include 'eshop-email.php';
}else{



//paypal tries upto 4 days after a transaction.
$delit=4;
$wpdb->query("UPDATE $dtable set status='Deleted' where status='Pending' && edited < DATE_SUB(NOW(), INTERVAL $delit DAY)");

//try and move all orders that only have downloadable products
$moveit=$wpdb->get_results("Select checkid From $dtable where downloads='yes'");

foreach($moveit as $mrow){
	$pdownload=$numbrows=0;
	$result=$wpdb->get_results("Select down_id From $itable where checkid='$mrow->checkid' AND post_id!='0'");
	foreach($result as $crow){
		//check if downloadable product
		if($crow->down_id != '0')
			$pdownload++;

		$numbrows++;
	}
	if($pdownload==$numbrows){
		//in theory this will only activate if the order only contains downloads
		$wpdb->query("UPDATE $dtable set status='Sent' where status='Completed' && checkid='$mrow->checkid'");
	}
}



echo '<div class="wrap">';
if(isset($_GET['view'])){
	$view=$_GET['view'];
	$status=$wpdb->get_var("Select status From $dtable where id='$view'");
	if($status=='Completed'){$status=__('Active Order','eshop');}
	if($status=='Pending'){$status=__('Pending Order','eshop');}
	if($status=='Waiting'){$status=__('Orders Awaiting Payment','eshop');}
	if($status=='Sent'){$status=__('Shipped Order','eshop');}
	if($status=='Deleted'){$status=__('Deleted Order','eshop');}
	if($status=='Failed'){$status=__('Failed Order','eshop');}
	$state=$status;
}elseif(isset($_GET['action'])){
	switch ($_GET['action']) {
		case 'Completed':
			$state=__('Active Orders','eshop');
			break;
		case 'Pending':
			$state=__('Pending Orders','eshop');
			break;
		case 'Failed':
			$state=__('Failed Orders','eshop');
			break;
		case 'Waiting':
			$state=__('Orders Awaiting Payment','eshop');
			break;
		case 'Sent':
			$state=__('Shipped Orders','eshop');
			break;
		case 'Deleted':
			$state=__('Deleted Orders','eshop');
			break;
		default:
			break;
	}
}else{
	die ('<h2 class="error">'.__('Error','eshop').'</h2>');
}

echo '<div id="eshopicon" class="icon32"></div><h2>'.$state."</h2>\n";
eshop_admin_mode();
if(isset($_GET['delid']) && !isset($_GET['view'])){
	deleteorder($_GET['delid']);
	unset($_GET['view']);
	$_GET['action']=$_POST['action'];
	$_GET['action']='Deleted';
}
if(isset($_POST['dall'])){
	$dhours=$_POST['dhours'];
	if($_POST['dhours']=='0' ||$_POST['dhours']=='4'||$_POST['dhours']=='8'||$_POST['dhours']=='16'||$_POST['dhours']=='24'||$_POST['dhours']=='48'||$_POST['dhours']=='72'){
		$delay=$wpdb->escape($_POST['dhours']);
		$replace=$delay.__(' hours','eshop');
		if($delay==24){$replace=__('1 day','eshop');}
		$dtable=$wpdb->prefix.'eshop_orders';
		$itable=$wpdb->prefix.'eshop_order_items';
		$myrows=$wpdb->get_results("Select checkid From $dtable where status='Deleted' && edited < DATE_SUB(NOW(), INTERVAL $delay HOUR)");
		foreach($myrows as $myrow){
			$checkid=$myrow->checkid;
			$delquery2=$wpdb->query("DELETE FROM $itable WHERE checkid='$checkid'");
			$query2=$wpdb->query("DELETE FROM $dtable WHERE status='Deleted' && checkid='$checkid' && edited < DATE_SUB(NOW(), INTERVAL $delay HOUR)");
		}
		echo '<div class="updated fade">'.__('Deleted orders older than','eshop').' '.$replace.' '.__('have now been <strong>completely</strong> deleted.','eshop').'</div>';
	}else{
		echo '<p class="error">'.__('There was an error, and nothing has been deleted.','eshop').'</p>';
	}
}
if(isset($_POST['mark']) && !isset($_POST['change'])){
	$mark=$_POST['mark'];
	$checkid=$_POST['checkid'];
	$query2=$wpdb->get_results("UPDATE $dtable set status='$mark' where checkid='$checkid'");
	echo '<div class="updated fade">'.__('Order status changed successfully.','eshop').'</div>';
}

if(isset($_POST['change'])){
	if(isset($_POST['move']) && $_POST['move'][0]!=''){
		foreach($_POST['move'] as $v=>$ch){
			$mark=$_POST['mark'];
			$query2=$wpdb->get_results("UPDATE $dtable set status='$mark' where checkid='$ch'");
		}
		echo '<div class="updated fade"><p>'.__('Order status changed successfully.','eshop').'</p></div>';
	}else{
		echo '<div class="error fade"><p>'.__('No orders were selected.','eshop').'</p></div>';
	}
}


echo '<ul class="nav-tab-wrapper">';
if(current_user_can('eShop_admin'))
	$stati=array('Pending' => __('Pending','eshop'),'Waiting'=>__('Awaiting Payment','eshop'),'Completed'=>__('Active','eshop'),'Sent'=>__('Shipped','eshop'),'Failed'=>__('Failed','eshop'),'Deleted'=>__('Deleted','eshop'));
else
	$stati=array();

$dtable=$wpdb->prefix.'eshop_orders';
$myres=$wpdb->get_results("SELECT COUNT( id ) as amt, status FROM $dtable WHERE id >0 GROUP BY status");
foreach ($myres as $row ){
	$counted[$row->status]=$row->amt;
}

foreach ( $stati as $status => $label ) {
	$class = '';
	if ( $status == $action_status )
		$class = ' nav-tab-active';
	$cnt='(0)';
	if(isset($counted[$status]))
		$cnt='('.$counted[$status].')';
	$status_links[] = '<li><a href="?page=eshop-orders.php&amp;action='.$status.'" class="nav-tab'.$class.'">' . $label . ' <span class="count">'.$cnt.'</span></a>';
}
echo implode('</li>', $status_links) . '</li>';
echo '</ul><br class="clear" />';



if (isset($_GET['view']) && is_numeric($_GET['view'])){
	$view=$wpdb->escape($_GET['view']);
	if (isset($_GET['adddown']) && is_numeric($_GET['adddown'])){
		$dordtable=$wpdb->prefix.'eshop_download_orders';
		$adddown=$wpdb->escape($_GET['adddown']);
		$wpdb->query("UPDATE $dordtable SET downloads=downloads+1 where id='$adddown' limit 1");
		echo '<div class="updated fade">'.__('Download allowance increased.','eshop').'</div>';
	}
	
	$dquery=$wpdb->get_results("Select * From $dtable where id='$view'");
	foreach($dquery as $drow){
		$status=$drow->status;
		$checkid=$drow->checkid;
		$custom=$drow->custom_field;
		$transid=$drow->transid;
		$admin_note=htmlspecialchars(stripslashes($drow->admin_note));
		$user_notes=htmlspecialchars(stripslashes($drow->user_notes));
		$paidvia=$drow->paidvia;
		$eshopaff=$drow->affiliate;
	}
	
	if($status=='Completed'){$status=__('Active','eshop');}
	if($status=='Pending'){$status=__('Pending','eshop');}
	if($status=='Sent'){$status=__('Shipped','eshop');}
	if($status=='Waiting'){$status=__('Awaiting Payment','eshop');}
	if($status=='Failed'){$status=__('Failed','eshop');}
	if($status=='Deleted'){$status=__('Deleted','eshop');}

	//moved order status box
	echo "<div id=\"eshopformfloat\"><form id=\"orderstatus\" action=\"".$phpself."\" method=\"post\">";
	?>
	<fieldset><legend><?php _e('Change Order Status','eshop'); ?></legend>
	<p class="submit eshop"><label for="mark"><?php _e('Mark order as:','eshop'); ?></label>
	<select name="mark" id="mark">
	<option value="Sent"><?php _e('Shipped','eshop'); ?></option>
	<option value="Completed"><?php _e('Active','eshop'); ?></option>
	<option value="Waiting"><?php _e('Awaiting Payment','eshop'); ?></option>
	<option value="Pending"><?php _e('Pending','eshop'); ?></option>
	<option value="Failed"><?php _e('Failed','eshop'); ?></option>
	<option value="Deleted"><?php _e('Deleted','eshop'); ?></option>
	</select>
	<input type="hidden" name="action" value="<?php echo $_GET['action']; ?>" />
	<input type="hidden" name="checkid" value="<?php echo $checkid; ?>" />
	<input type="submit" id="submit3" value="<?php _e('Change','eshop'); ?>" /></p>
	</fieldset></form></div>
	<?php
	//order status box code end
	echo '<h3 class="status"><span>'.$status.'</span> '.__('Order Details','eshop').' <small>('.$view.')</small></h3>';
	$result=$wpdb->get_results("Select * From $itable where checkid='$checkid' ORDER BY id ASC");
	$totaltax=$total=0;
	$calt=0;
	$currsymbol=$eshopoptions['currency_symbol'];
	?>
	<div class="orders tablecontainer">
	<p><?php _e('Transaction ID:','eshop'); ?> <strong><?php echo $transid; ?></strong></p>
	<?php
	if($admin_note!=''){
		echo '<div id="eshop_admin_note" class="noprint"><h4>'.__('Admin Note:','eshop')."</h4>\n";
		echo nl2br($admin_note).'</div>'."\n";
		echo '<p class="eshop_edit_note noprint"><a href="#eshop-anote">'.__('Edit admin note','eshop').'</a></p>';
	}else{
		echo '<p class="eshop_edit_note noprint"><a href="#eshop-anote">'.__('Add admin note','eshop').'</a></p>';
	}
	?>
	
	<table class="widefat" summary="<?php _e('Table for order details','eshop'); ?>">
	<caption><?php _e('Order Details','eshop'); ?></caption>
	<thead>
	<tr>
	<th id="opname"><?php _e('Product Name','eshop'); ?></th>
	<th id="oitem"><?php _e('Item or Unit Data','eshop'); ?></th>
	<th id="odown"><?php _e('Download?','eshop'); ?></th>
	<th id="oqty"><?php _e('Quantity','eshop'); ?></th>
	<th id="oprice"><?php _e('Price','eshop'); ?></th>
	<?php if(isset($eshopoptions['tax']) && $eshopoptions['tax']=='1') : ?>
	<th id="otax"><?php _e('Tax Rate','eshop'); ?></th>
	<th id="otaxamt"><?php _e('Tax amt','eshop'); ?></th>
	<?php endif; ?>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach($result as $myrow){
		$value=$myrow->item_qty * $myrow->item_amt;
		if(isset($eshopoptions['tax']) && $eshopoptions['tax']=='1') {
			$linetax='';
			if($myrow->tax_amt!=='' && is_numeric($myrow->tax_amt)) {
				$linetax=$myrow->tax_amt;
				$totaltax=$totaltax+$linetax;
			}
		}else{
			if($myrow->tax_amt!=='' && is_numeric($myrow->tax_amt)) {
				$value = $value + $myrow->tax_amt;
			}
		}
		$total=$total+$value;
		$itemid=$myrow->item_id;
		if($myrow->optsets!='')
			$itemid.='<span class="eshopoptsets">'.nl2br($myrow->optsets).'</span>';
		//check if downloadable product
		$dordtable=$wpdb->prefix.'eshop_download_orders';
		$downstable=$wpdb->prefix.'eshop_downloads';
		$downloadable='';
		if($myrow->down_id!='0'){
			//item is a download
			$dlinfo= $wpdb->get_row("SELECT d.downloads, d.id FROM $dordtable as d, $downstable as dl WHERE d.checkid='$myrow->checkid' AND dl.id='$myrow->down_id' AND d.files=dl.files");
			if(isset($dlinfo->downloads)){
				$downloadable='<span class="downprod">'.__('Yes - remaining:','eshop');
				$downloadable .=' '.$dlinfo->downloads.'<a href="'.$phpself.'&amp;view='.$view.'&amp;adddown='.$dlinfo->id.'" title="'.__('Increase download allowance by 1','eshop').'">'.__('Increase','eshop').'</a></span>';
			}else{
				$downloadable = __('Download Item Missing','eshop');
			}
		}
	
		// add in a check if postage here as well as a link to the product
		$showit=$myrow->optname;
		$calt++;
		$alt = ($calt % 2) ? '' : ' class="alternate"';
		echo '<tr'.$alt.'>
		<td id="onum'.$calt.'" headers="opname">'.$showit.'</td>
		<td headers="oitem onum'.$calt.'">'.$itemid.'</td>
		<td headers="odown onum'.$calt.'">'.$downloadable.'</td>
		<td headers="oqty onum'.$calt.'">'.$myrow->item_qty.'</td>
		<td headers="oprice onum'.$calt.'" class="right">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($value, __('2','eshop')))."</td>\n";
		if(isset($eshopoptions['tax']) && $eshopoptions['tax']=='1') {
			echo '<td headers="otax onum'.$calt.'" class="right">'.$myrow->tax_rate.'</td>';
			$ectax='';
			if( $linetax !='' )
				$ectax=sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($linetax, __('2','eshop')));
			
			echo '<td headers="otaxamt onum'.$calt.'" class="right">'.$ectax."</td>\n";
		}
		echo "</tr>\n";

	}
	if($transid==__('Processing&#8230;','eshop'))
		echo "<tr><td colspan=\"4\" class=\"totalr\">".__('Total &raquo;','eshop')." </td><td class=\"total\">".sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($total, __('2','eshop')))."</td>";
	else
		echo "<tr><td colspan=\"4\" class=\"totalr\">".sprintf(__('Total paid via %1$s &raquo;','eshop'),ucfirst($paidvia))." </td><td class=\"total\">".sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($total, __('2','eshop')))."</td>\n";
	
	if(isset($eshopoptions['tax']) && $eshopoptions['tax']=='1') {
		echo '<td class="totalr">'.__('Total Tax &raquo;','eshop').'</td>';
		echo '<td class="total">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($totaltax, __('2','eshop')))."</td>\n";
		$totalwithtax=$total + $totaltax;
		echo '</tr>
		<tr><td colspan="6" class="totalr">'.__('Total with tax &raquo;','eshop').'</td><td class="total">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($totalwithtax, __('2','eshop')))."</td>\n";
	}
	echo "</tr></tbody></table>\n";
			
	$thisdate = eshop_real_date($custom);
	echo "<p>".__('Order placed on','eshop')." <strong>".$thisdate."</strong>.";
	if($eshopaff!='') echo '<br />'.__('Affiliate Reference:','eshop').' <strong>'.$eshopaff.'</strong>';
	echo "</p>\n</div>\n";
	if($drow->reference!=''){
		echo '<p><strong>'.__('Customer reference:','eshop').'</strong> '.$drow->reference.'</p>';
	}
	echo "<div class=\"orderaddress\"><h4>".__('Invoice','eshop')."</h4>";
	foreach($dquery as $drow){
		$userlink='';
		if(isset($drow->user_id) && $drow->user_id!='0')
			$userlink=' (<a href="user-edit.php?user_id='.$drow->user_id.'" title="'.esc_attr(sprintf(__('Profile for %1$s','eshop'),$drow->first_name.' '.$drow->last_name)).'" class="eshop-userlink">*</a>)';

		echo '<p><strong>'.__("Name: ",'eshop').'</strong>'.$drow->first_name." ".$drow->last_name.$userlink."<br />\n";
		if($drow->company!='') echo '<strong>'.__("Company: ",'eshop').'</strong>'.$drow->company."<br />\n";
		echo '<strong>'.__('Email:','eshop').'</strong>'." <a href=\"".$phpself."&amp;viewemail=".$view."\" title=\"".__('Send a form email','eshop')."\">".$drow->email.'</a> <small class="noprint">'.__('(sends a form email)','eshop')."</small><br />\n";
		if('no' == $eshopoptions['downloads_only']){
			echo '<strong>'.__("Phone: ",'eshop').'</strong>'.$drow->phone."</p>\n";

			echo '<h5>'.__('Address','eshop').'</h5>';
			$address=$drow->address1;
			if($drow->address2!='') $address.= ', '.$drow->address2;

			echo '<address>'.$drow->first_name." ".$drow->last_name."<br />\n";
			if($drow->company!='') echo __("Company: ",'eshop').$drow->company."<br />\n";
			echo $address."<br />\n";
			echo $drow->city."<br />\n";
			$qcode=$wpdb->escape($drow->state);
			$qstate = $wpdb->get_var("SELECT stateName FROM $stable WHERE id='$qcode' limit 1");
			if($qstate!=''){
				echo $qstate."<br />";
				$statezone = $wpdb->get_var("SELECT zone FROM $stable WHERE id='$qcode' limit 1");
			}else{
				echo $drow->state."<br />";
			}
			echo $drow->zip."<br />\n";

			$qcode=$wpdb->escape($drow->country);
			$qcountry = $wpdb->get_var("SELECT country FROM $ctable WHERE code='$qcode' limit 1");
			$countryzone = $wpdb->get_var("SELECT zone FROM $ctable WHERE code='$qcode' limit 1");
			echo $qcountry."</address>";
			/*
			if($eshopoptions['shipping_zone']=='country'){
				$qzone=$countryzone;
			}else{
				$qzone=$statezone;
				if($statezone=='') $qzone=$eshopoptions['unknown_state'];
			}
			echo '<p>'.__('Shipping Zone: ','eshop')."<strong>".$qzone."</strong></p>
			*/
			echo "</div>\n";
			if($drow->ship_name!='' && $drow->ship_address!='' && $drow->ship_city!='' && $drow->ship_postcode!=''){
				echo "<div class=\"shippingaddress\"><h4>".__('Shipping','eshop')."</h4>";
				echo '<p><strong>'.__("Name: ",'eshop').'</strong>'.$drow->ship_name."<br />\n";
				if($drow->ship_company!='') echo '<strong>'.__("Company: ",'eshop').'</strong>'.$drow->ship_company."<br />\n";
				echo '<strong>'.__("Phone: ",'eshop').'</strong>'.$drow->ship_phone."</p>\n";
				echo '<h5>'.__('Address','eshop').'</h5>';
				echo '<address>'.$drow->ship_name.'<br />'."\n";
				if($drow->ship_company!='') echo $drow->ship_company."<br />\n";
				echo $drow->ship_address."<br />\n";
				echo $drow->ship_city."<br />\n";
				$qcode=$wpdb->escape($drow->ship_state);
				$qstate = $wpdb->get_var("SELECT stateName FROM $stable WHERE id='$qcode' limit 1");
				if($qstate!=''){
					$statezone = $wpdb->get_var("SELECT zone FROM $stable WHERE id='$qcode' limit 1");
					echo $qstate."<br />";
				}else{
					echo $drow->ship_state."<br />";
				}
				echo $drow->ship_postcode."<br />\n";
				$qcode=$wpdb->escape($drow->ship_country);
				$qcountry = $wpdb->get_var("SELECT country FROM $ctable WHERE code='$qcode' limit 1");
				$countryzone = $wpdb->get_var("SELECT zone FROM $ctable WHERE code='$qcode' limit 1");
				echo $qcountry."</address>";
				/*
				if($eshopoptions['shipping_zone']=='country'){
					$qzone=$countryzone;
				}else{
					$qzone=$statezone;
					if($statezone=='') $qzone=$eshopoptions['unknown_state'];
				}
				echo '<p>'. __('Shipping Zone:','eshop')." <strong>".$qzone."</strong></p>
				*/
				echo "</div>\n";
			}
		}else{
			echo '</p></div>';
		}
		
		do_action('eshopshowdetails',$drow);
		echo '<hr class="eshopclear" />';
		if($drow->thememo!=''){
			echo '<div class="paypalmemo"><h4>'.__('Customer paypal memo:','eshop').'</h4><p>'.nl2br($drow->thememo).'</p></div>';
		}
		
		if($drow->comments!=''){
			echo '<div class="eshopmemo"><h4>'.__('Customer order comments:','eshop').'</h4><p>'.nl2br($drow->comments).'</p></div>';
		}
		if($drow->thememo!='' || $drow->comments!=''){
			echo '<hr class="eshopclear" />';
		}
	}
	//admin note form goes here
	?>
	<form method='post' action="" id="eshop-anote"><fieldset><legend><?php _e('Notes','eshop'); ?></legend>
	<p><label for="eshop-adnote"><?php _e('Admin Note','eshop'); ?></label><br />
	<textarea rows="5" cols="80" id="eshop-adnote" name="eshop-adnote"><?php echo $admin_note; ?></textarea></p>
	<?php
	if (isset($eshopoptions['users']) && $eshopoptions['users']=='yes'){
	?>
	<p><label for="eshop-unote"><?php _e('Note for customer (will be displayed on their order view page)','eshop'); ?></label><br />
	<textarea rows="5" cols="80" id="eshop-unote" name="eshop-unote"><?php echo $user_notes; ?></textarea></p>
	<?php
	}
	?>
	<p class="submit eshop"><input type="submit" class="button-primary" value="<?php _e('Update Notes','eshop'); ?>" name="submit" /></p>
	</fieldset>
	</form>
	<?php	
	if($status=='Deleted'){$delete="<p class=\"delete noprint\"><a href=\"".$phpself."&amp;delid=".$view."\">".__('Completely delete this order?','eshop')."</a><br />".__('<small><strong>Warning:</strong> this order will be completely deleted and cannot be recovered at a later date.</small>','eshop')."</p>";}else{$delete='';};
	echo $delete;
}else{

	if (empty($_GET['action'])) $_GET['action'] = 'Completed';  
	switch ($_GET['action']) {
		case 'Completed':
			displayorders('Completed','da');
			break;
		case 'Failed':
			displayorders('Failed','dd');
			break;
		case 'Waiting':
			displayorders('Waiting','da');
			break;
		case 'Sent':
			displayorders('Sent','dd');
			break;
		case 'Deleted':
			displayorders('Deleted','dd');
			break;
		case 'Pending':
		default:
			displayorders('Pending','da');
			break;
	}
}

echo '<br class="clearbr" />&nbsp;</div>';

}
?>