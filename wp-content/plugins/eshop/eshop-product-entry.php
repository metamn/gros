<?php
//make it available
add_action('admin_menu', 'eshop_add_custom_box');
/* Use the save_post action to do something with the data entered */
add_action('save_post', 'eshop_save_postdata');
add_action('admin_head-post.php', 'eshop_check_error'); // called after the redirect
/* Adds a custom section to the "advanced" Post and Page edit screens */
function eshop_add_custom_box() {
	if( function_exists( 'add_meta_box' )) {
  		get_currentuserinfo() ;
  		$array=array('post','page');
  		$array=apply_filters('eshop_post_types',$array);
		if(current_user_can('eShop')){
			foreach($array as $type){
    			add_meta_box( 'epagepostcustom', __( 'Product Entry', 'eshop' ), 
                'eshop_inner_custom_box', $type, 'normal','high' );
        	}
    	}
  	}
}
   
/* Prints the inner fields for the custom post/page section */
function eshop_inner_custom_box($post) {
    global $wpdb,$eshopoptions;
      // Use nonce for verification
    echo '<input type="hidden" name="eshop_noncename" id="eshop_noncename" value="' . 
    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
    // The actual fields for data entry
    $osets=array();
    if(isset($_REQUEST[ 'post' ])){
    	$stkav=get_post_meta( $_REQUEST[ 'post' ], '_eshop_stock',true );
    	$eshop_product=maybe_unserialize(get_post_meta( $_REQUEST[ 'post' ], '_eshop_product',true ));
    }else{
    	$stkav='';
    	$eshop_product=array();
    }
    if(isset($eshop_product['optset']))
		$osets=$eshop_product['optset'];
 
    //recheck stkqty
    $stocktable=$wpdb->prefix ."eshop_stock";
    if(isset($eshop_product['products'])){
		for ( $i = 1; $i<= count( $eshop_product['products']); $i++) {
			if ( isset( $eshop_product['products'][$i]['option'] ) && !empty( $eshop_product['products'][$i]['option'] ) ) {
				$eshop_product['products'][$i]['stkqty'] = $wpdb->get_var("SELECT available FROM $stocktable where post_id=$post->ID AND option_id=$i");
			}
		}
    }

    ?>
    <h4><?php _e('Product','eshop'); ?></h4>

    <p><label for="eshop_sku"><?php _e('Sku','eshop'); ?> </label><input id="eshop_sku" name="eshop_sku" value="<?php if (isset($eshop_product['sku'])) echo $eshop_product['sku']; ?>" type="text" size="20" /> <?php _e('(unique identification reference eg. abc001)','eshop'); ?></p>
    <p><label for="eshop_product_description"><?php _e('Product Description','eshop'); ?> </label><input id="eshop_product_description" name="eshop_product_description" value="<?php if (isset($eshop_product['description'])) echo $eshop_product['description']; ?>" type="text" size="30" /></p>
    <?php
    //get list of download products for selection 
    $producttable = $wpdb->prefix ."eshop_downloads";
    $myrowres=$wpdb->get_results("Select * From $producttable");
    //check for existence of downloads
    $eshopdlavail = $wpdb->get_var("SELECT COUNT(id) FROM $producttable WHERE id > 0");
    $numoptions=$eshopoptions['options_num'];
    if(isset($eshopoptions['etax']) && !isset($eshopoptions['etax']['bands']))
    	$eshopoptions['etax']['bands'] = 0;
    ?>
    <div class="eshopwidetable">
    <table class="hidealllabels widefat eshoppopt" summary="<?php _e('Product Options by option price and download','eshop'); ?>">
    <caption><?php _e('Product Options','eshop'); ?></caption>
    <thead><tr><th id="eshopnum">#</th><th id="eshopoption"><?php _e('Option','eshop'); ?></th>
    <th id="eshopprice"><?php _e('Price','eshop'); ?></th>
    <?php if(isset($eshopoptions['sale_prices']) && $eshopoptions['sale_prices'] == 1 ){?><th id="eshopsaleprice"><?php _e('Sale Price','eshop'); ?></th><?php } ?>
    <?php if(isset($eshopoptions['etax']) && $eshopoptions['etax']['bands']>'0'){?><th id="eshoptax"><?php _e('Tax','eshop'); ?></th><?php } ?>
    <?php if($eshopdlavail>0){ ?><th id="eshopdownload"><?php _e('Download','eshop'); ?></th><?php } ?>
    <?php if($eshopoptions['shipping']=='4'){?><th id="eshopweight"><?php _e('Weight','eshop'); ?></th><?php } ?>
    <?php if($eshopoptions['stock_control']=='yes'){?><th id="eshopstkqty"><?php _e('Stock','eshop'); ?></th><?php } ?>
    </tr></thead>
        <tbody>
        <?php
		for($i=1;$i<=$numoptions;$i++){
			if(isset($eshop_product['products']) && isset($eshop_product['products'][$i]) && is_array($eshop_product['products'][$i])){
				$opt=$eshop_product['products'][$i]['option'];
				$price=$eshop_product['products'][$i]['price'];
				$downl='';
				if(isset($eshop_product['products'][$i]['download']))
					$downl=$eshop_product['products'][$i]['download'];
				if(isset($eshop_product['products'][$i]['weight'])) 
					$weight=$eshop_product['products'][$i]['weight'];
				else
					$weight='';
				if(isset($eshop_product['products'][$i]['stkqty']) && $eshopoptions['stock_control']=='yes')
					$stkqty=$eshop_product['products'][$i]['stkqty'];
				else
					$stkqty='';
				if(isset($eshop_product['products'][$i]['tax'])) 
					$eshoptax=$eshop_product['products'][$i]['tax'];
				else
					$eshoptax='0';
				
				$saleprice = '';
				if(isset($eshop_product['products'][$i]['saleprice']))
					$saleprice = $eshop_product['products'][$i]['saleprice'];
			}else{
				$saleprice=$stkqty=$weight=$opt=$price=$downl='';
				$eshoptax='0';
			}
			$alt = ($i % 2) ? '' : ' class="alternate"';
			?>
			<tr<?php echo $alt; ?>>
			<th id="eshopnumrow<?php echo $i; ?>" headers="eshopnum"><?php echo $i; ?></th>
			<td headers="eshopoption eshopnumrow<?php echo $i; ?>"><label for="eshop_option_<?php echo $i; ?>"><?php _e('Option','eshop'); ?> <?php echo $i; ?></label><input id="eshop_option_<?php echo $i; ?>" name="eshop_option_<?php echo $i; ?>" value="<?php echo $opt; ?>" type="text" size="20" /></td>
			<td headers="eshopprice eshopnumrow<?php echo $i; ?>"><label for="eshop_price_<?php echo $i; ?>"><?php _e('Price','eshop'); ?> <?php echo $i; ?></label><input id="eshop_price_<?php echo $i; ?>" name="eshop_price_<?php echo $i; ?>" value="<?php echo $price; ?>" type="text" size="6" /></td>
			<?php 
			//saleprice
			if(isset($eshopoptions['sale_prices']) && $eshopoptions['sale_prices'] == 1 ){
			?>
			<td headers="eshopsaleprice eshopnumrow<?php echo $i; ?>"><label for="eshop_saleprice_<?php echo $i; ?>"><?php _e('Sale Price','eshop'); ?> <?php echo $i; ?></label><input id="eshop_saleprice_<?php echo $i; ?>" name="eshop_saleprice_<?php echo $i; ?>" value="<?php echo $saleprice; ?>" type="text" size="6" /></td>
			<?php
			}
			//tax
			if(isset($eshopoptions['etax']['bands']) && $eshopoptions['etax']['bands']>'0'){	?>
				<td headers="eshoptax eshopnumrow<?php echo $i; ?>">
					<label for="eshop_tax_<?php echo $i; ?>"><?php _e('Tax','eshop'); ?> <?php echo $i; ?></label>
					<select name="eshop_tax_<?php echo $i; ?>" id="eshop_tax_<?php echo $i; ?>">
						<option value=""><?php _e('No','eshop'); ?></option>
						<?php
						for($it=1;$it<=$eshopoptions['etax']['bands'];$it++){
							$tzone=sprintf(__('Band %1$d','eshop'),$it);
							$disptzone=apply_filters('eshop_rename_tax_zone',array());
							if(isset($disptzone[$it]))
								$tzone=$disptzone[$it];
							echo '<option value="'.$it.'"'.selected($it,$eshoptax).'>'.$tzone.'</option>'."\n";
						}
						?>
					</select>
				</td>
			<?php }	?>
			<?php if($eshopdlavail>0){ ?>
			<td headers="eshopdownload eshopnumrow<?php echo $i; ?>"><label for="eshop_download_<?php echo $i; ?>"><?php _e('Download','eshop'); ?> <?php echo $i; ?></label><select name="eshop_download_<?php echo $i; ?>" id="eshop_download_<?php echo $i; ?>">
			   <option value=""><?php _e('No (or select)','eshop'); ?></option>
				<?php
				foreach($myrowres as $prow){
					$checked = ( trim( $prow->id ) == trim( $downl ) ) ? ' selected="selected"' : '';
					echo '<option value="'.$prow->id.'"'.$checked.'>'.$prow->title.'</option>'."\n";
				}
				?>
				</select></td>
			<?php } ?>
			<?php if($eshopoptions['shipping']=='4'){//shipping by weight 
			?>
			<td headers="eshopweight eshopnumrow<?php echo $i; ?>"><label for="eshop_weight_<?php echo $i; ?>"><?php _e('Weight','eshop'); ?> <?php echo $i; ?></label><input id="eshop_weight_<?php echo $i; ?>" name="eshop_weight_<?php echo $i; ?>" value="<?php echo $weight; ?>" type="text" size="6" /></td>
			<?php 
			} 
			if($eshopoptions['stock_control']=='yes'){
			?>
			<td headers="eshopstkqty eshopnumrow<?php echo $i; ?>"><label for="eshop_stkqty_<?php echo $i; ?>"><?php _e('Stock','eshop'); ?> <?php echo $i; ?></label><input id="eshop_stkqty_<?php echo $i; ?>" name="eshop_stkqty_<?php echo $i; ?>" value="<?php echo $stkqty; ?>" type="text" size="6" /></td>
			<?php 
			} 
			?>
			</tr>
			<?php
		 }
    ?>
    </tbody>
	</table>
	</div>
	<?php
	$opttable=$wpdb->prefix.'eshop_option_names';
	$myrowres=$wpdb->get_results("select *	from $opttable ORDER BY name ASC");
	if(sizeof($myrowres)>0){
	?>
	<div id="eshoposetc">
	<h4><?php _e('Option Sets','eshop'); ?></h4>
	<div id="eshoposets">
	<ul>
	<?php
	$oi=1;
	if(!is_array($osets)) $osets=array();
	foreach($myrowres as $row){
		$displayname=$row->name;
		if(isset($row->admin_name) && $row->admin_name!='')
			$displayname=$row->admin_name;
	?>
		<li><input type="checkbox" name="eshoposets[]" id="osets<?php echo $oi; ?>" value="<?php echo $row->optid; ?>"<?php if(in_array($row->optid,$osets)) echo ' checked="checked"'; ?> /><label for="osets<?php echo $oi; ?>"><?php echo stripslashes(esc_attr($displayname))?></label></li>
	<?php
		$oi++;
	}
	?>
	</ul>
	</div>
	</div>
	<?php } ?>
	<div id="eshoposetsc">
    <h4><?php _e('Product Settings','eshop'); ?></h4>
    <?php
	if($eshopoptions['downloads_only'] !='yes' && $eshopoptions['shipping']!='4'){
		?>
		<p><label for="eshop_shipping_rate"><?php _e('Shipping Rate','eshop'); ?></label> <select name="eshop_shipping_rate" id="eshop_shipping_rate">
		<option value=""><?php _e('No (or select)','eshop'); ?></option>
		<?php
		if(isset($eshop_product['shiprate']) && $eshop_product['shiprate']!=''){
			$selected = $eshop_product['shiprate'];
		}else{
			$selected = '';
			$eshop_product['shiprate']='';
		}
		
		$shipcodes=array('A','B','C','D','E','F');
		$size = sizeof($shipcodes)-1;
		for($i=0;$i<=$size;$i++){
			$disshipclass=apply_filters('eshop_shipping_rate_class',$shipcodes[$i]);
			$checked = ( trim($shipcodes[$i]) == trim( $eshop_product['shiprate'] ) ) ? 'selected="selected"' : '';
			echo '<option value="'.$shipcodes[$i].'"'.$checked.'>'.$disshipclass."</option>\n";
		}
		?>
    </select></p>
    <?php
    }else{
	?>
		<input type="hidden" name="eshop_shipping_rate" value="F" />
	<?php
	}
	?>
    <p><input id="eshop_featured_product" name="eshop_featured_product" value="Yes"<?php echo isset($eshop_product['featured']) && $eshop_product['featured']=='Yes' ? 'checked="checked"' : ''; ?> type="checkbox" /> <label for="eshop_featured_product" class="selectit"><?php _e('Featured Product','eshop'); ?></label></p>
    <p><input id="eshop_sale_product" name="eshop_sale_product" value="yes"<?php echo isset($eshop_product['sale']) && $eshop_product['sale']=='yes' ? 'checked="checked"' : ''; ?> type="checkbox" /> <label for="eshop_sale_product" class="selectit"><?php _e('Product in sale','eshop'); ?></label></p>

    <p><input id="eshop_stock_available" name="eshop_stock_available" value="Yes"<?php echo $stkav=='1' ? 'checked="checked"' : ''; ?> type="checkbox" /> <label for="eshop_stock_available" class="selectit"><strong><?php _e('Stock Available','eshop'); ?></strong></label></p>
    <?php
    /*
    if($eshopoptions['stock_control']=='yes'){
    ?>
    <p><label for="eshop_stock_quantity"><?php _e('Stock Quantity','eshop'); ?></label> <input id="eshop_stock_quantity" name="eshop_stock_quantity" value="<?php if(isset($eshop_product['qty'])) echo $eshop_product['qty']; ?>" type="text" size="4" /></p>
    <?php
    }
    */
    ?>
    <h4><?php _e('Form Settings','eshop'); ?></h4>
    <p><label for="eshop_cart_radio"><?php _e('Show Options as','eshop'); ?></label> 
    <select name="eshop_cart_radio" id="eshop_cart_radio">
    	<option value="0"<?php if(isset($eshop_product['cart_radio']) && $eshop_product['cart_radio']=='0') echo ' selected="selected"'; ?>><?php _e('Dropdown Select','eshop'); ?></option>
		<option value="1"<?php if(isset($eshop_product['cart_radio']) && $eshop_product['cart_radio']=='1') echo ' selected="selected"'; ?>><?php _e('Radio Buttons','eshop'); ?></option>
    </select></p>
    <?php
	echo '</div><div class="clear"></div>';

}

