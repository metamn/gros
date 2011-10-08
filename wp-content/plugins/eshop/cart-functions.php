<?php
if ('cart-functions.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');

if (!function_exists('display_cart')) {
	function display_cart($shopcart, $change, $eshopcheckout,$pzone='',$shiparray=''){
		//The cart display.
		global $wpdb, $blog_id,$eshopoptions;
		if(!isset($_SESSION['shipping'.$blog_id]) || !is_array($_SESSION['shipping'.$blog_id])) $_SESSION['shipping'.$blog_id]=array();
		if($pzone=='widget'){
			$pzone='';
			$iswidget='w';
		}else{
			$iswidget='';
		}
		$echo ='';
		$check=0;
		$sub_total=0;
		$tempshiparray=array();
		//this checks for an empty cart, may not be required but leaving in just in case.
		$eshopcartarray=$_SESSION['eshopcart'.$blog_id];
		
		if($change==true){
			if(isset($_SESSION['eshop_discount'.$blog_id]))
				unset($_SESSION['eshop_discount'.$blog_id]);
		}
		
		foreach ($eshopcartarray as $productid => $opt){
			if(is_array($opt)){
				foreach($opt as $qty){
					$check=$check+$qty;
				}
			}
		}
		//therefore if cart exists and has products
		if($check > 0){
			//global $final_price, $sub_total;
			// no fieldset/legend added - do we need it?
			if ($change == 'true'){
				$echo.= '<form action="'.get_permalink($eshopoptions['cart']).'" method="post" id="eshopcartform" class="eshop eshopcart">';
			}
			$echo.= '<table class="eshop cart" summary="'.__('Shopping cart contents overview','eshop').'">
			<caption>'.__('Shopping Cart','eshop').'</caption>
			<thead>
			<tr class="thead">';
			$echo .='<th id="cartItem'.$iswidget.'" class="nb">'.__('Item Description','eshop').'</th>
			<th id="cartQty'.$iswidget.'" class="bt">'.__('<abbr title="Quantity">Qty</abbr>','eshop').'</th>';
			
			$totalstring = __('Sub-Total','eshop');
			
			$echo .= '<th id="cartTotal'.$iswidget.'" class="btbr">'.$totalstring.'</th>';
			$etax = $eshopoptions['etax'];
			if(($pzone!='' && isset($eshopoptions['tax']) && $eshopoptions['tax']=='1')|| ('yes' == $eshopoptions['downloads_only'] && isset($etax['unknown']) && $etax['unknown']!='')){
				$echo .= '<th id="carttax" class="bt">'.__('Tax %','eshop').'</th>
				<th id="carttaxamt" class="btbr">'.__('Tax Amt','eshop').'</th>';
			}
			if($iswidget=='' && $change == 'true'){
				$eshopdeleteheaderimage=apply_filters('eshop_delete_header_image',WP_PLUGIN_URL.'/eshop/no.png');
				$echo.= '<th id="cartDelete" class="btbr"><img src="'.$eshopdeleteheaderimage.'" alt="'.__('Delete','eshop').'" title="'.__('Delete','eshop').'" /></th>';
			}
			$echo .= '</tr></thead><tbody>';
			//display each item as a table row
			$calt=0;
			$shipping=0;
			$totalweight=0;
			$taxtotal=0;
			$currsymbol=$eshopoptions['currency_symbol'];
			$eshopcartarray=$_SESSION['eshopcart'.$blog_id];
			foreach ($eshopcartarray as $productid => $opt){
				$addoprice=0;
				if(is_array($opt)){
					$key=$opt['option'];
					$calt++;
					$alt = ($calt % 2) ? '' : ' class="alt"';
					$echo.= "\n<tr".$alt.">";
					//do the math for weight
					$eshop_product=maybe_unserialize(get_post_meta( $opt['postid'], '_eshop_product',true ));
					$eimg='';
					/* image insertion */
					if( is_numeric($eshopoptions['image_in_cart']) || (isset($eshopoptions['widget_cart_type']) && $eshopoptions['widget_cart_type']<='1' && $iswidget=='w' ) ){
						$imgsize=$eshopoptions['image_in_cart'];
						if($iswidget=='w'){
							$imgsize=$eshopoptions['widget_cart_img'];
							if($imgsize=='') $imgsize=100;
						}
						$w=get_option('thumbnail_size_w');
						$h=get_option('thumbnail_size_h');
						if($imgsize!=''){
							$w=round(($w*$imgsize)/100);
							$h=round(($h*$imgsize)/100);
						}
						if (has_post_thumbnail( $opt['postid'] ) ) {
							$eimg='<a class="itemref" href="'.get_permalink($opt['postid']).'">'.get_the_post_thumbnail( $opt['postid'], array($w, $h)).'</a>'."\n";
						}else{
							$eimage=eshop_files_directory();
							$eshopnoimage=apply_filters('eshop_no_image',$eimage['1'].'noimage.png');
							$eimg='<a class="itemref" href="'.get_permalink($opt['postid']).'"><img src="'.$eshopnoimage.'" height="'.$h.'" width="'.$w.'" alt="" /></a>'."\n";
						}
					}
					/* end */
					//opsets

					if(isset($opt['optset'])){
						$data['optset']=$opt['optset'];
						$data['addoprice']=$addoprice;
						$data=eshop_parse_optsets($data);
						$optset='<span class="eshopoptsets">'.$data['optset'].'</span>';
						$addoprice=$data['addoprice'];
					}else{
						$optset='';
					}
					$echooptset=apply_filters('eshop_optset_cart_display',$optset);
					if( !has_filter( 'eshop_optset_cart_display') ) $echooptset=nl2br($optset);
					$textdesc='<a href="'.get_permalink($opt['postid']).'">'.stripslashes($opt["pname"]).' <span class="eshopidetails">('.$opt['pid'].' : '.stripslashes($opt['item']).')</span></a>'.$echooptset;
					$echoimg=$eimg;
					if(isset($eshopoptions['widget_cart_type']) && $eshopoptions['widget_cart_type']=='1' && $iswidget=='w'  ){
						$textdesc='';
					}
					if($iswidget=='w' && isset($eshopoptions['widget_cart_type']) && $eshopoptions['widget_cart_type']=='2'){
						$echoimg='';
					}
					$echo.= '<td id="prod'.$calt.$iswidget.'" headers="cartItem'.$iswidget.'" class="leftb cartitem">'.$echoimg.$textdesc.'</td>'."\n";

					$echo.= "<td class=\"cqty lb\" headers=\"cartQty$iswidget prod".$calt.$iswidget."\">";
					// if we allow changes, quantities are in text boxes
					if ($change == true){
						//generate acceptable id
						//$toreplace=array(" ","-","$","\r","\r\n","\n","\\","&","#",";");
						$accid=$productid.$key;
						$accid='c'.md5($accid);//str_replace($toreplace, "", $accid);
						$echo.= '<label for="'.$accid.$iswidget.'"><input class="short" type="text" id="'.$accid.$iswidget.'" name="'.$productid.'['.$key.']" value="'.$opt["qty"].'" size="3" maxlength="3" /></label>';
					}else{
						$echo.= $opt["qty"];
					}
					/* DISCOUNT */
					$opt["price"]+=$addoprice;
					if(is_discountable(calculate_total())>0){
						$discount=is_discountable(calculate_total())/100;
						$disc_line= round($opt["price"]-($opt["price"] * $discount), 2);
					}
					$eline = $line_total = $opt["price"] * $opt["qty"];
					if(isset($disc_line))
						$eline = $disc_line*$opt["qty"];
					$echo.= "</td>\n<td headers=\"cartTotal$iswidget prod".$calt.$iswidget."\" class=\"amts\">".sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($eline,__('2','eshop')))."</td>\n";
					
					//TAX
					$etax = $eshopoptions['etax'];
					if(($pzone!='' && isset($eshopoptions['tax']) && $eshopoptions['tax']=='1') || ('yes' == $eshopoptions['downloads_only'] && isset($etax['unknown']) && $etax['unknown']!='')){
						if(isset($eshop_product['products'][$opt['option']]['tax']) && $eshop_product['products'][$opt['option']]['tax']!='' && $eshop_product['products'][$opt['option']]['tax']!='0'){
							if($pzone!='')
								$taxrate=eshop_get_tax_rate($eshop_product['products'][$opt['option']]['tax'], $pzone);
							else
								$taxrate=$etax['unknown'];
							$ttotax=$line_total;
							if(isset($disc_line))
								$ttotax=$disc_line * $opt["qty"];
							$taxamt=round(($ttotax * $taxrate)/100, 2);
							$echo.= '<td>'.$taxrate.'</td><td>'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($taxamt,__('2','eshop'))).'</td>';
							$taxtotal += $taxamt;
							$_SESSION['eshopcart'.$blog_id][$productid]['tax_rate']=$taxrate;
							$_SESSION['eshopcart'.$blog_id][$productid]['tax_amt']=$taxamt;
						}else{
							$echo.= '<td></td><td></td>';
						}
						
					}
					//
					
					if($iswidget=='' && $change == 'true'){
						$eshopdeleteimage=apply_filters('eshop_delete_image',WP_PLUGIN_URL.'/eshop/no.png');
						$echo .='<td headers="cartDelete" class="deletecartitem"><label for="delete'.$productid.$iswidget.'" class="hide">'.__('Delete this item','eshop').'</label><input type="image" src="'.$eshopdeleteimage.'" id="delete'.$productid.$iswidget.'" name="eshopdeleteitem['.$productid.']" value="'.$opt["qty"].'" title="'.__('Delete this item','eshop').'"/></td>';
					}
					$echo .="</tr>\n";
					if(isset($disc_line))
						$sub_total+=$disc_line*$opt["qty"];
					else		
						$sub_total+=$line_total;
					//weight
					if(isset($opt['weight']))
						$totalweight+=$opt['weight']*$opt['qty'];
				}
			}
			// display subtotal row - total for products only
			$disc_applied='';
			if(is_discountable(calculate_total())>0){
				$discount=is_discountable(calculate_total());
				$disc_applied='<small>('.sprintf(__('Including Discount of <span>%s%%</span>','eshop'),number_format_i18n(round($discount, 2),2)).')</small>';
			}
			if($iswidget==''  && $change == 'true')
				$emptycell='<td headers="cartDelete" class="eshopempty"></td>';
			else
				$emptycell='';
				
			if(($pzone!='' && isset($taxtotal) && isset($eshopoptions['tax']) && $eshopoptions['tax']=='1') || ('yes' == $eshopoptions['downloads_only'] && isset($etax['unknown']) && $etax['unknown']!='')){
				$emptycell='<td headers="subtotal carttaxamt" class="amts lb" colspan="2">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($taxtotal,__('2','eshop'))).'</td>';
			}
			$echo.= "<tr class=\"stotal\"><th id=\"subtotal$iswidget\" class=\"leftb\">".__('Sub-Total','eshop').' '.$disc_applied."</th><td headers=\"subtotal$iswidget cartTotal$iswidget\" class=\"amts lb\" colspan=\"2\">".sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($sub_total,__('2','eshop')))."</td>$emptycell</tr>\n";

			
			
			$final_price=$sub_total;
			$_SESSION['final_price'.$blog_id]=$final_price;
			// SHIPPING PRICE HERE
			$shipping=0;
			//$pzone will only be set after the checkout address fields have been filled in
			// we can only work out shipping after that point
			if($pzone!='' || ('yes' == $eshopoptions['downloads_only'] && isset($etax['unknown']) && $etax['unknown']!='')){
				if($pzone!=''){
					//shipping for cart.
					if($eshopoptions['shipping_zone']=='country'){
						$table=$wpdb->prefix.'eshop_countries';
					}else{
						$table=$wpdb->prefix.'eshop_states';
					}
					$table2=$wpdb->prefix.'eshop_rates';
					switch($eshopoptions['shipping']){
						case '1'://( per quantity of 1, prices reduced for additional items )
							foreach ($shiparray as $nowt => $shipclass){
								//add to temp array for shipping
								if(!in_array($shipclass, $tempshiparray)) {
									if($shipclass!='F'){
										array_push($tempshiparray, $shipclass);
										$shipzone='zone'.$pzone;
										$shipcost = $wpdb->get_var("SELECT $shipzone FROM $table2 WHERE class='$shipclass' and items='1' and rate_type='shipping' limit 1");
										$shipping+=$shipcost;
									}
								}else{
									if($shipclass!='F'){
										$shipzone='zone'.$pzone;
										$shipcost = $wpdb->get_var("SELECT $shipzone FROM $table2 WHERE class='$shipclass'  and items='2' and rate_type='shipping' limit 1");
										$shipping+=$shipcost;
									}
								}
							}
							break;
						case '2'://( once per shipping class no matter what quantity is ordered )
							foreach ($shiparray as $nowt => $shipclass){
								if(!in_array($shipclass, $tempshiparray)) {
									array_push($tempshiparray, $shipclass);
									if($shipclass!='F'){
										$shipzone='zone'.$pzone;
										$shipcost = $wpdb->get_var("SELECT $shipzone FROM $table2 WHERE class='$shipclass' and items='1' and rate_type='shipping' limit 1");
										$shipping+=$shipcost;
									}
								}
							}
							break;
						case '3'://( one overall charge no matter how many are ordered )
							$shiparray=array_unique($shiparray);
							foreach ($shiparray as $nowt => $shipclass){
								if($shipclass!='F'){
									$shipzone='zone'.$pzone;						
									$shipcost = $wpdb->get_var("SELECT $shipzone FROM $table2 WHERE class='A' and items='1' and rate_type='shipping' limit 1");
									$shipping+=$shipcost;
								}
							}
							break;
						case '4'://by weight/zone etc
							//$totalweight
							$shipzone='zone'.$pzone;
							$shipcost=$wpdb->get_var("SELECT $shipzone FROM $table2 where weight <= '$totalweight' && class='$shiparray' and rate_type='ship_weight' order by weight DESC limit 1");
							$shipping+=$shipcost;
							$_SESSION['eshopshiptype'.$blog_id]=$shiparray;
					}

					//display shipping cost
					//discount shipping?
					if(is_shipfree(calculate_total())  || eshop_only_downloads()) $shipping=0;

					$echo.= '<tr class="alt shippingrow"><th headers="cartItem'.$iswidget.'" id="scharge" class="leftb">';
					if($eshopoptions['shipping']=='4' && !eshop_only_downloads() && $shiparray!='0'){
						$eshopoptions['ship_types']=trim($eshopoptions['ship_types']);
						$typearr=explode("\n", $eshopoptions['ship_types']);
						//darn, had to add in unique to be able to go back a page
						$echo.=' <a href="'.get_permalink($eshopoptions['checkout']).'?eshoprand='.rand(2,100).'#shiplegend" title="'.__('Change Shipping','eshop').'">'.stripslashes(esc_attr($typearr[$shiparray-1])).'</a> ';
					}else{
						$echo .=__('Shipping','eshop');
					}
					if($eshopoptions['cart_shipping']!=''){
						$ptitle=get_post($eshopoptions['cart_shipping']);
						$echo.=' <small>(<a href="'.get_permalink($eshopoptions['cart_shipping']).'">'.__($ptitle->post_title,'eshop').'</a>)</small>';
					}

					$echo.='</th>
					<td headers="cartItem scharge" class="amts lb" colspan="2">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($shipping,__('2','eshop'))).'</td>';
					if($pzone!='' && isset($taxtotal) && isset($eshopoptions['tax']) && $eshopoptions['tax']=='1'){
						$taxrate=eshop_get_tax_rate($eshopoptions['etax']['shipping'], $pzone);
						$ttotax=$shipping;
						$taxamt=round(($ttotax * $taxrate)/100, 2);
						$taxtext = '';
						if($taxamt > '0.00')
							$taxtext = sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($taxamt,__('2','eshop')));
						$echo.= '<td>'.$taxrate.'</td><td>'.$taxtext.'</td>';
						$shiptax=$taxamt;
						$_SESSION['shipping'.$blog_id]['tax']=$shiptax;
						$_SESSION['shipping'.$blog_id]['taxrate']=$taxrate;

					}
					$echo .= '</tr>';
					$_SESSION['shipping'.$blog_id]['cost']=$shipping;
					$final_price=$sub_total+$shipping;
					$_SESSION['final_price'.$blog_id]=$final_price;
				}
				$excltax = '';
				if(isset($taxtotal) && isset($eshopoptions['tax']) && $eshopoptions['tax']=='1'){
					$excltax = __('(excl.tax)','eshop');
				}
				
				$echo.= '<tr class="total"><th id="cTotal'.$iswidget.'" class="leftb">'.__('Total Order Charges','eshop')."</th>\n<td headers=\"cTotal$iswidget cartTotal$iswidget\"  colspan=\"2\" class = \"amts lb\"><strong>".sprintf( __('%1$s%2$s <span>%3$s</span>','eshop'), $currsymbol, number_format_i18n($final_price, __('2','eshop')),$excltax)."</strong></td>";
				if(isset($shiptax) && isset($eshopoptions['tax']) && $eshopoptions['tax']=='1'){
					$withtax = $final_price + $shiptax + $taxtotal;
				}
				if('yes' == $eshopoptions['downloads_only'] && isset($etax['unknown']) && $etax['unknown']!=''){
					$withtax = $final_price + $taxtotal;
				}
				if(isset($eshopoptions['tax']) && $eshopoptions['tax']=='1'){
					$echo.= '<td headers="taxtotal" class="taxttotal amts lb" colspan="2"><strong>'.sprintf( __('%1$s%2$s <span>%3$s</span>','eshop'), $currsymbol, number_format_i18n($withtax,__('2','eshop')), __('(incl.tax)','eshop')).'</strong></td>';
				}
				$echo .= "</tr>";
			}

			$echo.= "</tbody></table>\n";
			// display unset/update buttons
			if($change == true){
				$echo.= "<div class=\"cartopt\"><input type=\"hidden\" name=\"save\" value=\"true\" />\n<input type=\"hidden\" name=\"eshopnon\" value=\"set\" />\n"; 
				$echo .= wp_nonce_field('eshop_add_product_cart','_wpnonce',true,false);
				$echo.= "<p><label for=\"update\"><input type=\"submit\" class=\"button\" id=\"update\" name=\"update\" value=\"".__('Update Cart','eshop')."\" /></label>";
				$echo.= "<label for=\"unset\"><input type=\"submit\" class=\"button\" id=\"unset\" name=\"unset\" value=\"".__('Empty Cart','eshop')."\" /></label></p>\n";
				$echo.= "</div>\n";
			}
			if ($change == 'true'){
				$echo.= "</form>\n";
			}
		}else{
			//if cart is empty - display a message - this is only a double check and should never be hit
			$echo.= "<p class=\"eshoperror error\">".__('Your shopping cart is currently empty.','eshop')."</p>\n";
		}
		if($eshopoptions['status']!='live'){
			$echo ="<p class=\"testing\"><strong>".__('Test Mode &#8212; No money will be collected.','eshop')."</strong></p>\n".$echo;
		}
		if(isset($_SESSION['eshop_discount'.$blog_id]) && valid_eshop_discount_code($_SESSION['eshop_discount'.$blog_id])){
			$echo .= '<p class="eshop_dcode">'.sprintf(__('Discount Code <span>%s</span> has been applied to your cart.','eshop'),$_SESSION['eshop_discount'.$blog_id]).'</p>'."\n";
		}
		//test
		if(isset($totalweight))
			$_SESSION['eshop_totalweight'.$blog_id]['totalweight']=$totalweight;
			
			
		if($iswidget=='w'){
			$echo.= '<br /><a class="cartlink" href="'.get_permalink($eshopoptions['cart']).'">'.__('Edit Cart','eshop').'</a>';
			$echo .='<br /><a class="checkoutlink" href="'.get_permalink($eshopoptions['checkout']).'">'.__('Checkout','eshop').'</a>';
		}

		return $echo;
	}
}
if (!function_exists('calculate_price')) {
	function calculate_price(){
		global $blog_id;
		if(isset($_SESSION['eshopcart'.$blog_id])){
			$thecart=$_SESSION['eshopcart'.$blog_id];
			// sum total price for all items in shopping shopcart
			$price = 0.0;

			if(is_array($thecart)){
				foreach ($thecart as $productid => $opt){
					$price=$price+$opt['price'];
				}
			}
			return number_format($price, 2);
		}
		return '0';
	}
}
if (!function_exists('calculate_total')) {
	function calculate_total(){
		global $blog_id;
		$thecart=$_SESSION['eshopcart'.$blog_id];
		// sum total price for all items in shopping shopcart
		$price = 0;
		if(is_array($thecart)){
			foreach ($thecart as $productid => $opt){
				$addtoprice=0;
				if(isset($opt['optset'])){
					$data['optset']=$opt['optset'];
					$data['addoprice']=$addtoprice;
					$data=eshop_parse_optsets($data);
					$addtoprice=$data['addoprice'];
				}			
				$price=$price+(($opt['price']+$addtoprice)*$opt['qty']);
			}
		}
		return $price;
	}
}
if (!function_exists('calculate_items')) {
	function calculate_items(){
		global $blog_id;
		if(isset($_SESSION['eshopcart'.$blog_id])){
			$thecart=$_SESSION['eshopcart'.$blog_id];
			// sum total items in shopping shopcart
			$items = 0;
			if(is_array($thecart))	{
				foreach ($thecart as $productid => $opt){
					if(is_array($opt)){
						foreach($opt as $option=>$qty){
							$items = $items+$qty;
						}
					}
				}
			}
			return $items;
		}
		return;
	}
}
if (!function_exists('is_discountable')) {
	function is_discountable($total){
		global $blog_id,$eshopoptions;
		$percent=0;
		$percent=apply_filters('eshop_is_discountable',$percent);
		if($percent != 0)
			return $percent;
		//check for 
		if(isset($_SESSION['eshop_discount'.$blog_id]) && eshop_discount_codes_check()){
			$chkcode=valid_eshop_discount_code($_SESSION['eshop_discount'.$blog_id]);
			if($chkcode && apply_eshop_discount_code('discount')>0)
				return apply_eshop_discount_code('discount');
		}
		for ($x=1;$x<=3;$x++){
			if($eshopoptions['discount_spend'.$x]!='')
				$edisc[$eshopoptions['discount_spend'.$x]]=$eshopoptions['discount_value'.$x];
		}
		if(isset($edisc) && is_array($edisc)){
			krsort($edisc);
			foreach ($edisc as $amt => $percent) {
				if($amt <= $total)
					return $percent;	
			}
			$percent=0;
		}
		return $percent;
	}
}

