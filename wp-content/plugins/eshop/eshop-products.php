<?php
if ('eshop-products.php' == basename($_SERVER['SCRIPT_FILENAME']))
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

function eshop_products_manager() {
	global $wpdb, $user_ID,$eshopoptions;
	get_currentuserinfo();
	//add in if current user can here
	if(current_user_can('eShop_admin')){
		$eshopfilter='all';
		if(isset($_POST['eshopfiltering'])){
			$eshopfilter=$_POST['eshopfilter'];
		}
		?>
		<div class="wrap">
		<div id="eshopicon" class="icon32"></div><h2><?php _e('Products','eshop'); ?></h2>
		<?php eshop_admin_mode(); ?>
		<h3><?php _e('Authors','eshop'); ?></h3>
		<?php if(isset($msg)) echo '<div class="updated fade"><p>'.$msg.'</p></div>'; ?>
		<form action="" method="post" class="eshop filtering">
		<p><label for="eshopfilter"><?php _e('Show products for','eshop'); ?></label><select name="eshopfilter" id="eshopfilter">
		<?php
		echo eshop_authors($eshopfilter);
		?>
		</select><input type="submit" name="eshopfiltering" id="submit"  class="submit button-primary" value="Filter" /></p>
		</form>
		</div>
	<?php
	}
	?>
	<div class="wrap">
	<h3><?php _e('Products Table','eshop'); ?></h3>
	<p><?php _e('A reference table for identifying products','eshop'); ?>.</p>
	<?php
	if(isset($_POST['eshopqp']) && isset($_POST['product'])){
		foreach($_POST['product'] as $id=>$type){
			$pid=$id;
			$stocktable=$wpdb->prefix ."eshop_stock";
			$eshop_product=maybe_unserialize(get_post_meta( $pid, '_eshop_product',true ));
			$total=0;
			for($i=1;$i<=$eshopoptions['options_num'];$i++){
				if(isset($type[$i]['stkqty']) && is_numeric($type[$i]['stkqty'])){
					$meta_value=$type[$i]['stkqty'];
					$results=$wpdb->get_results("select post_id from $stocktable where post_id=$pid && option_id=$i");
					if(!empty($results)){
						$wpdb->query($wpdb->prepare("UPDATE $stocktable set available=$meta_value where post_id=$pid && option_id=$i"));
					}else{
						$wpdb->query($wpdb->prepare("INSERT INTO $stocktable (post_id,option_id,available,purchases) VALUES ($pid,$i,$meta_value,0)"));
					}
					$total=$total+$type[$i]['stkqty'];
				}
			}
			if(isset($type['sale'])){
				$eshop_product['sale']='yes';
				update_post_meta( $id, '_eshop_sale', 'yes');
			}else{
				$eshop_product['sale']='no';
				delete_post_meta( $id, '_eshop_sale');
			}
			if(isset($type['featured'])){
				$eshop_product['featured']='Yes';
				update_post_meta( $id, '_eshop_featured', 'Yes');
			}else{
				$eshop_product['featured']='no';
				delete_post_meta( $id, '_eshop_featured');
			}
			if(isset($type['stkavail']))
				$stkav='1';
			else
				$stkav='0';
			
			if($stkav=='1' && ($eshop_product['description']=='' || $eshop_product['sku']=='' || $eshop_product['products']['1']['option']=='' || $eshop_product['products']['1']['price']=='')) {
				$stkav='0';
				$emsg[0] = 'set';
			}
			if($stkav=='1' && ($total==0 && 'yes' == $eshopoptions['stock_control'])){
				$stkav='0';
				$emsg [1]= 'set';
			}
			if($stkav=='1')
				update_post_meta( $pid, '_eshop_stock', $stkav);
			else
				delete_post_meta( $id, '_eshop_stock');
				
			update_post_meta( $pid, '_eshop_product', $eshop_product);
		}
		$msg='';
		if(isset($emsg)){
			if(isset($emsg[0]))
				$msg .=  __(' Some products were marked as not available due to missing information, check the individual product pages.','eshop');
			if(isset($emsg[1]))
				$msg .= __(' Some products were marked as not available as no stock was found.','eshop');
		}
		echo '<div id="message" class="updated fade">'.__('Products have been updated.','eshop'). $msg . "</div>\n";
	}
	//sort by switch statement
	$csa=$csb=$csc=$csd=$cse=$csf='';
	if(isset($_GET['by'])){
		switch ($_GET['by']) {
			case'sa'://date descending
				$sortby='sku';
				$csa=' class="current"';
				break;
			case'sb'://description alphabetically
				$sortby='description';
				$csb=' class="current"';
				break;
		/*
			case'sc'://name alphabetically (last name)
				$sortby='shiprate';
				$csc=' class="current"';
				break;
		*/
			case'sd'://stock availability 
				$sortby='_stock';
				$csd=' class="current"';
				break;
		
			case'se'://transaction id numerically
				$sortby='_featured';
				$cse=' class="current"';
				break;
			case'sf'://date ascending
			default:
				$sortby='id';
				$csf=' class="current"';
		}
	}else{
		$csf=' class="current"';
		$sortby='id';
	}
	
	if(current_user_can('eShop_admin')){
		if($eshopfilter=='all')
			$addtoq='';
		elseif(is_numeric($eshopfilter))
			$addtoq="AND posts.post_author = $eshopfilter";
		else
			die('There was an error');
	}else{
		$addtoq="AND posts.post_author = $user_ID ";
	}
	$numoptions=$eshopoptions['options_num'];
	$metatable=$wpdb->prefix.'postmeta';
	$poststable=$wpdb->prefix.'posts';
	$range=10;
	
	$max = $wpdb->get_var("SELECT COUNT(meta.post_id) FROM $metatable as meta, $poststable as posts where meta.meta_key='_eshop_product' 
	AND posts.ID = meta.post_id 
	AND posts.post_status != 'trash' AND posts.post_status != 'revision'".$addtoq);
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
	
	if($max>0){
		$apge=get_admin_url().'admin.php?page='.$_GET['page'];
		echo '<ul id="eshopsubmenu" class="stuffbox">';
		echo '<li><span>'.__('Sort Products by &raquo;','eshop').'</span></li>';
		echo '<li><a href="'.$apge.'&amp;by=sf"'.$csf.'>'.__('ID Number','eshop').'</a></li>';
		echo '<li><a href="'.$apge.'&amp;by=sa"'.$csa.'>'.__('Sku','eshop').'</a></li>';
		echo '<li><a href="'.$apge.'&amp;by=sb"'.$csb.'>'.__('Product','eshop').'</a></li>';
		// echo '<li><a href="'.$apge.'&amp;by=sc"'.$csc.'>'.__('Shipping','eshop').'</a></li>';
		echo '<li><a href="'.$apge.'&amp;by=sd"'.$csd.'>'.__('Stock','eshop').'</a></li>';
		echo '<li><a href="'.$apge.'&amp;by=se"'.$cse.'>'.__('Featured','eshop').'</a></li>';
		echo '</ul>';
		
		if(current_user_can('eShop_admin')){
			if($eshopfilter=='all')
				$addtoq='';
			elseif(is_numeric($eshopfilter))
				$addtoq="AND posts.post_author = '$eshopfilter'";
			else
				die('There was an error');
		}else{
			$addtoq="AND posts.post_author = '$user_ID' ";
		}

		$myrowres=$wpdb->get_results("
		SELECT DISTINCT meta.post_id
		FROM $metatable as meta, $poststable as posts
		WHERE meta.meta_key = '_eshop_product'
		AND posts.ID = meta.post_id
		$addtoq
		AND posts.post_status != 'trash' AND posts.post_status != 'revision'
		ORDER BY meta.post_id");

		$calt=0;
		$currsymbol=$eshopoptions['currency_symbol'];
		$x=0;

		//add in post id( doh! )
		foreach($myrowres as $row){
			$grabit[$x]=maybe_unserialize(get_post_meta( $row->post_id, '_eshop_product',true ));//get_post_custom($row->post_id);
			$grabit[$x]['_eshop_stock']=get_post_meta( $row->post_id, '_eshop_stock',true);//get_post_custom($row->post_id);
			$grabit[$x]['id']=$row->post_id;
			$grabit[$x]['_featured']='1';
			$grabit[$x]['_stock']='1';

			if(strtolower($grabit[$x]['featured'])=='yes') $grabit[$x]['_featured']='0';
			if(strtolower($grabit[$x]['_eshop_stock'])=='1') $grabit[$x]['_stock']='0';
			$x++;
		}

		$array=$grabit;
		$grab=subval_sort($array,$sortby); 

		//grabs some tax data
		if(!isset($eshopoptions['etax']))$eshopoptions['etax']=array();
		$etax = $eshopoptions['etax'];
		if(!isset($etax['bands']) || $etax['bands']=='') $etax['bands']='0';
	?>	
		<form action="" method="post" class="eshop">
		<div class="eshopwidetable">
		<table class="hidealllabels widefat" summary="product listing">
		<caption><?php _e('Product Quick reference table','eshop'); ?></caption>
		<thead>
		<tr>
		<th id="sku"><?php _e('Sku','eshop'); ?></th>
		<th id="ids"><?php _e('ID','eshop'); ?></th>
		<th id="page"><?php _e('Page','eshop'); ?></th>
		<th id="desc"><?php _e('Description','eshop'); ?></th>
		<th id="down"><abbr title="<?php _e('Downloads','eshop'); ?>"><?php _e('DL','eshop'); ?></abbr></th>
		<th id="ship"><abbr title="<?php _e('Shipping Rate','eshop'); ?>"><?php _e('S/R','eshop'); ?></abbr></th>
		<th id="stkavail"><abbr title="<?php _e('Stock Available','eshop'); ?>"><?php _e('Stk avail.','eshop'); ?></abbr></th>
		<th id="purc"><abbr title="<?php _e('Number of Purchases','eshop'); ?>"><?php _e('Purc.','eshop'); ?></abbr></th>
		<th id="ftrd"><abbr title="<?php _e('Marked as Featured','eshop'); ?>"><?php _e('Feat.','eshop'); ?></abbr></th>
		<th id="onsale" title="<?php _e('Product on sale','eshop'); ?>"><?php _e('Sale','eshop'); ?></th>
		<th id="opt"><?php _e('Option/Price','eshop'); ?></th>
		<?php if ( $etax['bands'] > 0 ) : ?>
		<th id="tax"><?php _e('Tax','eshop'); ?></th>
		<?php endif; ?>
		<th id="stk"><abbr title="<?php _e('Stock Level','eshop'); ?>"><?php _e('Stk','eshop'); ?></abbr></th>
		<th id="associmg"><?php _e('Thumbnail','eshop'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$scc=0;
		$start =($epage * $records)-($records);
		$grab=array_slice($grab,$start,$records);
		foreach($grab as $grabit){
			$eshop_product=$grabit;
			if(isset($grabit['_eshop_stock']) && is_numeric($grabit['_eshop_stock']))
				$stkav=$grabit['_eshop_stock'];
			else
				$stkav=0;
			$pdownloads='no';
			if(isset($eshop_product['products']['1']['price']) && $eshop_product['products']['1']['price']!=''){
			//reset array
				$purcharray=array();
				//get page title
				$ptitle=get_post($grabit['id']);
				$getid=$grabit['id'];
				//get download file title
				$pdown='';
				//check if downloadable product
				for($i=1;$i<=$eshopoptions['options_num'];$i++){
					if($eshop_product['products'][$i]['option']!=''){
						if(isset($eshop_product['products'][$i]['download']) && $eshop_product['products'][$i]['download']!=''){
							$dltable=$wpdb->prefix.'eshop_downloads';
							$fileid=$eshop_product['products'][$i]['download'];
							$filetitle=$wpdb->get_var("SELECT title FROM $dltable WHERE id='$fileid'");;
							$pdown.='<a href="admin.php?page=eshop-downloads.php&amp;edit='.$fileid.'">'.$filetitle.'</a>';
							$pdownloads='yes';
						}else{
							$pdown.='<br />';
						}
					}
				}
				if($ptitle->post_title=='')
					$posttitle=__('(no title)');
				else
					$posttitle=$ptitle->post_title;
				$calt++;
				$alt = ($calt % 2) ? '' : ' class="alternate"';
				echo '<tr'.$alt.'>';
				echo '<td id="sku'.$calt.'" headers="sku">'.$eshop_product['sku'].'</td>';
				echo '<td headers="ids sku'.$calt.'">'.$getid.'<input type="hidden" value="1" name="product['.$getid.'][theid]" /></td>';
				echo '<td headers="page sku'.$calt.'"><a href="post.php?action=edit&amp;post='.$getid.'" title="id: '.$getid.'">'.$posttitle.'</a></td>';
				echo '<td headers="desc sku'.$calt.'">'.stripslashes(esc_attr($eshop_product['description'])).'</td>';
				echo '<td headers="down sku'.$calt.'">'.$pdown.'</td>';
				echo '<td headers="ship sku'.$calt.'">'.$eshop_product['shiprate'].'</td>';
				
				if($stkav=='1')
					$stkchk=' checked="checked"';
				else
					$stkchk='';
				
				echo '<td headers="stkavail sku'.$calt.'"><label for="stkavail'.$calt.'">'.__('Stock Available','eshop').'</label><input type="checkbox" value="1" name="product['.$getid.'][stkavail]" id="stkavail'.$calt.'"'.$stkchk.' /></td>';

				$purcharray=array();
				$dltable = $wpdb->prefix ."eshop_downloads";
				$stocktable=$wpdb->prefix ."eshop_stock";

				for($i=1;$i<=$eshopoptions['options_num'];$i++){
					if($eshop_product['products'][$i]['option']!=''){
						if(isset($eshop_product['products'][$i]['download']) && $eshop_product['products'][$i]['download']!=''){
							$fileid=$eshop_product['products'][$i]['download'];
							$purchases=$wpdb->get_var("SELECT purchases FROM $dltable WHERE id='$fileid'");
							if($purchases!='')
								$purcharray[]=$purchases;
							else
								$purcharray[]='0';
						}else{
							$purchases=$wpdb->get_var("select purchases from $stocktable where post_id=$getid && option_id=$i limit 1");
							if($purchases!='')
								$purcharray[]=$purchases;
							else
								$purcharray[]='0';
						}
					}
					if($pdownloads=='no') break;
				}

				//Featured Product
				if($eshop_product['featured']=='Yes')
					$fchk=' checked="checked"';
				else
					$fchk='';
				$feat='<label for="featured'.$calt.'">'.__('Featured Product','eshop').'</label><input type="checkbox" value="1" name="product['.$getid.'][featured]" id="featured'.$calt.'"'.$fchk.' />';
				echo '<td headers="purc sku'.$calt.'">'.implode("<br />",$purcharray).'</td>';
				echo '<td headers="ftrd sku'.$calt.'">'.$feat.'</td>';
				//Sale Product
				if(isset($eshop_product['sale']) && $eshop_product['sale']=='yes')
					$salechk=' checked="checked"';
				else
					$salechk='';
				$onsale='<label for="sale'.$calt.'">'.__('Product On Sale','eshop').'</label><input type="checkbox" value="1" name="product['.$getid.'][sale]" id="sale'.$calt.'"'.$salechk.' />';
				echo '<td headers="onsale sku'.$calt.'">'.$onsale.'</td>';
				echo '<td headers="opt sku'.$calt.'" class="optline">';
				for($i=1;$i<=$numoptions;$i++){
					if($eshop_product['products'][$i]['option']!=''){
						echo sprintf( __('%1$s @ %2$s%3$s','eshop'),stripslashes(esc_attr($eshop_product['products'][$i]['option'])), $currsymbol, number_format_i18n($eshop_product['products'][$i]['price'],__('2','eshop'))).'<br />';
					}
				}
				echo '</td>';
				if ( $etax['bands'] > 0 ) {
					echo '<td headers="tax sku'.$calt.'" class="optline">';
					for($i=1;$i<=$numoptions;$i++){
						if(isset($eshop_product['products'][$i]['tax']) && $eshop_product['products'][$i]['tax']!=''){
							$tzone=sprintf(__('Band %1$d','eshop'),$eshop_product['products'][$i]['tax']);
							$disptzone=apply_filters('eshop_rename_tax_zone',array());
							if(isset($disptzone[$eshop_product['products'][$i]['tax']]))
								$tzone=$disptzone[$eshop_product['products'][$i]['tax']];
							echo $tzone.'<br />';
						}else{
							echo '<br />';
						}
					}
					echo '</td>';
				}
				//reset the string to stop multiple boxes!
				$pravailable='';
				if($eshopoptions['stock_control']=='yes'){
					for($i=1;$i<=$numoptions;$i++){
						if($eshop_product['products'][$i]['option']!=''){
							$available=$wpdb->get_var("select available from $stocktable where post_id=$getid && option_id=$i limit 1");
							if($available=='')
								$available='0';
							if(is_numeric($available) && $eshopoptions['stock_control']=='yes'){
								$pravailable.='<label for="stock'.$calt.'">'.__('Stock','eshop').'</label><input type="text" value="'.$available.'" id="stock'.$scc.'" name="product['.$getid.']['.$i.'][stkqty]" size="4" /><br />'."\n";
								$scc++;
							}
						}
					}
				}else{
					$pravailable.=__('n/a','eshop').'<br />';
				}
				echo '<td headers="stk sku'.$calt.'">'.$pravailable.'</td>';
				echo '<td headers="associmg sku'.$calt.'">';
				$w=get_option('thumbnail_size_w');
				$h=get_option('thumbnail_size_h');
				$imgsize='50';
				$w=round(($w*$imgsize)/100);
				$h=round(($h*$imgsize)/100);
				if (has_post_thumbnail( $getid ) ) {
					 echo '<a class="itemref" href="'.get_permalink($getid).'" title="view page">'.get_the_post_thumbnail( $getid, array($w, $h)).'</a>'."\n";
				}else{
					$eimage=eshop_files_directory();
					$eshopnoimage=apply_filters('eshop_no_image',$eimage['1'].'noimage.png');
					 echo '<a class="itemref" href="'.get_permalink($getid).'" title="view page"><img src="'.$eshopnoimage.'" height="'.$h.'" width="'.$w.'" alt="" /></a>'."\n";
				}
				echo '</td>';
				echo '</tr>'."\n";
			}
		}

		?>
		</tbody>
		</table>
		</div>
		<p><input type="submit" name="eshopqp" id="submitit" class="submit button-primary" value="<?php _e('Update Products','eshop'); ?>" /></p>
		</form>
		<?php
		//paginate
		echo '<div class="paginate tablenav-pages stuffbox">';
			if($records!=$max){
				$eecho = $page_links;
			}else{
				--$records;
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
	}else{	
		echo '<p>'.__('There are no products available.','eshop').'</p>';
	}
	echo '</div>';
}
function eshop_authors($filter=''){
	global $wpdb;
	if ( !is_multisite() ) {
		$all_logins = $wpdb->get_results("SELECT ID, user_login FROM $wpdb->users ORDER BY user_login");
	} else {
		// WPMU only searches users of current blog
		$all_logins = $wpdb->get_results("SELECT ID, user_login FROM $wpdb->users, $wpdb->usermeta WHERE $wpdb->users.ID = $wpdb->usermeta.user_id AND meta_key = '".$wpdb->prefix."capabilities' ORDER BY user_login");
	}
	$selected=' selected="selected"';
	$sel='';
	if($filter=='all') $sel=$selected;
	$echo= '<option value="all"'.$sel.'>'.__('All','eshop').'</option>'."\n";
	$sel='';
	if($filter=='') $sel=$selected;
	foreach ($all_logins as $login) {
		$user_info = get_userdata($login->ID);
		$enic='';
		if($user_info->nickname!='' && $user_info->display_name!=$user_info->nickname) $enic='['.$user_info->nickname.']';
		$thisone='';
		if($filter!='' && $filter==$login->ID) $thisone=$selected;
		$echo.='<option value="'.$login->ID.'"'.$thisone.'>'.$user_info->display_name.$enic.'</option>'."\n";
	}
	return $echo;
}
?>