/* When the post is saved, saves our custom data */
function eshop_save_postdata( $post_id ) {
	global $wpdb,$eshopoptions;
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if (!isset($_POST['eshop_noncename'])){
		return $post_id;
	}
	if ( !wp_verify_nonce( $_POST['eshop_noncename'], plugin_basename(__FILE__) )) {
		return $post_id;
	}

	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ))
	  		return $post_id;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ))
	  		return $post_id;
	}
  
	if( !isset( $id ) )
		$id = $post_id;
  // OK, we're authenticated: we need to find and save the data
	$stkav=get_post_meta( $post_id, '_eshop_stock',true );
    $eshop_product=maybe_unserialize(get_post_meta( $post_id, '_eshop_product',true ));
	
	$eshop_product['sku']=htmlspecialchars($_POST['eshop_sku']);
	$numoptions=$eshopoptions['options_num'];
	for($i=1;$i<=$numoptions;$i++){
		$eshop_product['products'][$i]['option']=htmlspecialchars($_POST['eshop_option_'.$i]);
		if($_POST['eshop_price_'.$i]=='0') $_POST['eshop_price_'.$i]='0.00';
		$eshop_product['products'][$i]['price']=$_POST['eshop_price_'.$i];
		if(!is_numeric($_POST['eshop_price_'.$i]) && $_POST['eshop_price_'.$i]!=''){
			add_filter('redirect_post_location','eshop_price_error');
		}
		if(isset($_POST['eshop_download_'.$i])){
			$eshop_product['products'][$i]['download']=$thisdl=$_POST['eshop_download_'.$i];
		}
		if(isset($_POST['eshop_tax_'.$i])){
			$eshop_product['products'][$i]['tax']=$_POST['eshop_tax_'.$i];
		}
		if(isset($_POST['eshop_weight_'.$i])){
			$eshop_product['products'][$i]['weight']=$_POST['eshop_weight_'.$i];
			if(!is_numeric($_POST['eshop_weight_'.$i]) && $_POST['eshop_weight_'.$i]!=''){
				add_filter('redirect_post_location','eshop_weight_error');
			}
		}
		if(isset($_POST['eshop_stkqty_'.$i])){
			$eshop_product['products'][$i]['stkqty']=$_POST['eshop_stkqty_'.$i];
			if(!is_numeric($_POST['eshop_stkqty_'.$i]) && $_POST['eshop_stkqty_'.$i]!=''){
				add_filter('redirect_post_location','eshop_stkqty_error');
			}
		}
		if(!isset($_POST['eshop_saleprice_'.$i]) || $_POST['eshop_saleprice_'.$i]==''){
			$eshop_product['products'][$i]['saleprice'] = '';
		}elseif(!is_numeric($_POST['eshop_saleprice_'.$i])){
			add_filter('redirect_post_location','eshop_saleprice_error');
		}else{
			$eshop_product['products'][$i]['saleprice'] = $_POST['eshop_saleprice_'.$i];
		}
	}
	$eshop_product['description']=htmlspecialchars($_POST['eshop_product_description']);
	$eshop_product['shiprate']=$_POST['eshop_shipping_rate'];
	if($eshop_product['shiprate']=='') $mydata['_Shipping Rate']='F';
	if(isset($_POST['eshop_featured_product'])){
		$eshop_product['featured']='Yes';
		update_post_meta( $id, '_eshop_featured', 'Yes');
	}else{
		$eshop_product['featured']='no';
		delete_post_meta( $id, '_eshop_featured');
	}
	if(isset($_POST['eshop_sale_product'])){
		$eshop_product['sale']='yes';
		update_post_meta( $id, '_eshop_sale', 'yes');
	}else{
		$eshop_product['sale']='no';
		delete_post_meta( $id, '_eshop_sale');
	}
	
	
	
	if(isset($_POST['eshop_stock_available']))
		$stkav='1';
	else
		$stkav='0';
		
	$stocktable=$wpdb->prefix ."eshop_stock";
	//test stk control per option
	for($i=1;$i<=$numoptions;$i++){
		if(isset($eshop_product['products'][$i]['stkqty']) && $eshop_product['products'][$i]['stkqty']!='' && is_numeric($eshop_product['products'][$i]['stkqty'])){
			$stkv=$eshop_product['products'][$i]['stkqty'];
			// Clicking update appears to trigger this function twice (once to create a revision I think, and once to save).  Upshot is that we can't rely on the $post_id variable so...
			$pid = $_POST['post_ID'];
			$sql = "select post_id from $stocktable WHERE post_id=$pid AND option_id=$i";
			$result=$wpdb->get_results($sql);
			if( !empty( $result ) ){
				$sql = "UPDATE $stocktable set available=$stkv where post_id=$pid AND option_id=$i";
				$wpdb->query($wpdb->prepare($sql));
			} else {
				$sql = "INSERT INTO $stocktable (post_id,option_id,available,purchases) VALUES ($pid,$i,$stkv,0)";
				$wpdb->query($wpdb->prepare($sql));
			}
		}
	}
	
	//form setup
	$eshop_product['cart_radio']=$_POST['eshop_cart_radio'];
	//option sets
	if(isset($_POST['eshoposets'])){
		$eshop_product['optset']=$_POST['eshoposets'];
	}else{
		$eshop_product['optset']='';
	}
	update_post_meta( $id, '_eshop_product', $eshop_product);
	if($stkav=='0')
		delete_post_meta( $id, '_eshop_stock');
	else
		update_post_meta( $id, '_eshop_stock', $stkav);

		
	if($stkav=='1' && ($eshop_product['sku']=='' || $eshop_product['description']=='' || $eshop_product['products']['1']['option']=='' || $eshop_product['products']['1']['price']=='')){
		delete_post_meta( $id, '_eshop_stock');
		add_filter('redirect_post_location','eshop_error');
	}
	if($stkav=='0' && $eshop_product['sku']=='' && $eshop_product['description']=='' && $eshop_product['products']['1']['option']=='' && $eshop_product['products']['1']['price']==''){
	//not a product
		delete_post_meta( $id, '_eshop_stock');
		delete_post_meta( $id, '_eshop_product');
	}
	return;
}
?>