if (!function_exists('is_shipfree')) {
	function is_shipfree($total){
		global $blog_id,$eshopoptions;
		if(isset($_SESSION['eshop_discount'.$blog_id]) && eshop_discount_codes_check()){
			$chkcode=valid_eshop_discount_code($_SESSION['eshop_discount'.$blog_id]);
			if($chkcode && apply_eshop_discount_code('shipping'))
				return true;
		}
		$amt=$eshopoptions['discount_shipping'];
		if($amt!='' && $amt <= $total)
			return true;
		
		return false;

	}
}

// discount/promotional codes
if (!function_exists('apply_eshop_discount_code')) {
	function apply_eshop_discount_code($disc){
		global $wpdb, $blog_id;
		$now=date('Y-m-d');
		$disctable=$wpdb->prefix.'eshop_discount_codes';
		if(eshop_discount_codes_check()){
			$chkcode=valid_eshop_discount_code($_SESSION['eshop_discount'.$blog_id]);
			if(!$chkcode)
				return false;
			$grabthis=$wpdb->escape($_SESSION['eshop_discount'.$blog_id]);
			$row = $wpdb->get_row("SELECT * FROM $disctable WHERE id > 0 && live='yes' && disccode='$grabthis'");
			if($disc=='shipping'){
				switch($row->dtype){
					case '4':
						if($row->remain=='' || $row->remain>0) return true;
						break;
					case '5':
						if($row->enddate>=$now) return true;
						break;
					case '6':
						if(($row->remain=='' || $row->remain>0) && ($row->enddate>=$now)) return true;
						break;
					default:
						return false;
				}
			}

			if($disc=='discount'){
				switch($row->dtype){
					case '1':
						if($row->remain=='' || $row->remain>0) 
							return $row->percent;
						break;
					case '2':
						if($row->enddate>=$now) 
							return $row->percent;
						break;
					case '3':
						if(($row->remain=='' || $row->remain>0) && ($row->enddate>=$now))
							return $row->percent;
						break;
					default:
						return false;
				}
			}
		}
		//and just in case
		return false;

	}
}
if (!function_exists('eshop_discount_codes_check')) {
	function eshop_discount_codes_check(){
		global $wpdb;
		$disctable=$wpdb->prefix.'eshop_discount_codes';
		$max = $wpdb->get_var("SELECT COUNT(id) FROM $disctable WHERE id > 0 && live='yes'");
		if($max>0)
			return true;
		return false;
	}
}
if (!function_exists('valid_eshop_discount_code')) {
	function valid_eshop_discount_code($code){
		global $wpdb;
		$now=date('Y-m-d');
		$code=$wpdb->escape(strtolower($code));
		$disctable=$wpdb->prefix.'eshop_discount_codes';
		$row = $wpdb->get_row("SELECT * FROM $disctable WHERE id > 0 && live='yes' && disccode='$code'");
		if(is_object($row)){
			switch ($row->dtype){
				case '1':
					if($row->remain=='' || $row->remain>0) 
						return true;
					break;
				case '2':
					if($row->enddate>=$now) 
						return true;
					break;
				case '3':
					if(($row->remain=='' || $row->remain>0) && ($row->enddate>=$now))
						return true;
					break;
				case '4':
					if($row->remain=='' || $row->remain>0) return true;
					break;
				case '5':
					if($row->enddate>=$now) return true;
					break;
				case '6':
					if(($row->remain=='' || $row->remain>0) && ($row->enddate>=$now)) return true;
					break;
				default:
					return false;
			}
		}
		return false;
	}
}

