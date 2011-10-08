<?php
global $eshopoptions;
if (isset($eshopoptions['users']) && $eshopoptions['users']=='yes'){
	//only add if set.
	add_action( 'show_user_profile', 'eshop_user_profile_fields' );
	add_action( 'edit_user_profile', 'eshop_user_profile_fields' );
	add_action( 'personal_options_update', 'save_eshop_user_profile_fields' );
	add_action( 'edit_user_profile_update', 'save_eshop_user_profile_fields' );
}
function eshop_user_profile_fields( $user ) { 
	$eshopuser=stripslashes_deep(get_the_author_meta( 'eshop', $user->ID )) ;
	global $wpdb,$eshopoptions;
		$table=$wpdb->prefix.'eshop_states';
		$getstate=$eshopoptions['shipping_state'];
		if($eshopoptions['show_allstates'] != '1'){
			$stateList=$wpdb->get_results("SELECT id,code,stateName FROM $table WHERE list='$getstate' ORDER BY stateName",ARRAY_A);
		}else{
			$stateList=$wpdb->get_results("SELECT id,code,stateName,list FROM $table ORDER BY list,stateName",ARRAY_A);
		}
		if(sizeof($stateList)>0){
			$estates ='<select class="med pointer" name="eshopuser[state]" id="state">';
			$estates .='<option value="" selected="selected">'.__('Please Select','eshop').'</option>';
			$estates .='<option value="">'.__('not applicable','eshop').'</option>';
			foreach($stateList as $code => $value){
				if(isset($value['list'])) $li=$value['list'];
				else $li='1';
				$eshopstatelist[$li][$value['id']]=$value['stateName'];
			}
			$tablec=$wpdb->prefix.'eshop_countries';
			foreach($eshopstatelist as $egroup =>$value){
				$eshopcname=$wpdb->get_var("SELECT country FROM $tablec where code='$egroup' limit 1");

				$estates .='<optgroup label="'.$eshopcname.'">'."\n";
				foreach($value as $code =>$stateName){
					$stateName=htmlspecialchars($stateName);
					if (isset($eshopuser['state']) && $eshopuser['state'] == $code){
						$estates.= '<option value="'.$code.'" selected="selected">'.$stateName."</option>\n";
					}else{
						$estates.='<option value="'.$code.'">'.$stateName."</option>\n";
					}
				}
				$estates .="</optgroup>\n";
			}
			$estates.= "</select></label><br /></span>\n";
		}else{
			$estates .='<input type="hidden" name="eshopuser[state]" value="" />';
		}
		if (isset($eshopuser['altstate'])) 
			$altstate = esc_attr( $eshopuser['altstate'] );
		else
			$altstate='';
		$estates .= '<span class="altstate"><label for="altstate">'.__('State/County/Province <small>if not listed above</small>','eshop').' <br />
				  <input type="text" name="eshopuser[altstate]" value="'.$altstate.'" id="altstate"  class="regular-text" /></label><br /></span>';

?>

<h3><?php _e("Shopping Cart information", "eshop"); ?></h3>
<p><?php _e('Filling in fields here will save you having to enter them each time you purchase something from our shop.','eshop'); ?></p>
<table class="form-table">
<tr>
<th><label for="company"><?php _e("Company",'eshop'); ?></label></th>
<td>
<input type="text" name="eshopuser[company]" id="company" value="<?php if (isset($eshopuser['company'])) echo esc_attr( $eshopuser['company'] ); ?>" class="regular-text" /><br />
<span class="description"><?php _e("Business users only.",'eshop'); ?></span></td>
</tr>
<tr>
<th><label for="phone"><?php _e("Phone",'eshop'); ?></label></th>
<td>
<input type="text" name="eshopuser[phone]" id="phone" value="<?php if (isset($eshopuser['phone'])) echo esc_attr( $eshopuser['phone'] ); ?>" class="regular-text" /><br />
</td>
</tr>
<tr>
<th><label for="address1"><?php _e("Address",'eshop'); ?></label></th>
<td>
<input type="text" name="eshopuser[address1]" id="address1" value="<?php if (isset($eshopuser['address1'])) echo esc_attr( $eshopuser['address1'] ); ?>" class="regular-text" /><br />
</td>
</tr>
<tr>
<th><label for="address2"><?php _e("Address (continued)",'eshop'); ?></label></th>
<td>
<input type="text" name="eshopuser[address2]" id="address2" value="<?php if (isset($eshopuser['address2'])) echo esc_attr( $eshopuser['address2'] ); ?>" class="regular-text" /><br />
</td>
</tr>
<tr>
<th><label for="city"><?php _e("City",'eshop'); ?></label></th>
<td>
<input type="text" name="eshopuser[city]" id="city" value="<?php if (isset($eshopuser['city'])) echo esc_attr( $eshopuser['city'] ); ?>" class="regular-text" /><br />
</td>
</tr>
<tr>
<th><label for="province"><?php _e("State/County/Province",'eshop'); ?></label></th>
<td>
<?php echo $estates; ?>
</td>
</tr>
<tr>
<th><label for="zip"><?php _e("Zip/Post code",'eshop'); ?></label></th>
<td>
<input type="text" name="eshopuser[zip]" id="zip" value="<?php if (isset($eshopuser['zip'])) echo esc_attr( $eshopuser['zip'] ); ?>" class="short-text" /><br />
</td>
</tr>
<tr>
<th><label for="country"><?php _e('Country','eshop'); ?></label></th>
<td><select class="med pointer" name="eshopuser[country]" id="country">
<?php
// country list from db
$tablec=$wpdb->prefix.'eshop_countries';
$List=$wpdb->get_results("SELECT code,country FROM $tablec GROUP BY list,country",ARRAY_A);
foreach($List as $key=>$value){
	$k=$value['code'];
	$v=$value['country'];
	$countryList[$k]=$v;
}
$ecountries ='<option value="" selected="selected">'.__('Select your Country','eshop').'</option>';
foreach($countryList as $code => $label){
	$label=htmlspecialchars($label);
	if (isset($eshopuser['country']) && $eshopuser['country'] == $code){
		$ecountries.= "<option value=\"$code\" selected=\"selected\">$label</option>\n";
	}else{
		$ecountries.="<option value=\"$code\">$label</option>";
	}
}
echo $ecountries;
?>
</select>
</td>
</tr>
</table>
<?php 
}

function save_eshop_user_profile_fields( $user_id ) {
 if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
 update_user_meta( $user_id, 'eshop', $_POST['eshopuser'] );
}
?>