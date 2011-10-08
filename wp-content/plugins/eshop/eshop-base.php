<?php
if ('eshop-base.php' == basename($_SERVER['SCRIPT_FILENAME']))
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

function eshop_base_manager() {
	global $wpdb,$eshopoptions;
	include 'eshop-base-functions.php';

if(!isset($_GET['change'])){
	?>
	<div class="wrap">
	<div id="eshopicon" class="icon32"></div><h2><?php _e('eShop Base Products','eshop'); ?></h2>
	<?php eshop_admin_mode(); ?>
	<ul>
	<?php
	$dlpage='?page='.$_GET['page'].'&amp;eshopbasedl=yes';
	?>
	<li><a href="<?php echo $dlpage; ?>&amp;d=1"><?php _e('Download the xml file','eshop'); ?></a></li>
	<li><a href="<?php echo $dlpage; ?>&amp;d=1&amp;os=mac"><?php _e('Mac - Download the xml file','eshop'); ?></a></li>
	<li><a href="<?php echo $dlpage; ?>"><?php _e('View xml file','eshop'); ?></a></li>
	<li><?php echo sprintf( __('Direct link to file for Google Base: <a href="%1$s">%1$s</a>','eshop'), trim(site_url(),'/').'/?eshopbasefeed' ); ?></li>
	</ul>
	<?php	
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
			case'sd'://stock availability 
				$sortby='_eshop_stock';
				$csd=' class="current"';
				break;
		*/
			case'sf'://date ascending
			default:
				$sortby='id';
				$csf=' class="current"';;
		}
	}else{
		$csf=' class="current"';
		$sortby='id';
	}
	
	
	$numoptions=$eshopoptions['options_num'];
	$metatable=$wpdb->prefix.'postmeta';
	$poststable=$wpdb->prefix.'posts';
	$stocktable=$wpdb->prefix ."eshop_stock";
	$range=10;
	$max = $wpdb->get_var("SELECT COUNT(meta.post_id) FROM $metatable as meta, 
	$poststable as posts where meta.meta_key='_eshop_product' 
	AND posts.ID = meta.post_id	
	AND posts.post_status != 'trash' AND posts.post_status != 'revision'");
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
		$apge=get_admin_url().'admin.php?page=eshop-base.php';
		echo '<ul id="eshopsubmenu" class="stuffbox">';
		echo '<li><span>'.__('Sort Orders by &raquo;','eshop').'</span></li>';
		echo '<li><a href="'.$apge.'&amp;by=sf"'.$csf.'>'.__('ID Number','eshop').'</a></li>';
		echo '<li><a href="'.$apge.'&amp;by=sa"'.$csa.'>'.__('Sku','eshop').'</a></li>';
		echo '<li><a href="'.$apge.'&amp;by=sb"'.$csb.'>'.__('Product','eshop').'</a></li>';
		// echo '<li><a href="'.$apge.'&amp;by=sd"'.$csd.'>'.__('Stock','eshop').'</a></li>';
		echo '</ul>';
		
		$myrowres=$wpdb->get_results("
		SELECT DISTINCT meta.post_id
		FROM $metatable as meta, $poststable as posts
		WHERE meta.meta_key = '_eshop_product'
		AND posts.ID = meta.post_id
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
	?>	
		<table class="widefat" summary="<?php _e('product listing','eshop'); ?>">
		<caption><?php _e('Product Quick reference table','eshop'); ?></caption>
		<thead>
		<tr>
		<th id="sku"><?php _e('Sku','eshop'); ?></th>
		<th id="page"><?php _e('Page','eshop'); ?></th>
		<th id="desc"><?php _e('Description','eshop'); ?></th>
		<th id="down"><?php _e('Download','eshop'); ?></th>
		<th id="stkavail"><abbr title="<?php _e('Stock Available','eshop'); ?>"><?php _e('Stk avail.','eshop'); ?></abbr></th>
		<th id="stk"><?php _e('Stock','eshop'); ?></th>
		<th id="opt"><?php _e('Option/Price','eshop'); ?></th>
		<th id="imga"><?php _e('Image','eshop'); ?></th>

		</tr>
		</thead>
		<tbody>
		<?php
		$start =($epage * $records)-($records);
		$grab=array_slice($grab,$start,$records);
		foreach($grab as $foo=>$grabit){
			$eshop_product=$grabit;
			if(isset($grabit['_eshop_stock']))
				$stkav=$grabit['_eshop_stock'];
			else
				$stkav=0;
			if(isset($eshop_product['products']['1']['price']) && $eshop_product['products']['1']['price']!=''){
				//get page title
				$ptitle=get_post($grabit['id']);
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
					$posttitle=__('(no title)','eshop');
				else
					$posttitle=$ptitle->post_title;
				$calt++;
				$alt = ($calt % 2) ? '' : ' class="alternate"';
				echo '<tr'.$alt.'>';
				echo '<td id="sku'.$calt.'" headers="sku"><a href="admin.php?page=eshop-base.php&amp;change='.$grabit['id'].'" title="'.__('Change details','eshop').'">'.$eshop_product['sku'].'</a></td>';
				echo '<td headers="page sku'.$calt.'"><a href="post.php?action=edit&amp;post='.$grabit['id'].'">'.$posttitle.'</a></td>';
				echo '<td headers="desc sku'.$calt.'">'.stripslashes(esc_attr($eshop_product['description'])).'</td>';
				echo '<td headers="down sku'.$calt.'">'.$pdown.'</td>';
				if($stkav=='1')
					$stkchk=__('Yes','eshop');
				else
					$stkchk=__('No','eshop');

				echo '<td headers="stkavail sku'.$calt.'">'.$stkchk.'</td>';

				$pid=$grabit['id'];
				if($eshopoptions['stock_control']=='yes'){
					$pravailable='';
					$getid=$grabit['id'];
					for($i=1;$i<=$numoptions;$i++){
						if($eshop_product['products'][$i]['option']!=''){
							$available=$wpdb->get_var("select available from $stocktable where post_id=$getid && option_id=$i limit 1");
							if($available=='' || $available<0)
								$available='0';
							if(is_numeric($available) && $eshopoptions['stock_control']=='yes'){
								$pravailable.=$available.'<br />'."\n";
							}
						}
					}
				}else{
					$pravailable=__('n/a','eshop').'<br />';
				}
				echo '<td headers="stk sku'.$calt.'">'.$pravailable.'</td>';
				
				echo '<td headers="opt sku'.$calt.'">';
				for($i=1;$i<=$numoptions;$i++){
					if($eshop_product['products'][$i]['option']!=''){
						echo sprintf( __('%1$s @ %2$s%3$s','eshop'),stripslashes(esc_attr($eshop_product['products'][$i]['option'])), $currsymbol, number_format_i18n($eshop_product['products'][$i]['price'],__('2','eshop'))).'<br />';
					}
				}
				echo '</td>';
				echo '<td>';
				$getid=$grabit['id'];
				$w=get_option('thumbnail_size_w');
				$h=get_option('thumbnail_size_h');
				$imgsize='50';
				$w=round(($w*$imgsize)/100);
				$h=round(($h*$imgsize)/100);
				if (has_post_thumbnail( $getid ) ) {
					 echo '<a class="itemref" href="'.get_permalink($getid).'">'.get_the_post_thumbnail( $getid, array($w, $h)).'</a>'."\n";
				}else{
					$eimage=eshop_files_directory();
					$eshopnoimage=apply_filters('eshop_no_image',$eimage['1'].'noimage.png');
					 echo '<a class="itemref" href="'.get_permalink($getid).'"><img src="'.$eshopnoimage.'" height="'.$h.'" width="'.$w.'" alt="" /></a>'."\n";
				}
				echo '</td>';
				echo '</tr>';
			}
		}
		?>
		</tbody>
		</table>
		<?php
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
	}else{	
		echo '<p>'.__('There are no products available.','eshop').'</p>';
	}
	echo '</div>';
}else{
//////////change one.
//form checks:
	$basetable=$wpdb->prefix ."eshop_base_products";
	$change=$_GET['change'];
	if(is_numeric($change)){
		if(isset($_POST['submit'])){
			include 'cart-functions.php';
			if (get_magic_quotes_gpc()==0) {
				$_POST = stripslashes_array($_POST);
			}
			$_POST=sanitise_array($_POST);
			$err='';
			if(isset($_POST['baseimg'])){
				$baseimg=$wpdb->escape($_POST['baseimg']);
			}else{
				$baseimg='';
			}
			$basebrand=$wpdb->escape($_POST['basebrand']);
			$baseean=$wpdb->escape($_POST['baseean']);
			$baseisbn=$wpdb->escape($_POST['baseisbn']);
			$basempn=$wpdb->escape($_POST['basempn']);
			$baseptype=$wpdb->escape($_POST['baseptype']);
			$baseqty=$wpdb->escape($_POST['baseqty']);
			$basecondition=$wpdb->escape($_POST['basecondition']);
			$baseexpiration_year=$_POST['baseexpiration_year'];
			$baseexpiration_month=$_POST['baseexpiration_month'];
			$baseexpiration_day=$_POST['baseexpiration_day'];

			if(!is_numeric($baseqty)){
				$baseqty='25';
				$err.='<li>'.__('Quantity was not numeric, a default of 25 has been applied.','eshop').'</li>';
			}
			$baseexpiration=$wpdb->escape($baseexpiration_year.'-'.$baseexpiration_month.'-'.$baseexpiration_day);

			//enter in db - delete old record first, 
			//then it will always be an insert and easier than checking for update.
			$wpdb->query("DELETE FROM $basetable WHERE post_id = $change limit 1");
			$wpdb->query("INSERT INTO $basetable (
			post_id,img,brand,ptype,thecondition,expiry,ean,isbn,mpn,qty
			)VALUES(
			'$change','$baseimg','$basebrand','$baseptype','$basecondition','$baseexpiration',
			'$baseean','$baseisbn','$basempn','$baseqty'
			)");

			if($err!=''){
				echo'<div id="message" class="error fade"><p>'.__('<strong>Error</strong> the following were not valid:','eshop').'</p><ul>'.$err.'</ul></div>'."\n";
			}else{
				echo'<div id="message" class="updated fade"><p>'.__('eshop Base details for this product have been updated.','eshop').'</p></div>'."\n";
			}
		}
		$basedata=$wpdb->get_row("SELECT * FROM $basetable WHERE post_id = $change");
		
		if($basedata==''){
				$basedata->post_id=$basedata->img=$basedata->brand=$basedata->ptype=$basedata->thecondition=$basedata->expiry=$basedata->ean=$basedata->isbn=$basedata->mpn=$basedata->qty='';
		}
		
	?>
		<div class="wrap">
		<div id="eshopicon" class="icon32"></div><h2><?php _e('eShop Base Product','eshop'); ?></h2>
		<?php eshop_admin_mode(); ?>

		<?php
		//sort by switch statement
		$sortby='id';
		$csf=' class="current"';

		$numoptions=$eshopoptions['options_num'];
		$metatable=$wpdb->prefix.'postmeta';

		$calt=0;
		$currsymbol=$eshopoptions['currency_symbol'];
		$x=0;
		//add in post id( doh! )
		$grabit[$x]=get_post_custom($change);
		$grabit[$x]['id']=array($change);
		$x++;
		/*
		* remove the bottom array to try and flatten
		* could be rather slow, but easier than trying to create
		* a different method, at least for now!
		*/
		foreach($grabit as $foo=>$k){
			foreach($k as $bar=>$v){
				if($bar=='_eshop_product'){
					$y=unserialize($v[0]);
					foreach($y as $nowt=>$val){
						$grab[$foo][$nowt]=$val;
					}
				}
				foreach($v as $nowt=>$val){
					$grab[$foo][$bar]=$val;
				}
			}
		}
		?>	
		<table class="widefat" summary="<?php _e('product listing','eshop'); ?>">
		<caption><?php _e('Product Quick reference table','eshop'); ?></caption>
		<thead>
		<tr>
		<th id="sku"><?php _e('Sku','eshop'); ?></th>
		<th id="page"><?php _e('Page','eshop'); ?></th>
		<th id="desc"><?php _e('Description','eshop'); ?></th>
		<th id="down"><?php _e('Download','eshop'); ?></th>
		<th id="stk"><?php _e('Stock','eshop'); ?></th>
		<th id="opt"><?php _e('Option/Price','eshop'); ?></th>
		<th id="imga"><?php _e('Image','eshop'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach($grab as $foo=>$grabit){
			$pdownloads='no';
			if($grabit['products']['1']['price']!=''){
				if(isset($grabit['_eshop_stock']))
					$stkav=$grabit['_eshop_stock'];
				else
					$stkav=0;
				//get page title
				$ptitle=get_post($grabit['id']);
				$pdown='';
				//check if downloadable product
				for($i=1;$i<=$eshopoptions['options_num'];$i++){
					if(isset($grabit['products'][$i]['option']) && $grabit['products'][$i]['option']!=''){
						if(isset($grabit['products'][$i]['download']) && $grabit['products'][$i]['download']!=''){
							$dltable=$wpdb->prefix.'eshop_downloads';
							$fileid=$grabit['products'][$i]['download'];
							$filetitle=$wpdb->get_var("SELECT title FROM $dltable WHERE id='$fileid'");;
							$pdown.='<a href="admin.php?page=eshop-downloads.php&amp;edit='.$fileid.'">'.$filetitle.'</a>';
							$pdownloads='yes';
						}else{
							$pdown.='<br />';
						}
					}
				}
				$calt++;
				$alt = ($calt % 2) ? '' : ' class="alternate"';
				echo '<tr'.$alt.'>';
				echo '<td id="sku'.$calt.'" headers="sku">'.$grabit['sku'].'</td>';
				echo '<td headers="page sku'.$calt.'"><a href="page.php?action=edit&amp;post='.$grabit['id'].'">'.$ptitle->post_title.'</a></td>';
				echo '<td headers="desc sku'.$calt.'">'.stripslashes(esc_attr($grabit['description'])).'</td>';
				echo '<td headers="down sku'.$calt.'">'.$pdown.'</td>';
				$pid=$grabit['id'];
				$stocktable=$wpdb->prefix ."eshop_stock";

				$pravailable='';
				if($eshopoptions['stock_control']=='yes'){
					for($i=1;$i<=$numoptions;$i++){
						if($grabit['products'][$i]['option']!=''){
							$available=$wpdb->get_var("select available from $stocktable where post_id=$pid && option_id=$i limit 1");
							if($available=='' || $available<0)
								$available='0';
							if(is_numeric($available) && $eshopoptions['stock_control']=='yes'){
								$pravailable.=$available.'<br />'."\n";
							}
						}
					}
				}else{
					$pravailable.=__('n/a','eshop').'<br />';
				}
				echo '<td headers="stk sku'.$calt.'">'.$pravailable.'</td>';

				echo '<td headers="opt sku'.$calt.'">';
				for($i=1;$i<=$numoptions;$i++){
					if(isset($grabit['products'][$i]['option']) && $grabit['products'][$i]['option']!=''){
						echo sprintf( __('%1$s @ %2$s%3$s','eshop'),stripslashes(esc_attr($grabit['products'][$i]['option'])), $currsymbol, number_format_i18n($grabit['products'][$i]['price'],__('2','eshop'))).'<br />';
					}
				}
				echo '</td>';


				echo '<td>';

				$w=get_option('thumbnail_size_w');
				$h=get_option('thumbnail_size_h');
				$imgsize='50';
				if($imgsize!=''){
					$w=round(($w*$imgsize)/100);
					$h=round(($h*$imgsize)/100);
				}
				if (has_post_thumbnail( $change ) ) {
					 echo '<a class="itemref" href="'.get_permalink($change).'">'.get_the_post_thumbnail( $change, array($w, $h)).'</a>'."\n";
				}else{
					$eimage=eshop_files_directory();
					$eshopnoimage=apply_filters('eshop_no_image',$eimage['1'].'noimage.png');
					 echo '<a class="itemref" href="'.get_permalink($change).'"><img src="'.$eshopnoimage.'" height="'.$h.'" width="'.$w.'" alt="" /></a>'."\n";
				}
				echo '</td>'."\n";


				echo '</tr>'."\n";
			}


			?>
		</tbody>
		</table>
		<?php
		}
		echo '<h3>'.__('Additional settings','eshop').'</h3>'."\n";

		$id=$grabit['id'];
		?>
		<form method="post" action="" id="eshop-gbase-alt">
		<fieldset id="baseothers"><legend><?php _e('Others','eshop'); ?></legend>
		<?php
		if (has_post_thumbnail( $id ) ) {
		$grabimg=get_post_thumbnail_id($id);
		$src=wp_get_attachment_image_src($grabimg);
		?>
		<input type="hidden" name="baseimg" value="<?php echo $src['0']; ?>" />
		<?php
		}
		?>
		<label for="basebrand"><?php _e('Brand <small>The brand name of the product</small>','eshop'); ?></label>
		<input type="text" name="basebrand" id="basebrand" value="<?php echo esc_html($basedata->brand); ?>" />
		<label for="baseean"><?php _e('EAN <small>European Article Number is a 13 digit number often below the bar code of the item.</small>','eshop'); ?></label>
		<input type="text" name="baseean" id="baseean" value="<?php echo esc_html($basedata->ean); ?>" />
		<label for="baseisbn"><?php _e('ISBN <small>The unique 10- or 13-digit number assigned to every printed book.</small>','eshop'); ?></label>
		<input type="text" name="baseisbn" id="baseisbn" value="<?php echo esc_html($basedata->isbn); ?>" />
		<label for="basempn"><?php _e('MPN <small>Manufacturer\'s Part Number is a unique code determined by the manufacturer for that product.</small>','eshop'); ?></label>
		<input type="text" name="basempn" id="basempn" value="<?php echo esc_html($basedata->mpn); ?>" />
		<label for="baseptype"><?php _e('Product type <small>The type of product being offered.</small>','eshop'); ?></label>
		<input type="text" name="baseptype" id="baseptype" value="<?php echo esc_html($basedata->ptype); ?>" />
		<label for="baseqty"><?php _e('Quantity','eshop'); ?></label>
		<input type="text" name="baseqty" id="baseqty" value="<?php echo esc_html($basedata->qty); ?>" />
	  <label for="basecondition"><?php _e('Condition <small>the condition of this product</small>','eshop'); ?></label>
	  <select name="basecondition" id="basecondition">
		<?php
		//'
		foreach($currentconditions as $code){
			if($basedata->thecondition==''){
				if($code == $eshopoptions['base_condition']){
					$sel=' selected="selected"';
				}else{
					$sel='';
				}
			}elseif($code==$eshopoptions['base_condition']){
				$sel=' selected="selected"';
			}else{
				$sel='';
			}
			echo '<option value="'. $code .'"'. $sel .'>'. $code .'</option>'."\n";
		}

		?>
	  </select>
	  <fieldset><legend><?php _e('Expiration date <small>(or how long a product will be available.)</small>','eshop'); ?></legend>
	  <label for="baseexpiration_year"><?php _e('Year','eshop'); ?></label>
	  <select name="baseexpiration_year" id="baseexpiration_year">
		<?php
		// work this out!!!
		if($basedata->expiry==''){
			$baseexpiry=$eshopoptions['base_expiry'];
			$basedate=date('Y-m-d',mktime(0, 0, 0, date("m") , date("d")+$baseexpiry, date("Y")));
			list($baseexpiration_year, $baseexpiration_month, $baseexpiration_day) = explode('-', $basedate);
		}else{
			list($baseexpiration_year, $baseexpiration_month, $baseexpiration_day) = explode('-', $basedata->expiry);
		}
		
		for($i=date('Y');$i<=date('Y')+5;$i++){
			if($i==$baseexpiration_year){
				$sel=' selected="selected"';
			}else{
				$sel='';
			}
			echo '<option value="'.$i.'"'.$sel.'>'.$i.'</option>'."\n";
		}
		?>
	  </select>
		<label for="baseexpiration_month"><?php _e('Month','eshop'); ?></label>

		  <select name="baseexpiration_month" id="baseexpiration_month">
		<?php

		for($i=1;$i<=12;$i++){
			if($i==$baseexpiration_month){
				$sel=' selected="selected"';
			}else{
				$sel='';
			}
			echo '<option value="'.$i.'"'.$sel.'>'.$i.'</option>'."\n";
		}
		?>
	  </select>
		<label for="baseexpiration_day"><?php _e('Day','eshop'); ?></label>

		  <select name="baseexpiration_day" id="baseexpiration_day">
		<?php

		for($i=1;$i<=31;$i++){
			if($i==$baseexpiration_day){
				$sel=' selected="selected"';
			}else{
				$sel='';
			}
			echo '<option value="'.$i.'"'.$sel.'>'.$i.'</option>'."\n";
		}
		?>
	  </select>
	  </fieldset>
	  </fieldset>
	  <p class="submit">
	  <input type="submit" name="submit" class="button-primary" value="<?php _e('Update') ?>" />
	</p>
		<?php


		echo '</form></div>';
	}else{
	?>
	<div class="wrap">
	<div id="eshopicon" class="icon32"></div><h2><?php _e('Error','eshop'); ?></h2>
	<p><?php _e('That product does not exist!','eshop'); ?></p>
	</div>
	<?php
	}
}
}
?>