if (!function_exists('eshop_get_tax_rate')) {
	function eshop_get_tax_rate($band, $pzone){
		global $wpdb, $blog_id, $eshopoptions;
		$area='country';
		if(isset($_SESSION['shiptocountry'.$blog_id]) && $_SESSION['shiptocountry'.$blog_id] == $eshopoptions['location'])
			$area='state';
		//rehash the zone to make sure we're picking up the correct tax rates!
		$tablecountries=$wpdb->prefix.'eshop_countries';
		$tablestates=$wpdb->prefix.'eshop_states';
		if($area=='country'){
			if(isset($_POST['ship_country']) && $_POST['ship_country']!='' && 'yes' != $eshopoptions['hide_shipping']){
				$pzoneid=$_POST['ship_country'];
			}elseif(isset($_POST['country']) && $_POST['country']!=''){
				$pzoneid=$_POST['country'];
			}
			if($eshopoptions['etax']['zonal']== 1)
				$pzone=$wpdb->get_var("SELECT zone FROM $tablecountries WHERE code='$pzoneid' LIMIT 1");
			else
				$pzone = 1;
		}else{
			if(isset($_POST['ship_state']) && $_POST['ship_state']!='' && 'yes' != $eshopoptions['hide_shipping']){
				$pzoneid=$_POST['ship_state'];
			}
			if(isset($_POST['state']) && $_POST['state']!=''){
				$pzoneid=$_POST['state'];
			}
			if(isset($pzoneid))
				$pzone=$wpdb->get_var("SELECT zone FROM $tablestates WHERE id='$pzoneid' LIMIT 1");

			if($eshopoptions['etax']['zonal']== 0)
				$pzone = 1;
		}
		$ratetable = $wpdb->prefix.'eshop_rates';
		$band=$wpdb->escape($band);
		$zone='zone'.$wpdb->escape($pzone);
		$taxrate = $wpdb->get_var("SELECT $zone FROM $ratetable WHERE id > 0 && class='$band' && area='$area' && rate_type='tax' ");

		return $taxrate;
	}
}



if (!function_exists('checkAlpha')) {
	//check string is alpha only.
	function checkAlpha($text){
		if(trim($text)!='')
			return true;
		else
			return false;
		//was:
		 //return preg_match ("/[A-z-]/", $text);
	}
}
if (!function_exists('checkEmail')) {
	//correctly formed email address?
	function checkEmail($email) {
	  $pattern = "/^[A-z0-9\._-]+"
			 . "@"
			 . "[A-z0-9][A-z0-9-]*"
			 . "(\.[A-z0-9_-]+)*"
			 . "\.([A-z]{2,6})$/";
	 return preg_match ($pattern, $email);
	}
}
if (!function_exists('checkAlphaNum')) {
	//check string is alphanumeric only
	function checkAlphaNum($text){
		 return preg_match ("/^[A-z0-9\._-]/", $text);
	}
}
if (!function_exists('checkPhone')) {
	//check phone number - needs work!
	function checkPhone($text){
		return preg_match ("/[A-z0-9\(\)]/", $text);
	}
}

if (!function_exists('eshopCartFields')) {
	function eshopCartFields(){
		global $wpdb, $blog_id;
		$echo='';
		$eshopcartarray=$_SESSION['eshopcart'.$blog_id];
		$x=0;
		foreach ($eshopcartarray as $productid => $opt){
			$x++;
			$productidident=$productid;
			//$toreplace=array(" ","-","$","\r","\r\n","\n","\\","&","#",";");
			//$productidident=md5($productidident);//str_replace($toreplace, "", $productidident);
			$echo.= "\n  <input type=\"hidden\" name=\"item_name_".$x."\" value=\"".$opt['pname']."\" />";
			$echo.= "\n  <input type=\"hidden\" name=\"eshopident_".$x."\" value=\"".$productidident."\" />";
			$echo.= "\n  <input type=\"hidden\" name=\"quantity_".$x."\" value=\"".$opt['qty']."\" />";
			$echo.= "\n  <input type=\"hidden\" name=\"weight_".$x."\" value=\"".$opt['weight']."\" />";
			/* options */
			$addoprice=0;
			if(isset($opt['optset'])){
				if(isset($qb)) unset($qb);
				$oset=array();
				$optings=unserialize($opt['optset']);
				foreach($optings as $foo=>$opst){
					if(!isset($opst['type']) || (isset($opst['text']) && $opst['text']!='')) 
						$qb[]="id=$opst[id]";
				}

				if(isset($qb)){
					$qbs = implode(" OR ", $qb);
					$otable=$wpdb->prefix.'eshop_option_sets';
					$orowres=$wpdb->get_results("select price, id from $otable where $qbs ORDER BY id ASC");
					foreach($orowres as $orow){
						$addoprice+=$orow->price;
					}
				}

			}

			$amt=number_format(round(($opt["price"]+$addoprice), 2),2,'.','');
			$echo.= "\n  <input type=\"hidden\" name=\"amount_".$x."\" value=\"".$amt."\" />";
			$echo.= "\n  <input type=\"hidden\" name=\"item_number_".$x."\" value=\"".$opt['pid']." : ".$opt['item']."\" />";
			$echo.= "\n  <input type=\"hidden\" name=\"postid_".$x."\" value=\"".$opt['postid']."\" />";
		}
		$echo.= "\n  <input type=\"hidden\" name=\"numberofproducts\" value=\"".$x."\" />";

		return $echo;
	}
}
if (!function_exists('eshopTaxCartFields')) {
	function eshopTaxCartFields($name, $value, $combine = 0){
		global $blog_id, $eshopoptions;
		if(!isset($eshopoptions['tax']) || $eshopoptions['tax']=='0')
			return "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
		
		$echo='';
		$eshopcartarray=$_SESSION['eshopcart'.$blog_id];
		$x=0;
		$taxamt=0;
		switch ($combine){
			case '0': //no just return 1 line for total tax
				if($name == 'amount'){				
					foreach ($eshopcartarray as $productid => $opt){
						if(isset($opt['tax_amt']))
							$taxamt += $opt['tax_amt'];
					}
					$echo .= "<input type=\"hidden\" name=\"tax\" value=\"".$taxamt."\" />\n";
				}
				
				$echo .= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
				break;
			case '1': //add tax to amount
				foreach ($eshopcartarray as $productid => $opt){
					$x++;
					if(isset($opt['tax_amt'])){
						$taxamt += $opt['tax_amt'];
						if($name == 'amount_'.$x){
							$value += $opt['tax_amt'];
							$echo .= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
						}
					}
				}
				if($name == 'amount'){
					$value +=$taxamt;
					$echo .= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
				}
				break;
		}
		if($name == 'amount')
			$echo .= eshopasstaxlinestoall();
		return $echo;
	}
}
if (!function_exists('eshopasstaxlinestoall')) {
	function eshopasstaxlinestoall(){
		global $blog_id;
		$echo='';
		$x=0;
		$eshopcartarray=$_SESSION['eshopcart'.$blog_id];
		foreach ($eshopcartarray as $productid => $opt){
			$x++;
			if(isset($opt['tax_amt'])){
				$echo .= "<input type=\"hidden\" name=\"tax_".$x."\" value=\"".$opt['tax_amt']."\" />\n";
				$echo .= "<input type=\"hidden\" name=\"tax_rate_".$x."\" value=\"".$opt['tax_rate']."\" />\n";
			}
		}
		return $echo;
	}
}

if (!function_exists('eshopShipTaxAmt')) {
	function eshopShipTaxAmt($combine = 0){
		global $blog_id, $eshopoptions;
		if(!isset($_SESSION['shipping'.$blog_id]['cost']))
			return;
		if(!isset($eshopoptions['tax']) || $eshopoptions['tax']=='0')
			return number_format($_SESSION['shipping'.$blog_id]['cost'],2);
		
		//if(isset($_SESSION['shipping'.$blog_id]['cost']))
			$shipping=$_SESSION['shipping'.$blog_id]['cost'];
		
		if(isset($_SESSION['shipping'.$blog_id]['tax']))
			$shipping += $_SESSION['shipping'.$blog_id]['tax'];
		return number_format($shipping,2);

	}
}
if (!function_exists('orderhandle')) {
	function orderhandle($_POST,$checkid){
		//This function puts the order into the db.
		global $wpdb, $blog_id,$eshopoptions;

		if (!is_user_logged_in() && isset($eshopoptions['users']) && $eshopoptions['users']=='yes' && isset($_SESSION['eshop_user'.$blog_id])) {
			//set up blank user if in case anything goes phooey
			$user_id=0;
			if(get_bloginfo('version')<'3.1')
				require_once ( ABSPATH . WPINC . '/registration.php' );
			//auto create a new user if they don't exist - only works if not logged in ;)
			$user_email=$_POST['email'];
			$utable=$wpdb->prefix ."users";
			$filtnames=apply_filters('eshop_add_username',$_POST['first_name'],$_POST['last_name']);
			$names=str_replace(" ","",$filtnames);
			$username = strtolower($names);
			$eshopch = $wpdb->get_results("SHOW TABLE STATUS LIKE '$utable'");

			//a unique'ish number
			$altusername=strtolower($names.$eshopch[0]->Auto_increment);
			if(!email_exists($user_email)){
				if(username_exists($username))
					$username=$altusername;

				if(!username_exists($username)){
					$random_password = wp_generate_password( 12, false );
					$user_id = wp_create_user( $username, $random_password, $user_email );
					$eshopuser['company']=$_POST['company'];
					$eshopuser['phone']=$_POST['phone'];
					$eshopuser['address1']=$_POST['address1'];
					$eshopuser['address2']=$_POST['address2'];
					$eshopuser['city']=$_POST['city'];
					$eshopuser['country']=$_POST['country'];
					$eshopuser['state']=$_POST['state'];
					$eshopuser['zip']=$_POST['zip'];
					if(isset($_POST['altstate']) && $_POST['altstate']!='')
						$eshopuser['altstate']=$_POST['altstate'];
					if(!is_numeric($_POST['state'])){
						$statechk=$wpdb->escape($_POST['state']);
						$sttable=$wpdb->prefix.'eshop_states';
						$eshopuser['state']=$wpdb->get_var("SELECT id FROM $sttable where code='$statechk' limit 1");
					}else{
						$eshopuser['state']=$_POST['state'];
					}
					update_user_meta( $user_id, 'eshop', $eshopuser );
					update_user_meta( $user_id, 'first_name', $_POST['first_name'] );
					update_user_meta( $user_id, 'last_name',$_POST['last_name'] );
					update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.
					wp_new_user_notification($user_id, $random_password);
				}
			}
		}else{
			global $current_user;
			 get_currentuserinfo();
			$user_id=$current_user->ID;
		}
		if(!isset($eshopoptions['users'])) $user_id='0';
		
	
		//$wpdb->show_errors();
		if (get_magic_quotes_gpc()) {
			$_POST=stripslashes_array($_POST);
		}
		$custom_field=$wpdb->escape($_POST['custom']);
		$first_name=$wpdb->escape($_POST['first_name']);
		$last_name=$wpdb->escape($_POST['last_name']);
		$email=$wpdb->escape($_POST['email']);
		//set up some defaults
		$phone=$company=$address1=$address2=$city=$zip=$state=$country=$paidvia='';
		if(isset($_POST['phone']))
			$phone=$wpdb->escape($_POST['phone']);
		if(isset($_POST['company']))
			$company=$wpdb->escape($_POST['company']);
		if(isset($_POST['address1']))
			$address1=$wpdb->escape($_POST['address1']);
		if(isset($_POST['address2']))
			$address2=$wpdb->escape($_POST['address2']);
		if(isset($_POST['city']))
			$city=$wpdb->escape($_POST['city']);
		if(isset($_POST['zip']))
			$zip=$wpdb->escape($_POST['zip']);
		if(isset($_POST['state']))
			$state=$wpdb->escape($_POST['state']);
		if(isset($_POST['country']))
			$country=$wpdb->escape($_POST['country']);
		$paidvia=$wpdb->escape($_SESSION['eshop_payment'.$blog_id]);
		if(strtolower($paidvia)==__('cash','eshop')){
			$eshopcash = $eshopoptions['cash'];
			if($eshopcash['rename']!='')
				$paidvia=$eshopcash['rename'];
		}
		if(strtolower($paidvia)==__('bank','eshop')){
			$eshopbank = $eshopoptions['bank'];
			if($eshopbank['rename']!='')
				$paidvia=$eshopbank['rename'];
		}
		if(isset($_POST['state']) && $_POST['state']=='' && isset($_POST['altstate']) && $_POST['altstate']!='')
			$state=$wpdb->escape($_POST['altstate']);

		if(isset($_POST['ship_name'])){
			$ship_name=$wpdb->escape($_POST['ship_name']);
		}else{
			$ship_name=$first_name.' '.$last_name;
		}
		if(isset($_POST['ship_phone'])){
			$ship_phone=$wpdb->escape($_POST['ship_phone']);
		}else{
			$ship_phone=$phone;
		}
		if(isset($_POST['ship_company'])){
			$ship_company=$wpdb->escape($_POST['ship_company']);
		}else{
			$ship_company=$company;
		}
		if(isset($_POST['ship_address'])){
			$ship_address=$wpdb->escape($_POST['ship_address']);
		}else{
			$ship_address=$address1.' '.$address2;
		}
		if(isset($_POST['ship_city'])){
			$ship_city=$wpdb->escape($_POST['ship_city']);
		}else{
			$ship_city=$city;
		}
		if(isset($_POST['ship_postcode'])){
			$ship_postcode=$wpdb->escape($_POST['ship_postcode']);
		}else{
			$ship_postcode=$zip;
		}
		if(isset($_POST['ship_country'])){
			$ship_country=$wpdb->escape($_POST['ship_country']);
		}else{
			$ship_country=$country;
		}
		if(isset($_POST['ship_state'])){
			$ship_state=$wpdb->escape($_POST['ship_state']);
		}else{
			$ship_state=$state;
		}
		
		if(empty($_POST['ship_state']) && !empty($_POST['ship_altstate']))
			$ship_state=$wpdb->escape($_POST['ship_altstate']);
		if(isset($_POST['reference'])){
			$reference=$wpdb->escape($_POST['reference']);
		}else{
			$reference='';
		}
		if(isset($_POST['comments'])){
			$comments=$wpdb->escape($_POST['comments']);
		}else{
			$comments='';
		}
		if(isset($_POST['affiliate']))
			$affiliate=$wpdb->escape($_POST['affiliate']);
		else
			$affiliate='';
		$detailstable=$wpdb->prefix.'eshop_orders';
		$itemstable=$wpdb->prefix.'eshop_order_items';
		$processing=__('Processing&#8230;','eshop');
		//readjust state if needed
		$sttable=$wpdb->prefix.'eshop_states';
		$getstate=$eshopoptions['shipping_state'];
		if($eshopoptions['show_allstates'] != '1'){
			$stateList=$wpdb->get_results("SELECT id,code,stateName FROM $sttable WHERE list='$getstate' ORDER BY stateName",ARRAY_A);
		}else{
			$stateList=$wpdb->get_results("SELECT id,code,stateName,list FROM $sttable ORDER BY list,stateName",ARRAY_A);
		}
		foreach($stateList as $code => $value){
			$eshopstatelist[$value['code']]=$value['id'];
		}
		if(isset($eshopstatelist[$state]))	$state=$eshopstatelist[$state];
		if(isset($eshopstatelist[$ship_state]))	$ship_state=$eshopstatelist[$ship_state];
//if (!is_user_logged_in()) {
		$eshopching=$wpdb->get_var("SELECT checkid from $detailstable where checkid='$checkid' limit 1");
		if($eshopching!=$checkid){
			$query1=$wpdb->query("INSERT INTO $detailstable
				(checkid, first_name, last_name,company,email,phone, address1, address2, city,
				state, zip, country, reference, ship_name,ship_company,ship_phone, 
				ship_address, ship_city, ship_postcode,	ship_state, ship_country, 
				custom_field,transid,edited,comments,thememo,paidvia,affiliate,user_id,admin_note,user_notes)VALUES(
				'$checkid',
				'$first_name',
				'$last_name',
				'$company',
				'$email',
				'$phone',
				'$address1',
				'$address2',
				'$city',
				'$state',
				'$zip',
				'$country',
				'$reference',
				'$ship_name',
				'$ship_company',
				'$ship_phone',
				'$ship_address',
				'$ship_city',
				'$ship_postcode',
				'$ship_state',
				'$ship_country',
				'$custom_field',
				'$processing',
				NOW(),
				'$comments',
				'',
				'$paidvia',
				'$affiliate',
				'$user_id',
				'',''
					);");
					
			
			$i=1;
			//this is here to generate just one code per order
			$code=eshop_random_code(); 
			while($i<=$_POST['numberofproducts']){
				//test
				$addoprice=0;
				$chk_id='item_number_'.$i;
				$chk_qty='quantity_'.$i;
				$chk_amt='amount_'.$i;
				//$chk_opt=$itemoption.$i;
				$chk_opt='item_name_'.$i;
				$chk_postid='postid_'.$i;
				$chk_weight='weight_'.$i;
				//tax
				$tax_amt = $tax_rate = '';
				if(isset($eshopoptions['tax']) && $eshopoptions['tax']=='1'){
					$chk_tax='tax_'.$i;
					$chk_tax_rate='tax_rate_'.$i;
					if(isset($_POST[$chk_tax])){				
						$tax_amt=$wpdb->escape($_POST[$chk_tax]);
						$tax_rate=$wpdb->escape($_POST[$chk_tax_rate]);
					}
				}
				$item_id=$wpdb->escape($_POST[$chk_id]);
				$item_qty=$wpdb->escape($_POST[$chk_qty]);
				$item_amt=$wpdb->escape(str_replace(',', "", $_POST[$chk_amt]));;
				$optname=$wpdb->escape($_POST[$chk_opt]);
				$post_id=$wpdb->escape($_POST[$chk_postid]);
				$weight=$wpdb->escape($_POST[$chk_weight]);
				$dlchking=$_POST['eshopident_'.$i];
				//add opt sets
				if(isset($_SESSION['eshopcart'.$blog_id][$dlchking]['optset'])){
					$data['optset']=$_SESSION['eshopcart'.$blog_id][$dlchking]['optset'];
					$data['addoprice']=$addoprice;
					$data=eshop_parse_optsets($data);
					$optset=$data['optset'];
					$addoprice=$data['addoprice'];
				}else{
					$optset='';
				}
				$optset=$wpdb->escape($optset);
				//end
				$thechk=$_SESSION['eshopcart'.$blog_id][$dlchking]['option'];
				$option_id=$wpdb->escape($thechk);
				if(strpos($thechk,' ')===true){
					$edown=explode(' ',$thechk);
					$edl=$edown[1];
				}else{
					$edl=$thechk;
				}
				$eshop_product=maybe_unserialize(get_post_meta( $post_id, '_eshop_product',true ));
				$dlchk='';
				if(isset($eshop_product['products'][$edl]['download']))
					$dlchk=$eshop_product['products'][$edl]['download'];
				if($dlchk!=''){
					//there are downloads.
					$queryitem=$wpdb->query("INSERT INTO $itemstable
					(checkid, item_id,item_qty,item_amt,tax_rate,tax_amt,optname,post_id,option_id,down_id,optsets,weight)values(
					'$checkid','$item_id','$item_qty','$item_amt', '$tax_rate', '$tax_amt', 
					'$optname','$post_id','$option_id',
					'$dlchk','$optset','$weight');");

					$wpdb->query("UPDATE $detailstable set downloads='yes' where checkid='$checkid'");
					//add to download orders table
					$dloadtable=$wpdb->prefix.'eshop_download_orders';
					//$email,$checkid already set
					$producttable=$wpdb->prefix.'eshop_downloads';
					$grabit=$wpdb->get_row("SELECT id,title, files FROM $producttable where id='$dlchk'");
					$downloads = $eshopoptions['downloads_num'];
					$wpdb->query("INSERT INTO $dloadtable
					(checkid, title,purchased,files,downloads,code,email)values(
					'$checkid',
					'$grabit->title',
					NOW(),
					'$grabit->files',
					'$downloads',
					'$code',
					'$email');"
					);

				}else{
					$queryitem=$wpdb->query("INSERT INTO $itemstable
					(checkid, item_id,item_qty,item_amt,tax_rate,tax_amt,optname,post_id,option_id,optsets,weight)values(
					'$checkid','$item_id','$item_qty','$item_amt','$tax_rate', '$tax_amt', 
					'$optname','$post_id','$option_id','$optset','$weight');");
				}
				$i++;

			}
			$postage=$wpdb->escape(str_replace(',', "", $_POST['shipping_1']));
			$shiptaxamt=$shiptaxrate='';
			if(isset($eshopoptions['tax']) && $eshopoptions['tax']=='1'){
				if(isset($_SESSION['shipping'.$blog_id]['cost']))
					$postage=$wpdb->escape(str_replace(',', "", $_SESSION['shipping'.$blog_id]['cost']));
				if(isset($_SESSION['shipping'.$blog_id]['tax']))
					$shiptaxamt=$wpdb->escape(str_replace(',', "", $_SESSION['shipping'.$blog_id]['tax']));
				if(isset($_SESSION['shipping'.$blog_id]['taxrate']))
					$shiptaxrate=$wpdb->escape(str_replace(',', "", $_SESSION['shipping'.$blog_id]['taxrate']));
			}
			$postage_name='';
			if(isset($_SESSION['eshopshiptype'.$blog_id])  && !eshop_only_downloads() && $_SESSION['eshopshiptype'.$blog_id]!='0'){
				$st=$_SESSION['eshopshiptype'.$blog_id]-1;
				$typearr=explode("\n", $eshopoptions['ship_types']);
				$postage_name=stripslashes(esc_attr($typearr[$st])).' ';
			}
			$postage_name.=__('Shipping','eshop');
			$querypostage=$wpdb->query("INSERT INTO  $itemstable 
					(checkid, item_id,item_qty,item_amt,tax_rate,tax_amt,optsets)values(
					'$checkid',
					'$postage_name',
					'1',
					'$postage',
					'$shiptaxrate',
					'$shiptaxamt',
					'');");
			//update the discount codes used, and remove from remaining
			$disctable=$wpdb->prefix.'eshop_discount_codes';
			if(eshop_discount_codes_check()){
				if(isset($_SESSION['eshop_discount'.$blog_id]) && valid_eshop_discount_code($_SESSION['eshop_discount'.$blog_id])){
					$discvalid=$wpdb->escape($_SESSION['eshop_discount'.$blog_id]);
					do_action('eshop_discount_code_used',$checkid,$discvalid);
					$wpdb->query("UPDATE $disctable SET used=used+1 where disccode='$discvalid' limit 1");

					$remaining=$wpdb->get_var("SELECT remain FROM $disctable where disccode='$discvalid' && dtype!='2' && dtype!='5' limit 1");
					//reduce remaining
					if(is_numeric($remaining) && $remaining!='')			
						$wpdb->query("UPDATE $disctable SET remain=remain-1 where disccode='$discvalid' limit 1");
				}
			}
			
			do_action('eshoporderhandle',$_POST,$checkid);

			if($eshopoptions['status']!='live'){
				echo "<p class=\"testing\"><strong>".__('Test Mode &#8212; No money will be collected. This page will not auto redirect in test mode.','eshop')."</strong></p>\n";
			}
		}
	}
}
if (!function_exists('stripslashes_array')) {
	//only use after magic quote check
	function stripslashes_array($array) {
		return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}
}
if (!function_exists('sanitise_array')) {
	//sanitises input array!
	function sanitise_array($array) {
		return is_array($array) ? array_map('sanitise_array', $array) : esc_attr($array);
	}
}
if(!function_exists('eshop_build_cookie')) {
	function eshop_build_cookie($var_array) {
		$out='';
	  if (is_array($var_array)) {
		foreach ($var_array as $index => $data) {
		  $out.= ($data!="" && !is_array($data)) ? $index."=".stripslashes($data)."|" : "";
		}
	  }
	  return rtrim($out,"|");
	}
}
if (!function_exists('eshop_break_cookie')) {
	function eshop_break_cookie($cookie_string) {
	  $array=explode("|",$cookie_string);
	  foreach ($array as $i=>$stuff) {
		$stuff=explode("=",$stuff);
		$array[$stuff[0]]=$stuff[1];
		unset($array[$i]);
	  }
	  return $array;
	}
}

if (!function_exists('eshop_only_downloads')) {
	function eshop_only_downloads() {
		global $blog_id;
		$num=0;
		$items=0;
		$eshopcartarray=$_SESSION['eshopcart'.$blog_id];
		foreach ($eshopcartarray as $productid => $opt){
			$post_id=$opt['postid'];
			$option=$opt['option'];
			$eshop_product=maybe_unserialize(get_post_meta( $post_id, '_eshop_product',true ));
			if(isset($eshop_product['products'][$option]['download'])){
				$dlchk=$eshop_product['products'][$option]['download'];
				if($dlchk!='')
					$num++;
			}
			$items++;
		}
		if($num==$items)
			return true;
		
		return false;
	}
}


if (!function_exists('eshop_rtn_order_details')) {
	/*
	will return an array consisting of
	status
	name - first/last/company
	cart details
	address
	extras - comments/reference/PO
	contact info
	
	suitable for emailing.
	*/
	function eshop_rtn_order_details($checkid){
		global $wpdb,$eshopoptions;
		$dtable=$wpdb->prefix.'eshop_orders';
		$itable=$wpdb->prefix.'eshop_order_items';
		$stable=$wpdb->prefix.'eshop_states';
		$ctable=$wpdb->prefix.'eshop_countries';

		$dquery=$wpdb->get_results("Select * From $dtable where checkid='$checkid' limit 1");
		foreach($dquery as $drow){
			$status=$drow->status;
			$checkid=$drow->checkid;
			$custom=$drow->custom_field;
			$transid=$drow->transid;
			$edited=$drow->edited;
			$affiliate=$drow->affiliate;
			$paidvia=$drow->paidvia;
			$dbid=$drow->id;
		}
		if($status=='Completed'){$status=__('Order Received','eshop');}
		if($status=='Pending' || $status=='Waiting'){$status=__('Pending Payment','eshop');}
		$contact=$cart=$address=$extras= '';
		$result=$wpdb->get_results("Select * From $itable where checkid='$checkid' ORDER BY id ASC");
		$total=0;
		$taxtotal=0;
		$currsymbol=$eshopoptions['currency_symbol'];
		$cart.=__('Transaction id:','eshop').' '.$transid."\n";
		$containsdownloads=0;
		$prod_ids=array();
		foreach($result as $myrow){
			//default
			if(isset($myrow->post_id) && $myrow->post_id > 0)
				$prod_ids[]=$myrow->post_id;
			$value=$myrow->item_qty * $myrow->item_amt;
			$shipping_charge=0;
			$itemid=$myrow->item_id.' '.$myrow->optsets;
			// add in a check if postage here as well as a link to the product
			if(trim($itemid)=='postage' || trim($itemid)==__('Shipping','eshop')){
				$cart.= __('Shipping Charge:','eshop').' '.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($value, __('2','eshop')));
				if(isset($myrow->tax_amt) && $myrow->tax_amt!=''){
					$taxvalue = $myrow->tax_amt;
					$value += $taxvalue;
					$cart.= "\n".__('Sales Tax:','eshop').' '.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($taxvalue, __('2','eshop')));
				}
				$shipping_charge=number_format_i18n($value, __('2','eshop'));
				$cart .= "\n\n";
			}else{
				$cart.= $myrow->optname." ".strip_tags($itemid)."\n\n".__('Quantity:','eshop')." ".$myrow->item_qty."\n".__('Price:','eshop')." ".sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($value, __('2','eshop')));
				if(isset($myrow->tax_amt) && $myrow->tax_amt!=''){
					$taxvalue = $myrow->tax_amt;
					$value += $taxvalue;
					$cart.= "\n".__('Sales Tax:','eshop').' '.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($taxvalue, __('2','eshop')));
				}
				$cart .= "\n\n";
			}
			if(isset($taxvalue))
				$taxtotal += $taxvalue;
			$total += $value;
			//check if downloadable product
			if($myrow->down_id!='0'){
				$containsdownloads++;
			}
		}
		$arrtotal=number_format_i18n($total, __('2','eshop'));
		$arrtaxtotal=number_format_i18n($taxtotal, __('2','eshop'));

		$cart.= __('Total','eshop').' '.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($total, __('2','eshop')))."\n";
		
		$thisdate = eshop_real_date($custom);
		
		$cart.= "\n".__('Order placed on','eshop')." ".$thisdate."\n";
		foreach($dquery as $drow){
			$address.= "\n".__('Mailing Address:','eshop')."\n".$drow->address1.", ".$drow->address2."\n";
			$address.= $drow->city."\n";
			$qcode=$wpdb->escape($drow->state);
			$qstate = $wpdb->get_var("SELECT stateName FROM $stable WHERE id='$qcode' limit 1");
			if($qstate=='') $qstate=$drow->state;
			$address.= $qstate."\n";
			$address.= $drow->zip."\n";
			$qccode=$wpdb->escape($drow->country);
			$qcountry = $wpdb->get_var("SELECT country FROM $ctable WHERE code='$qccode' limit 1");
			$address.= $qcountry."\n";
		
			$contact.= __('Phone:','eshop').' '.$drow->phone."\n";
			$contact.= __('Email:','eshop').' '.$drow->email."\n";

			if($drow->ship_name!='' && $drow->ship_address!='' && $drow->ship_city!='' && $drow->ship_postcode!=''){
				$address.= "\n".__('Shipping Address:','eshop')."\n";
				$address.= $drow->ship_name."\n";
				$address.= $drow->ship_company."\n";
				if(($drow->ship_phone!=$drow->phone) && $drow->ship_phone!=''){
					$contact.= __('Shipping address phone number:','eshop')."\n".$drow->ship_phone."\n";
				}
				$address.= $drow->ship_address."\n";
				$address.= $drow->ship_city."\n";
				$qcode=$wpdb->escape($drow->ship_state);
				$sqstate = $wpdb->get_var("SELECT stateName FROM $stable WHERE id='$qcode' limit 1");
				if($sqstate=='') $sqstate=$drow->ship_state;
				$address.= $sqstate."\n";
				$address.= $drow->ship_postcode."\n";
				$qccode=$wpdb->escape($drow->ship_country);
				$sqcountry = $wpdb->get_var("SELECT country FROM $ctable WHERE code='$qccode' limit 1");
				$address.= $sqcountry."\n";
			}
			if($drow->thememo!=''){
				$extras.= __('Paypal memo:','eshop')."\n".$drow->thememo."\n";
			}
			if($drow->reference!=''){
					$extras.= __('Reference/PO:','eshop')."\n".$drow->reference."\n";
			}
			if($drow->comments!=''){
					$extras.= __('Order comments:','eshop')."\n".$drow->comments."\n";
			}
		}
		if($drow->company!=''){
			$ename=$drow->first_name." ".$drow->last_name.' '.__('of','eshop').' '.$drow->company;
		}else{
			$ename=$drow->first_name." ".$drow->last_name;
		}
		$user_id=$drow->user_id;
		$firstname=$drow->first_name;
		$eemail=$drow->email;
		$downloads='';
		if($containsdownloads>0){
			$downtable=$wpdb->prefix.'eshop_download_orders';
			$chkcode= $wpdb->get_var("SELECT code FROM $downtable WHERE checkid='$drow->checkid' && email='$drow->email'");
			$downloads=get_permalink($eshopoptions['show_downloads'])."\n";
			$downloads.=__('Email:','eshop').' '.$drow->email."\n";
			$downloads.=__('Code:','eshop').' '.$chkcode."\n";
		}
		$cart=html_entity_decode($cart);
		$extras=html_entity_decode($extras);
		$firstname=html_entity_decode($firstname);
		$ename=html_entity_decode($ename);
		$address=html_entity_decode($address);
		$array=array("status"=>$status,"firstname"=>$firstname, "ename"=>$ename,"eemail"=>$eemail,"cart"=>$cart,"downloads"=>$downloads,
		"address"=>$address,"extras"=>$extras, "contact"=>$contact,"date"=>$edited,"affiliate"=>$affiliate,"user_id"=>$user_id,
		"transid"=>$transid,"total"=>$arrtotal,"taxtotal"=>$arrtaxtotal,"dbid"=>$dbid, 'shipping_charge'=>$shipping_charge, 'prod_ids'=>$prod_ids,'paidvia'=>$paidvia);
		$secarray=apply_filters('eshoprtndetails',$dquery);
		$retarray=array_merge($array,$secarray);
		return $retarray;
	}
}

if (!function_exists('eshop_add_excludes')) {
	function eshop_add_excludes($excludes) {
		global $blog_id,$eshopoptions;
		if(!isset($_SESSION['eshopcart'.$blog_id]) && $eshopoptions['hide_cartco']=='yes'){
			$excludes[]=$eshopoptions['cart'];
			$excludes[]=$eshopoptions['checkout'];
		}
		$excludes[]=$eshopoptions['show_downloads'];
		$excludes[]=$eshopoptions['cart_success'];
		$excludes[]=$eshopoptions['cart_cancel'];
		return $excludes;
	}
}

if (!function_exists('eshop_fold_menus')) {
	function eshop_fold_menus($exclusions = "") {
		global $post, $wpdb,$eshopoptions;
		//code taken from fold page menu plugin and adapted
		if (isset($post->ID))
			$id=$post->ID;
		else
			$id=$eshopoptions['cart'];//fix to hide menus on other pages
		$x = $id;
		$inclusions = "(post_parent <> " . strval($x) . ")";
		do {
			$include = $wpdb->get_results("SELECT post_parent " .
			"FROM $wpdb->posts " .
			"WHERE ID = " . $x . " " .
			"LIMIT 1",ARRAY_N);
			$x = $include[0][0];
			$inclusions .= " AND (post_parent <> " . $x . ")";
		} while ($x <> 0);

		$rows = $wpdb->get_results("SELECT ID " .
		"FROM $wpdb->posts " .
		"WHERE (post_type = 'page') AND " .
		$inclusions, ARRAY_N);
		if ( count($rows) ) {
			foreach ( $rows as $row ) {
				foreach ( $row as $ro ) {
					if ($exclusions <> "")
						//$exclusions .= ",";
						$exclusions[]= strval($ro);
				}
			}
		}
		return $exclusions;
	}
}
if (!function_exists('eshop_random_code')) {
	function eshop_random_code ($length = 10){
		$password = "";
		//characters allowed
		//lower case l, upper case O, number 1 and number 0 have been removed for clarity
		$allowed = __('abcdefghijkmnopqrstuvwxyz23456789ABCDEFGHIJKLMNPQRSTUVWXYZ','eshop');    
		$i = 0; 
		// Loop until password string is the required length
		while ($i < $length){  
		// Select random character allowed string
			$char = substr($allowed, mt_rand(0, strlen($allowed)-1), 1);
		// Add random character to password string
			$password .= $char;
			$i++;
		}

		// Return random password
		return $password;
	}
}
if (!function_exists('eshop_download_the_product')) {
	function eshop_download_the_product($_POST){
		global $wpdb,$eshopoptions;
		$table = $wpdb->prefix ."eshop_downloads";
		$ordertable = $wpdb->prefix ."eshop_download_orders";
		$dir_upload = eshop_download_directory();
		$echo='';
		if (isset($_POST['eshoplongdownloadname'])){
			//check again everything else ok then go ahead
			$id=$wpdb->escape($_POST['id']);
			$code=$wpdb->escape($_POST['code']);
			$email=$wpdb->escape($_POST['email']);
			set_time_limit(1000);
			if($id!='all'){
				//single file handling
				$ordertable = $wpdb->prefix ."eshop_download_orders";
				$chkcount = $wpdb->get_var("SELECT COUNT(id) FROM $ordertable where email='$email' && code='$code' && id='$id' && downloads!=0");
				$chkresult = $wpdb->get_results("Select * from $ordertable where email='$email' && code='$code' && id='$id' && downloads!=0");
				if($chkcount>0){
					foreach($chkresult as $chkrow){
						// make sure output buffering is disabled
						session_write_close();
					   	ob_end_clean();
						set_time_limit(0);
						$item=$chkrow->files;
						$wpdb->query("UPDATE $ordertable SET downloads=downloads-1 where email='$email' && code='$code' && id='$id' limit 1");
						//update product with number of downloads made
						$wpdb->query("UPDATE $table SET downloads=downloads+1 where title='$chkrow->title' && files='$item' limit 1");
						//force download - should bring up save box, but it doesn't!
						$dload=$dir_upload.$item;
						$dlfilter=apply_filters('eshop_download_filter',$dload,$item);
						if( !has_filter( 'eshop_download_filter') ) {
							list($title, $ext) = explode('.', $item);
							if(!is_dir($dir_upload.$title)){
								header("Pragma: public"); // required
								header("Expires: 0");
								header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
								header("Cache-Control: private",false); // required for certain browsers 
								header("Content-Type: application/force-download");
								// it even allows spaces in filenames
								header('Content-Disposition: attachment; filename="'.$item.'"');
								header("Content-Transfer-Encoding: binary");
								header("Content-Length: ".filesize($dload));
								//ob_clean();
								//flush();
								readfile("$dload");
								//alternatives download methods comment above, and uncomment below
								//eshop_readfile($dload);
								//eshop_readfile_temp($dload,$item);
								exit;
							}else{
								eshop_multi_download($email,$code,false);
							}
						}
					}
				}
			}else{
			
				eshop_multi_download($email,$code,true);
		
			}
		}
		return;
	}
}


if (!function_exists('eshop_multi_download')){
  function eshop_multi_download($email, $code,$update=true) {
		//multiple files - need to be zipped.
		include_once("archive-class.php");
		global $wpdb,$eshopoptions;
		$table = $wpdb->prefix ."eshop_downloads";
		$ordertable = $wpdb->prefix ."eshop_download_orders";
		$dir_upload = eshop_download_directory();
		
		$date=date("Y-m-d");
		$backupfilename=get_bloginfo('name').'-'.$date.'.zip';
		$test = new zip_file($backupfilename);

		// Create archive in memory
		// Do not recurse through subdirectories
		// Do not store file paths in archive
		// Add lib/archive.php to archive
		//$test->add_files("src/archive.php");
		// Add all jpegs and gifs in the images directory to archive

		$addfiles=array();
		$test->set_options(array('inmemory' => 1, 'recurse' => 1, 'storepaths' => 0,'prepend' => 'downloads'));
		$chkcount = $wpdb->get_var("SELECT COUNT(id) FROM $ordertable where email='$email' && code='$code' && downloads!='0'");
		$chkresult = $wpdb->get_results("Select * from $ordertable where email='$email' && code='$code' && downloads!='0'");
		if($chkcount>0){
			foreach($chkresult as $drow){
				$item=$drow->files;
				$dload=$dir_upload.$drow->files;
				list($title, $ext) = explode('.', $drow->files);
				if(is_dir($dir_upload.$title)){
					$addfiles[]=$dir_upload.$title;
				}else{
					$addfiles[]=$dload;
				}
				if($update === true){
					$wpdb->query("UPDATE $ordertable SET downloads=downloads-1 where email='$email' && code='$code' && id='$drow->id'");
					//update product with number of downloads made
					$wpdb->query("UPDATE $table SET downloads=downloads+1 where title='$drow->title' && files='$item' limit 1");
				}
			}
		}
		$test->add_files($addfiles);
		// make sure output buffering is disabled
		ob_start();
		ob_end_clean();
		// Create archive in memory
		$test->create_archive();
		// Send archive to user for download
		$test->download_file();
		exit;
	}
}

if (!function_exists('eshop_readfile')){
  // Read a file and display its content chunk by chunk
  function eshop_readfile($filename, $retbytes = TRUE) {
    $buffer = '';
    $cnt =0;
    $chunksize=1024*1024;// Size (in bytes) of tiles chunk
    //also try this line
    //set_time_limit(300);
    // $handle = fopen($filename, 'rb');
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
      return false;
    }
    while (!feof($handle)) {
      $buffer = fread($handle, $chunksize);
      echo $buffer;
      ob_flush();
      flush();
      if ($retbytes) {
        $cnt += strlen($buffer);
      }
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
      return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
  }
}

//new alt
if (!function_exists('eshop_readfile_temp')){
  function eshop_readfile_temp($fileloc,$filename) {
  	$download_attempt=0;
	do {
        $fs = fopen($fileloc, "rb");
        $uploads = wp_upload_dir();
		$temp_file_name=$uploads['basedir'].'/'.$filename;
        if (!$fs) {
          die (__('Sorry there was an error with the download','eshop'));
        } else {
          $fm = fopen ($temp_file_name, "w");
          stream_set_timeout($fs, 30);

          while(!feof($fs)) {
            $contents = fread($fs, 4096); // Buffered download
            fwrite($fm, $contents);
            $info = stream_get_meta_data($fs);
            if ($info['timed_out']) {
              break;
            }
          }
          fclose($fm);
          fclose($fs);

          if ($info['timed_out']) {
            // Delete temp file if fails
            unlink($temp_file_name);
            $download_attempt++;
          } else {
			wp_redirect($uploads['baseurl'].'/'.$filename, '302');
            unlink($temp_file_name);
            //delete on success.
            exit;
          }
        }
      } while ($download_attempt < 5 && $info['timed_out']);
	}
}



if (!function_exists('eshop_visible_credits')) {
	function eshop_visible_credits($pee){
		//for front end
		global $eshopoptions;
		$version = explode(".", ESHOP_VERSION);
		if('yes' == $eshopoptions['credits']){
			 echo '<p class="creditline">'.__('Powered by','eshop').' <a href="http://www.quirm.net/" title="'.__('Created by','eshop').' Rich Pedley">eShop</a>
		<dfn title="'.__('Version','eshop').' '.ESHOP_VERSION.'">v.'.$version[0].'</dfn></p> ';
		}else{
			echo '<!--'.__('Powered by','eshop').' eShop v'.ESHOP_VERSION.' by Rich Pedley http://www.quirm.net/-->';
		}
		return;
	}
}
if (!function_exists('eshop_show_extra_links')) {
	function eshop_show_extra_links(){
		global $eshopoptions;
		$linkattr=apply_filters('eShopCheckoutLinksAttr','');
		$xtralinks='';
		if($eshopoptions['cart_shipping']!='' && $eshopoptions['downloads_only']!='yes'){
			$ptitle=get_post($eshopoptions['cart_shipping']);
			$xtralinks.='<a href="'.get_permalink($eshopoptions['cart_shipping']).'"'.$linkattr.'>'.$ptitle->post_title.'</a>, ';
		}
		if($eshopoptions['xtra_privacy']!=''){
			$ptitle=get_post($eshopoptions['xtra_privacy']);
			if($ptitle->post_title!=''){
				$xtralinks.='<a href="'.get_permalink($eshopoptions['xtra_privacy']).'"'.$linkattr.'>'.$ptitle->post_title.'</a>, ';
			}
		}
		if($eshopoptions['xtra_help']!=''){
			$ptitle=get_post($eshopoptions['xtra_help']);
			if($ptitle->post_title!=''){
				$xtralinks.='<a href="'.get_permalink($eshopoptions['xtra_help']).'"'.$linkattr.'>'.$ptitle->post_title.'</a>, ';
			}
		}
		
		if($xtralinks!=''){
			return '('.substr($xtralinks, 0, -2).')';
		}else{
			return;
		}
	}
}
if (!function_exists('eshop_download_directory')) {
    function eshop_download_directory(){
		$dirs=wp_upload_dir();
        $upload_dir=$dirs['basedir'];
        $eshop_goto=$upload_dir.'/../eshop_downloads';
		return apply_filters('eshop_download_directory',$eshop_goto.'/');
    }
}
if (!function_exists('eshop_files_directory')) {
    function eshop_files_directory(){
        $dirs=wp_upload_dir();
        $upload_dir=$dirs['basedir'];
        $url_dir=$dirs['baseurl'];
        if(substr($url_dir, -1)!='/')$url_dir.='/';
       	$eshop_goto=$upload_dir.'/eshop_files';
		$urlpath=$url_dir.'eshop_files/';
		$urlpath=preg_replace('/\/wp-content\/blogs\.dir\/\d+/', '', $urlpath);
		$rtn=array(0=>$eshop_goto.'/',1=>$urlpath);
		return apply_filters('eshop_files_directory',$rtn);;
    }
}

if (!function_exists('eshop_from_address')) {
	function eshop_from_address(){
		global $eshopoptions;
		if($eshopoptions['from_email']!=''){
			$headers='From: '.html_entity_decode( stripslashes(get_bloginfo('name')), ENT_QUOTES).' <'.$eshopoptions['from_email'].">\n";
		}elseif($eshopoptions['business']!=''){
			$headers='From: '.html_entity_decode( stripslashes(get_bloginfo('name')), ENT_QUOTES).' <'.$eshopoptions['business'].">\n";
		}else{
			$headers='';
		}
		return $headers;
	}
}

if (!function_exists('eshop_excerpt_img')) {
	function eshop_excerpt_img($output){
		global $post,$eshopoptions;
		$echo='';
		if(is_search()){
			$isaproduct=get_post_meta($post->ID,'_eshop_product',true);
			$w=get_option('thumbnail_size_w');
			$h=get_option('thumbnail_size_h');
			if (has_post_thumbnail( $post->ID ) ) {
				$eimg =get_the_post_thumbnail( $post->ID, array($w, $h))."\n";
			}else{
				$eimage=eshop_files_directory();
				$eshopnoimage=apply_filters('eshop_no_image',$eimage['1'].'noimage.png');
				$eimg ='<img src="'.$eshopnoimage.'" height="'.$h.'" width="'.$w.'" alt="" />'."\n";
			}
			if($eshopoptions['search_img'] == 'all'){
					$echo .=$eimg;
			}elseif($eshopoptions['search_img'] == 'yes' && $isaproduct!=''){
				$echo .=$eimg;
			}
		}
		return $echo.$output;
	}
}

if (!function_exists('eshop_plural')) {
	function eshop_plural( $quantity, $singular, $plural ){
	  if( intval( $quantity ) == 1 )
		return $singular;
	  return $plural;
	}
}
if (!function_exists('eshop_email_parse')) {
	function eshop_email_parse($this_email,$array, $d='yes'){
		global $eshopoptions;
	//	require_once ( ABSPATH . WPINC . '/registration.php' );
		$this_email = str_replace('{STATUS}', $array['status'], $this_email);
		$this_email = str_replace('{FIRSTNAME}', $array['firstname'], $this_email);
		$this_email = str_replace('{NAME}', $array['ename'], $this_email);
		$this_email = str_replace('{EMAIL}', $array['eemail'], $this_email);
		$this_email = str_replace('{CART}', $array['cart'], $this_email);
		if(isset($eshopoptions['downloads_email']) && 'yes' == $eshopoptions['downloads_email'] || $d=='yes')
			$this_email = str_replace('{DOWNLOADS}', $array['downloads'], $this_email);
		else
			$this_email = str_replace('{DOWNLOADS}', '', $this_email);
		/*
		if($d=='yes')
			$this_email = str_replace('{DOWNLOADS}', $array['downloads'], $this_email);
		else
			 $this_email = str_replace('{DOWNLOADS}', '', $this_email);
		*/
		$this_email = str_replace('{ADDRESS}', $array['address'], $this_email);
		$this_email = str_replace('{REFCOMM}', $array['extras'], $this_email);
		$this_email = str_replace('{CONTACT}', $array['contact'], $this_email);
		$this_email = str_replace('{ORDERDATE}', $array['date'], $this_email);
		$filterit=array($array,$this_email);
		$temp = apply_filters('eshopemailparse',$filterit);
		if(!is_array($temp)) $this_email=$temp;
		return $this_email;
	}
}
if (!function_exists('eshop_cache')) {
	function eshop_cache(){
		global $eshopoptions;
	  	if(!defined('DONOTCACHEPAGE') && $eshopoptions['set_cacheability']=='yes'){
	  		//wpsupercache
			define("DONOTCACHEPAGE", "true");
		}
	}
}
if (!function_exists('create_eshop_error')) {
	//old method
	function create_eshop_error($error){ ?>
		<div class="error fade"><?php echo $error; ?></div>
	<?php
	}
}
if (!function_exists('eshop_check_error')) {
	function eshop_check_error() {
		if(isset($_GET['eshop_message']))
			return eshop_error_message($_GET['eshop_message']);
	}
}
if (!function_exists('eshop_error')) {
	function eshop_error($loc) {
 		return add_query_arg( 'eshop_message', 1, $loc );
	}
}
if (!function_exists('eshop_price_error')) {
	function eshop_price_error($loc) {
 		return add_query_arg( 'eshop_message', 2, $loc );
	}
}
if (!function_exists('eshop_weight_error')) {
	function eshop_weight_error($loc) {
 		return add_query_arg( 'eshop_message', 3, $loc );
	}
}
if (!function_exists('eshop_stkqty_error')) {
	function eshop_stkqty_error($loc) {
 		return add_query_arg( 'eshop_message', 4, $loc );
	}
}
if (!function_exists('eshop_saleprice_error')) {
	function eshop_saleprice_error($loc) {
 		return add_query_arg( 'eshop_message', 5, $loc );
	}
}
if (!function_exists('eshop_error_message')) {
	function eshop_error_message($num){ 
		$messages=array(
		'1'=> __('Stock Available not set, as all details were not filled in.','eshop'),
		'2'=> __('Price incorrect, please only enter a numeric value.','eshop'),
		'3'=> __('Weight incorrect, please only enter a numeric value.','eshop'),
		'4'=> __('Stock Quantity is incorrect, please only enter a numeric value.','eshop'),
		'5'=> __('Sale Price incorrect, please only enter a numeric value.','eshop'),
		'100'=>__('eShop settings updated.','eshop')
		);
		$messages=apply_filters('eshop_error_messages',$messages);
		if($num<100 && array_key_exists($num, $messages)){
		?>
		<div class="error fade"><p><?php echo $messages[$num]; ?></p></div>
		<?php
		}else{
		?>
			<div id="message" class="updated fade"><p><?php echo $messages[$num]; ?></p></div>
		<?php
		}

	}
}
if (!function_exists('eshop_wp_version')) {
	function eshop_wp_version($req){ 
		global $wp_version;
		if (version_compare($wp_version, $req, '>=')) {
			// version x or higher
			return true;
		}
		return false;
	}
}
if (!function_exists('eshop_cart_process')) {
	function eshop_cart_process($data=''){
		global $wpdb, $blog_id,$wp_query,$eshopoptions,$_POST;
		if($data!='')
			$_POST=$data;
		if(!isset($_POST['eshopnon'])){
			return;
		}
		wp_verify_nonce('eshop_add_product_cart');
		unset($_POST['eshopnon']);
		//setup variables:
		$option=$qty=$pclas=$productid=$pid=$pname=$iprice='';
		$echo='';
		//cache
		eshop_cache();
		//delete the session, empties the cart
		if(isset($_POST['unset']) || (calculate_items()==0 && isset($_SESSION['eshopcart'.$blog_id]) && sizeof($_SESSION['eshopcart'.$blog_id])>0)){
			unset($_SESSION['eshopcart'.$blog_id]);
			unset($_SESSION['final_price'.$blog_id]);
			unset($_SESSION['items'.$blog_id]);
			$_POST['save']='false';
		}
		if(isset($eshopoptions['min_qty']) && $eshopoptions['min_qty']!='') 
			$min=$eshopoptions['min_qty'];
		if(isset($eshopoptions['max_qty']) && $eshopoptions['max_qty']!='') 
			$max=$eshopoptions['max_qty'];
		if(!isset($_POST['save'])){
			//on windows this check isn't working correctly, so I've added ==0 
			if (get_magic_quotes_gpc()) {
				$_COOKIE = stripslashes_array($_COOKIE);
				$_FILES = stripslashes_array($_FILES);
				$_GET = stripslashes_array($_GET);
				$_POST = stripslashes_array($_POST);
				$_REQUEST = stripslashes_array($_REQUEST);
			}
			$_POST=sanitise_array($_POST);

			//if adding a product to the cart
			if(isset($_POST['qty']) && !isset($_POST['save']) && (!is_numeric(trim($_POST['qty']))|| strlen($_POST['qty'])>3)){
				$qty=$_POST['qty']=1;
				$v='999';
				if(isset($max)) $v=$max;
				$error='<p><strong class="eshoperror error">'.sprintf(__('Error: The quantity must contain numbers only, with a maximum of %s.','eshop'),$v).'</strong></p>';
			}

			if(isset($min) && isset($_POST['qty']) && $_POST['qty'] < $min){
				$qty=$_POST['qty']=$min;
				$v='999';
				if(isset($max)) $v=$max;
				$k=$min;
				$enote='<p><strong class="eshoperror error">'.sprintf(__('Warning: The quantity must equal or be greater than %s, with a maximum of %s.','eshop'),$k,$v).'</strong></p>';
			}
			if(isset($max) && isset($_POST['qty']) && $_POST['qty'] > $max){
				$qty=$_POST['qty']=$max;
				$v=$max;
				$k=1;
				if(isset($min)) $k=$min;
				$enote='<p><strong class="eshoperror error">'.sprintf(__('Warning: The quantity must equal or be greater than %s, with a maximum of %s.','eshop'),$k,$v).'</strong></p>';
			}
			if(isset($_POST['postid'])){
				$stkav=get_post_meta( $_POST['postid'], '_eshop_stock',true );
				$eshop_product=maybe_unserialize(get_post_meta( $_POST['postid'], '_eshop_product',true ));
			}
			if(isset($_POST['option']) && !isset($_POST['save'])){
				$edown=$getprice=$option=$_POST['option'];
				if(!isset($_POST['qty'])){
					$enote='<p><strong class="eshoperror error">'.__('Warning: you must supply a quantity.','eshop').'</strong></p>';
				}
				$qty=$_POST['qty'];
				$plcas='';
				if(isset($_POST['pclas']))
					$pclas=$_POST['pclas'];
				$productid=$pid=$_POST['pid'];
				$pname=$_POST['pname'];
				/* if download option then it must be free shipping */
				$postid=$wpdb->escape($_POST['postid']);
				$eshop_product=maybe_unserialize(get_post_meta( $postid, '_eshop_product',true ));
				$dlchk='';
				if(isset($eshop_product['products'][$option]['download']))
					$dlchk=$eshop_product['products'][$option]['download'];
				if($dlchk!='')	$pclas='F';
				$iprice= $eshop_product['products'][$option]['price'];
				if(isset($eshopoptions['sale_prices']) && $eshopoptions['sale_prices'] == 1 
				&& isset($eshopoptions['sale']) && 'yes' == $eshopoptions['sale']  
				&& isset($eshop_product['products'][$option]['saleprice']) && $eshop_product['products'][$option]['saleprice']!=''
				&& isset($eshop_product['sale']) && $eshop_product['sale']=='yes'){
					$iprice=$eshop_product['products'][$option]['saleprice'];
				}
				
				
				if($iprice==''){
					$error='<p><strong class="eshoperror error">'.__('Error: That product is currently not available.','eshop').'</strong></p>';
					$option=$_POST['option']='';
					$qty=$_POST['qty']='';
					$pclas=$_POST['pclas']='';
					$productid=$pid=$_POST['pid']='';
					$pname=$_POST['pname']='';
					$iprice='';
				}
			}

			//unique identifier
			$optset='';
			if(isset($_POST['optset'])){
				$xx=0;
				foreach($_POST['optset'] as $opts){
					$optset.='os'.$xx.implode('os'.$xx,$opts);
					$xx++;
				}
			}
			if(!isset($pid)) $pid='';
			if(!isset($option)) $option='';
			if(!isset($postid)) $postid='';
			$identifier=$pid.$option.$postid.$optset;
			//$needle=array(" ","-","$","\r","\r\n","\n","\\","&","#",";");
			$identifier=md5($identifier);//str_replace($needle,"",$identifier);
			$stocktable=$wpdb->prefix ."eshop_stock";
			if(isset($_SESSION['eshopcart'.$blog_id][$identifier])){
				$testqty=$_SESSION['eshopcart'.$blog_id][$identifier]['qty']+$qty;
				$eshopid=$_SESSION['eshopcart'.$blog_id][$identifier]['postid'];
				$eshop_product=maybe_unserialize(get_post_meta( $postid, '_eshop_product',true ));
				$optnum=$_SESSION['eshopcart'.$blog_id][$identifier]['option'];
				$item=$eshop_product['products'][$_SESSION['eshopcart'.$blog_id][$identifier]['option']]['option'];
				if(isset($min) && $testqty < $min){
					$qty=0;
					$v='999';
					if(isset($max)) $v=$max;
					$k=$min;
					$enote='<p><strong class="eshoperror error">'.sprintf(__('Warning: The quantity must equal or be greater than %s, with a maximum of %s.','eshop'),$k,$v).'</strong></p>';
				}
				if(isset($max) && $testqty > $max){
					$qty=0;
					$v=$max;
					$k=1;
					if(isset($min)) $k=$min;
					$enote='<p><strong class="eshoperror error">'.sprintf(__('Warning: The quantity must equal or be greater than %s, with a maximum of %s.','eshop'),$k,$v).'</strong></p>';
				}
				if('yes' == $eshopoptions['stock_control']){
					$stkqty = $eshop_product['products'][$optnum]['stkqty'];
					//recheck stkqty
					$stktableqty=$wpdb->get_var("SELECT available FROM $stocktable where post_id=$eshopid && option_id=$optnum");
					if(isset($stktableqty) && is_numeric($stktableqty)) $stkqty=$stktableqty;
					if(!ctype_digit(trim($testqty))|| strlen($testqty)>3){
						$error='<p><strong class="eshoperror error">'.__('Error: The quantity must contain numbers only, with a 999 maximum.','eshop').'</strong></p>';
					}elseif('yes' == $eshopoptions['stock_control'] && ($stkav!='1' || $stkqty<$testqty)){
						$error='<p><strong class="eshoperror error">'.__('Error: That quantity is not available for that product.','eshop').'</strong></p>';
					}else{
						$_SESSION['eshopcart'.$blog_id][$identifier]['qty']+=$qty;
					}
				}else{
					$_SESSION['eshopcart'.$blog_id][$identifier]['qty']+=$qty;
				}
				$_SESSION['lastproduct'.$blog_id]=$postid;
				do_action('eshop_product_updated_cart',$_SESSION['eshopcart'.$blog_id][$identifier]);
			}elseif($identifier!=''){
				$weight=0;
				if(isset($_POST['save']) && $_POST['save']=='true'){
					$postid=$_SESSION['eshopcart'.$blog_id][$identifier]['postid'];
					$optid=$_SESSION['eshopcart'.$blog_id][$identifier]['option'];
					$optnum=$optid;
					$testqty=$qty;
				}else{
					$postid=$wpdb->escape($_POST['postid']);
					$optid=$wpdb->escape($_POST['option']);
					$optnum=$optid;
					$_SESSION['eshopcart'.$blog_id][$identifier]['postid']=$postid;
					$testqty=$qty;
				}
				$eshop_product=maybe_unserialize(get_post_meta( $postid, '_eshop_product',true ));
				$item=$eshop_product['products'][$optnum]['option'];
				if('yes' == $eshopoptions['stock_control']){
					$stkqty = $eshop_product['products'][$optnum]['stkqty'];

					//recheck stkqty
					$stktableqty=$wpdb->get_var("SELECT available FROM $stocktable where post_id=$postid && option_id=$optid");
					if(isset($stktableqty) && is_numeric($stktableqty)) $stkqty=$stktableqty;
					if(!ctype_digit(trim($testqty))|| strlen($testqty)>3){
						$error='<p><strong class="eshoperror error">'.__('Error: The quantity must contain numbers only, with a 999 maximum.','eshop').'</strong></p>';
					}elseif('yes' == $eshopoptions['stock_control'] && ($stkav!='1' || $stkqty<$testqty)){
						$error='<p><strong class="eshoperror error">'.__('Error: That quantity is not available for that product.','eshop').'</strong></p>';
						//$_SESSION['eshopcart'.$blog_id][$identifier]['qty']=$stkqty;
					}else{
						$_SESSION['eshopcart'.$blog_id][$identifier]['qty']=$qty;
					}
				}else{
					$_SESSION['eshopcart'.$blog_id][$identifier]['qty']=$qty;
				}	
				$_SESSION['lastproduct'.$blog_id]=$postid;
				$_SESSION['eshopcart'.$blog_id][$identifier]['item']=$item;
				$_SESSION['eshopcart'.$blog_id][$identifier]['option']=stripslashes($option);
				$_SESSION['eshopcart'.$blog_id][$identifier]['pclas']=stripslashes($pclas);
				$_SESSION['eshopcart'.$blog_id][$identifier]['pid']=$pid;
				$_SESSION['eshopcart'.$blog_id][$identifier]['pname']=stripslashes($pname);
				$_SESSION['eshopcart'.$blog_id][$identifier]['price']=$iprice;
				if(isset($_POST['optset'])){
					foreach($_POST['optset'] as $k=>$v)
						$newoptset[]=$v;

					$_SESSION['eshopcart'.$blog_id][$identifier]['optset']=serialize($newoptset);

					$oset=$qb=array();
					$optings=$newoptset;
					//$opttable=$wpdb->prefix.'eshop_option_sets';
					foreach($optings as $foo=>$opst){
						$qb[]="id=$opst[id]";
					}
					$qbs = implode(" OR ", $qb);
					$otable=$wpdb->prefix.'eshop_option_sets';
					$orowres=$wpdb->get_results("select weight from $otable where $qbs ORDER BY id ASC");
					$x=0;
					foreach($orowres as $orow){
						$weight+=$orow->weight;
						$x++;
					}

				}
				//weights?
				if(isset($eshop_product['products'][$option]['weight']))
					$weight+=$eshop_product['products'][$option]['weight'];
				$_SESSION['eshopcart'.$blog_id][$identifier]['weight']=$weight;
				if(isset($error)){
					unset($_SESSION['eshopcart'.$blog_id][$identifier]);
				}
				do_action('eshop_product_added_to_cart',$_SESSION['eshopcart'.$blog_id][$identifier]);

			}
		}
		if(!isset($error)){

			//save? not sure why I used that, but its working so why make trouble for myself.
			if(isset($_POST['save'])){
				$save=$_POST['save'];
			}
			
			//this bit is possibly not required
			if(isset($productid)){
				//new item selected ******* may need checking
				$_SESSION['final_price'.$blog_id] = calculate_price();
				$_SESSION['items'.$blog_id] = calculate_items();
			}
			
			if(isset($_POST['eshopdeleteitem'])){
				foreach($_POST['eshopdeleteitem'] as $chkey=>$chkval){
					$tochkkey=$chkey;
					$tochkqty=$_SESSION['eshopcart'.$blog_id][$chkey]['qty'];
				}
			}
			if(isset($eshopoptions['min_qty']) && $eshopoptions['min_qty']!='') 
				$min=$eshopoptions['min_qty'];
			if(isset($eshopoptions['max_qty']) && $eshopoptions['max_qty']!='') 
				$max=$eshopoptions['max_qty'];

			if(isset($min) && isset($_POST['qty']) && $_POST['qty'] < $min){
				$qty=$_POST['qty']=$min;
				$v='999';
				if(isset($max)) $v=$max;
				$k=$min;
				$enote='<p><strong class="eshoperror error">'.sprintf(__('Warning: The quantity must equal or be greater than %s, with a maximum of %s.','eshop'),$k,$v).'</strong></p>';
			}
			if(isset($max) && isset($_POST['qty']) && $_POST['qty'] > $max){
				$qty=$_POST['qty']=$max;
				$v=$max;
				$k=1;
				if(isset($min)) $k=$min;
				$enote='<p><strong class="eshoperror error">'.sprintf(__('Warning: The quantity must equal or be greater than %s, with a maximum of %s.','eshop'),$k,$v).'</strong></p>';
			}
			//update products in the cart
			if(isset($_POST['save']) && $_POST['save']=='true' && isset($_SESSION['eshopcart'.$blog_id])){
				$eshopcartarray=$_SESSION['eshopcart'.$blog_id];
				foreach ($eshopcartarray as $productid => $opt){
					$needle=array(" ",".");
					$sessproductid=str_replace($needle,"_",$productid);
					foreach ($_POST as $key => $value){
						if($key==$sessproductid){
							foreach ($value as $notused => $qty){
								if(isset($tochkkey) && $tochkkey==$key && $tochkqty==$qty){
								$qty=0;
								}
								if($qty=="0"){							
									unset($_SESSION['eshopcart'.$blog_id][$productid]);
								}else{
									$postid=$eshopid=$_SESSION['eshopcart'.$blog_id][$productid]['postid'];
									$eshop_product=maybe_unserialize(get_post_meta( $postid, '_eshop_product',true ));
									$optnum=$_SESSION['eshopcart'.$blog_id][$productid]['option'];
									if(isset($eshop_product['products'][$_SESSION['eshopcart'.$blog_id][$productid]['option']]['stkqty']))
										$stkqty = $eshop_product['products'][$_SESSION['eshopcart'.$blog_id][$productid]['option']]['stkqty'];
									//recheck stkqty
									$stocktable=$wpdb->prefix ."eshop_stock";
									$stktableqty=$wpdb->get_var("SELECT available FROM $stocktable where post_id=$eshopid AND option_id=$optnum");
									if(isset($stktableqty) && is_numeric($stktableqty)) $stkqty=$stktableqty;
									if(!ctype_digit(trim($qty))|| strlen($qty)>3){
										$v='999';
										if(isset($max)) $v=$max;
										$error='<p><strong class="eshoperror error">'.sprintf(__('Error: The quantity must contain numbers only, with a maximum of %s.','eshop'),$v).'</strong></p>';
									}elseif('yes' == $eshopoptions['stock_control'] &&  $stkqty<$qty){
										$error='<p><strong class="eshoperror error">'.__('Error: That quantity is not available for that product.','eshop').'</strong></p>';
									}elseif(isset($min) && isset($qty) && $qty < $min){
										$qty=$min;
										$v='999';
										if(isset($max)) $v=$max;
										$k=$min;
										$enote='<p><strong class="eshoperror error">'.sprintf(__('Warning: The quantity must equal or be greater than %s, with a maximum of %s.','eshop'),$k,$v).'</strong></p>';
									}elseif(isset($max) && isset($qty) && $qty > $max){
										$qty=$max;
										$v=$max;
										$k=1;
										if(isset($min)) $k=$min;
										$enote='<p><strong class="eshoperror error">'.sprintf(__('Warning: The quantity must equal or be greater than %s, with a maximum of %s.','eshop'),$k,$v).'</strong></p>';
									}else{
										$_SESSION['eshopcart'.$blog_id][$productid]['qty'] =$qty;
									}
								}
							}
						}
					}
				}
				$_SESSION['final_price'.$blog_id] = calculate_price();
				//$_SESSION['items'.$blog_id] = calculate_items();
			}
		}
		//any errors will print here.
		if(isset($error)){
			$_SESSION['eshopcart'.$blog_id]['error']= $error;
		}
		if(isset($enote)){
			$_SESSION['eshopcart'.$blog_id]['enote']= $enote;
		}
		if(isset($_SESSION['eshopcart'.$blog_id]) && sizeof($_SESSION['eshopcart'.$blog_id])=='0'){
			unset($_SESSION['eshopcart'.$blog_id]);
			unset($_SESSION['final_price'.$blog_id]);
			unset($_SESSION['items'.$blog_id]);
			
		}
	}
}
if (!function_exists('eshop_mg_process_product')) {
	function eshop_mg_process_product($txn_id,$checked,$status='Completed'){
		global $wpdb;
		//tables
		$detailstable=$wpdb->prefix.'eshop_orders';
		$itemstable=$wpdb->prefix ."eshop_order_items";
		$stocktable=$wpdb->prefix ."eshop_stock";
		$mtable=$wpdb->prefix.'postmeta';
		$producttable=$wpdb->prefix.'eshop_downloads';
		$wpdb->query("UPDATE $detailstable set status='$status',transid='$txn_id' where checkid='$checked'");
		
		//product stock control updater & stats

		$query=$wpdb->get_results("SELECT item_qty,post_id,option_id,item_id,down_id FROM $itemstable WHERE checkid='$checked' AND post_id!='0'");
		foreach($query as $row){
			$pid=$row->post_id;
			do_action('eshop_sold_product',$pid);
			$uqty=$row->item_qty;
			$optid=$row->option_id;
			////test downloads
			//check if downloadable product
			$fileid=$row->down_id;
			if($fileid!=0){
				$grabit=$wpdb->get_row("SELECT title, files FROM $producttable where id='$fileid'");
				//add 1 to number of purchases here (duplication but left in)
				$wpdb->query("UPDATE $producttable SET purchases=purchases+$uqty where title='$grabit->title' && files='$grabit->files' limit 1");
				$chkit= $wpdb->get_var("SELECT purchases FROM $stocktable WHERE post_id='$pid'");
				if($chkit!=''){	
					$wpdb->query("UPDATE $stocktable set purchases=purchases+$uqty where post_id=$pid && option_id=$optid");
				}else{
					$wpdb->query("INSERT INTO $stocktable (available, purchases, post_id, option_id) VALUES ('0','$uqty','$pid', '$optid')");
				}
			}else{
				$chkit= $wpdb->get_var("SELECT purchases FROM $stocktable WHERE post_id='$pid' && option_id=$optid");
				if($chkit!=''){						
					$wpdb->query("UPDATE $stocktable set available=available-$uqty, purchases=purchases+$uqty where post_id=$pid && option_id=$optid");
				}else{
					$wpdb->query("INSERT INTO $stocktable (available, purchases, post_id, option_id) VALUES ('0','$uqty','$pid', '$optid')");
				}
			}

		}
	}
}
if (!function_exists('eshop_contains')) {
    /**
     * Return true if one string can be found in another
     * as used above
     * @param $haystack the string to search *in*
     * @param $needle the string to search *for*
     */
    function eshop_contains($haystack, $needle){
        $pos = strpos($haystack, $needle);
        
        if ($pos === false) {
            return false;
        }
        else {
            return true;
        }
    }   
}
if (!function_exists('eshop_send_customer_email')) {
    function eshop_send_customer_email($checked, $mg_id){
    	global $wpdb;
    	//runcode - return false to stop the rest from running.
    	//checked is reference for db, mg_id is the email template to use
    	$runcode=true;
    	$runcode=apply_filters('eshop_send_customer_email_replace',$runcode,$checked, $mg_id);

		//this is an email sent to the customer:
		//first extract the order details
		$array=eshop_rtn_order_details($checked);
		$etable=$wpdb->prefix.'eshop_emails';

		//grab the template
		$thisemail=$wpdb->get_row("SELECT emailSubject,emailContent FROM ".$etable." WHERE (id='".$mg_id."' AND emailUse='1') OR id='1'  order by id DESC limit 1");
		$this_email = stripslashes($thisemail->emailContent);

		// START SUBST
		$csubject=stripslashes($thisemail->emailSubject);
		$this_email = eshop_email_parse($this_email,$array);

		//try and decode various bits
		$this_email=html_entity_decode($this_email,ENT_QUOTES);

		$headers=eshop_from_address();
		if($runcode==true){
			wp_mail($array['eemail'], $csubject, $this_email,$headers);
		}
		do_action('eshop_send_customer_email', $csubject, $this_email, $headers, $array);
		//affiliate
		if($array['affiliate']!=''){
			// to allow for the removal of shipping vlaue from the order. total is sent by default, shipping can be removed.
			$sale_amt=apply_filters('eShop_aff_order_total',$array['total'],$array['shipping_charge']);
			//for affiliates.
			$affcheck=apply_filters('eShop_aff_order_check', true, $array);
			if($affcheck == true)
				do_action('eShop_process_aff_commission', array("id" =>$array['affiliate'],"sale_amt"=>$sale_amt, "txn_id"=>$array['transid'], "buyer_email"=>$array['eemail']));
		}
		//this is fired on successful purchase, so might as well have this action here
		do_action('eshop_on_success',$checked);
	}
}
if (!function_exists('eshop_test_or_live')) {
	function eshop_test_or_live(){
		global $eshopoptions, $wp_admin_bar;
		if ( !is_object( $wp_admin_bar ) ) {
			if($eshopoptions['status']=='testing'){
				if(is_user_logged_in() && current_user_can('eShop_admin')){
					add_action('wp_head','eshop_test_mode');
					add_action('wp_footer','eshop_test_mode_text');
				}
			}
		} else {
			if(is_user_logged_in() && current_user_can('eShop_admin')){
				add_action( 'wp_before_admin_bar_render', 'eshop_admin_bar_menu', 150 );
			}
		}

	}
}
if (!function_exists('eshop_admin_bar_menu')) {
	function eshop_admin_bar_menu() {
		global $wp_admin_bar;
		$eshopoptions = get_option('eshop_plugin_settings');
		if ( !is_object( $wp_admin_bar ) )
			return false;
		if($eshopoptions['status']=='testing')
			$title=__('eShop Test Mode','eshop');
		else
			$title=__('eShop is Live','eshop');
		
		/* Add the Blog Info menu */
		$wp_admin_bar->add_menu( array( 'id' => 'eshopadminbar', 'title' => $title, 'href' => get_option( 'siteurl' ).'/wp-admin/options-general.php?page=eshop-settings.php' ) );
	}
}

if (!function_exists('eshop_test_mode_text')) {
	function eshop_test_mode_text(){
		echo '<div id="eshoptestmode" title="'.__("This note is only visible to eShop Admins",'eshop').'">'.__('Admin note: eShop is currently in test mode, and only admins can place orders.','eshop').'</div>';
		return;
	}
}

if (!function_exists('eshop_test_mode')) {
	function eshop_test_mode(){
	?>
<style type="text/css">
#eshoptestmode{
	padding:5px 0;
	text-align:center;
	width:100%;
	display:block;
	color:#FFFFFF;
	position:absolute;
	top:0;
	left:0;
	background-color:#800;
	filter:alpha(opacity=80);
	-moz-opacity:0.8;
	-khtml-opacity: 0.8;
	opacity: 0.8;
	font-weight:bold;
}
</style>
<?php
	return;
	}
}
if (!function_exists('eshop_read_filesize')){
	function eshop_read_filesize($size){
	  if ($size == NULL){
		 return "error";
	  }
	  $i=0;
	  $iec = array("Bytes", "KB", "MB", "GB");
	  while (($size/1024)>1) {
		 $size=$size/1024;
		 $i++;
	  }
	  if($iec[$i]=='Bytes'){
		return '&lt; 1Kb';
	  }else{
		return substr($size,0,strpos($size,'.')+3).$iec[$i];
	  }
	}
}
if (!function_exists('get_eshop_product')) {
	function get_eshop_product($id=''){
		/*
		attempt to allow people to access product info correctly - currently not showing absolutely 
		everything but may suffice.
		*/
		global $wpdb, $post, $wp_query,$eshopoptions;
		if($id=='' && isset($post->ID))
			$id=$post->ID;
		//nothing to return, then return
		if($id=='')
			return;

		$producttable = $wpdb->prefix ."eshop_downloads";
		$eshop_product=maybe_unserialize(get_post_meta($id, '_eshop_product','true'));
		$eshopdlavail = $wpdb->get_var("SELECT COUNT(id) FROM $producttable WHERE id > 0");
		$numoptions=$eshopoptions['options_num'];
		$stocktable=$wpdb->prefix ."eshop_stock";

		if('yes' == $eshopoptions['stock_control']){
			$stkq=$wpdb->get_results("SELECT option_id, available from $stocktable where post_id=$id");
			foreach($stkq as $thisstk){
				if($thisstk->available<0) $thisstk->available=0;
				$stkarr[$thisstk->option_id]=$thisstk->available;
			}
		}
		for($i=1;$i<=$numoptions;$i++){
			if(isset($eshop_product['products'][$i]) && is_array($eshop_product['products'][$i])){
				if(isset($eshop_product['products'][$i]['stkqty']) && $eshop_product['products'][$i]['stkqty']!='' && 'yes' == $eshopoptions['stock_control'])
					$eshop_product['products'][$i]['stkqty']=$stkarr[$i];
				if($eshop_product['products'][$i]['option']=='' && $eshop_product['products'][$i]['price']=='')
					unset($eshop_product['products'][$i]);
				if(isset($eshop_product['products'][$i]['download']) && $eshop_product['products'][$i]['download']=='')
					unset($eshop_product['products'][$i]['download']);
			}
		}
		
		if(isset($eshop_product['optset'])){
			$osets=$eshop_product['optset'];
			if(is_array($osets)){
				$opttable=$wpdb->prefix.'eshop_option_names';
				$optsettable=$wpdb->prefix.'eshop_option_sets';
				foreach($osets as $optid){
					$myrowres=$wpdb->get_results($wpdb->prepare("select name as optname, price,weight,id from $optsettable where optid='%d' ORDER by id ASC",$optid));
					$egrab=$wpdb->get_row($wpdb->prepare("select * from $opttable where optid='%d' LIMIT 1",$optid));
					if(isset($egrab->name)){
						$eshop_product['optionset'][$optid]['optname']=$egrab->name;
						foreach($myrowres as $myrow){
							if($myrow->weight=='')
								$myrow->weight='0';
							$eshop_product['optionset'][$optid][$myrow->id]['name']=$myrow->optname;
							$eshop_product['optionset'][$optid][$myrow->id]['price']=$myrow->price;
							$eshop_product['optionset'][$optid][$myrow->id]['weight']=$myrow->weight;
						}

					}
				}
			}
			unset($eshop_product['optset']);
		}
		return $eshop_product;
	}
}

if (!function_exists('subval_sort')){
	function subval_sort($a,$subkey) {
		foreach($a as $k=>$v) {
			$b[$k] = strtolower($v[$subkey]);
		}
		asort($b);
		foreach($b as $key=>$val) {
			$c[] = $a[$key];
		}
		return $c;
	}
}

if (!function_exists('eshop_parse_optsets')){
	function eshop_parse_optsets($data){
		global $wpdb;
		$opt['optset']=$data['optset'];
		$addoprice=$data['addoprice'];
		$oset=$qb=array();
		$optings=unserialize($opt['optset']);
		//then sort it how we want.
		$optings=subval_sort($optings,'id'); 
		$c=0;
		if(isset($newoptings)) unset($newoptings);

		foreach($optings as $foo=>$opst){
			if(!isset($opst['type']) 
			|| (($opst['type']=='2' || $opst['type']=='3') && (isset($opst['text']) && trim($opst['text'])!=''))
			){
				$newoptings[]=$optings[$c];
				$qb[]="id=$opst[id]";

			}
			$c++;
		}
		if(isset($newoptings)){
			$qbs = implode(" OR ", $qb);
			$otable=$wpdb->prefix.'eshop_option_sets';
			$otablename=$wpdb->prefix.'eshop_option_names';
			$orowres=$wpdb->get_results("select o.name, o.price, o.id, t.type,t.name as oname from $otable as o, $otablename as t where ($qbs) && o.optid=t.optid ORDER BY id ASC");
			$x=0;
			foreach($orowres as $orow){
				if(isset($newoptings[$x]['id']) && $orow->id==$newoptings[$x]['id']){
					if((isset($newoptings[$x]['type']) && isset($newoptings[$x]['text']) && trim($newoptings[$x]['text'])!='' && ($newoptings[$x]['type']=='2' || $newoptings[$x]['type']=='3'))){
						$oset[]='<span class="eshopoptset"><span class="eshopoptname">'.stripslashes($orow->name)."</span>\n".'<span class="eshoptext">'.stripslashes($newoptings[$x]['text']).'</span></span>';
					}elseif(($orow->type=='2' || $orow->type=='3') && !isset($newoptings[$x]['text']))
						$xxxx='';
					else
						$oset[]='<span class="eshopoptset"><span class="eshopoptname">'.stripslashes($orow->oname)."</span>\n".'<span class="eshoptext">'.stripslashes($orow->name).'</span></span>';
					$addoprice=$addoprice+$orow->price;
					$x++;
				}
			}
			$optset="\n".implode("\n",$oset);
		}else{
			$optset='';
		}
		$data['optset']=$optset;
		$data['addoprice']=$addoprice;
		return $data;
	}
}
if (!function_exists('eshop_filesize')){
	function eshop_filesize($file){
		$size = 0;
		$eshopdldir = eshop_download_directory();
		list($title, $ext) = explode('.', $file);
		if(is_dir($eshopdldir.$title)){
			foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($eshopdldir.$title)) as $filed){
			  $size+=$filed->getSize();
			}
		}else{
			$size = @filesize($eshopdldir.$file);
		}
		return $size;
	}
}
if (!function_exists('eshop_real_date')){
	function eshop_real_date($custom){
		//custom is the date, but stored with no - or :
		$cyear=substr($custom, 0, 4);
		$cmonth=substr($custom, 4, 2);
		$cday=substr($custom, 6, 2);
		$chours=substr($custom, 8, 2);
		$cminutes=substr($custom, 10, 2);
		//rebuild the date
		$realdate=$cyear.'-'.$cmonth.'-'.$cday.' '.$chours.':'.$cminutes.':00';
		$newdate=trim(get_date_from_gmt($realdate),':00');
		return apply_filters('eshop_real_date',$newdate,$custom);
	}
}
?>