<?php
/*  based on:
 * PHP Paypal IPN Integration Class Demonstration File
 *  4.16.2005 - Micah Carrick, email@micahcarrick.com
*/
global $wpdb,$wp_query,$wp_rewrite,$blog_id,$eshopoptions;
$detailstable=$wpdb->prefix.'eshop_orders';
$derror=__('There appears to have been an error, please contact the site admin','eshop');

//sanitise
include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
$_POST=sanitise_array($_POST);

include_once (WP_PLUGIN_DIR.'/eshop/authorizenet/index.php');
// Setup class
require_once(WP_PLUGIN_DIR.'/eshop/authorizenet/authorizenet.class.php');  // include the class file
$p = new authorizenet_class;             // initiate an instance of the class

if($eshopoptions['status']=='live'){
	$p->authorizenet_url = 'https://secure.authorize.net/gateway/transact.dll';     // authorizenet url
}else{
	$p->authorizenet_url = 'https://secure.authorize.net/gateway/transact.dll';   // testing authorizenet url
}
//only reqd for the developer
$authorizenet = $eshopoptions['authorizenet']; 
$authemail=$authorizenet['email'];
if(isset($authorizenet['developer']) && $authorizenet['developer']=='1')
	$p->authorizenet_url = 'https://test.authorize.net/gateway/transact.dll';   // devloper testing authorizenet url

$this_script = site_url();
if($eshopoptions['checkout']!=''){
	$p->autoredirect=add_query_arg('eshopaction','redirect',get_permalink($eshopoptions['checkout']));
}else{
	die('<p>'.$derror.'</p>');
}

// if there is no action variable, set the default action of 'process'
if(!isset($wp_query->query_vars['eshopaction']))
	$eshopaction='process';
else
	$eshopaction=$wp_query->query_vars['eshopaction'];

switch ($eshopaction) {
    case 'redirect':
    	//auto-redirect bits
		header('Cache-Control: no-cache, no-store, must-revalidate'); //HTTP/1.1
		header('Expires: Sun, 01 Jul 2005 00:00:00 GMT');
		header('Pragma: no-cache'); //HTTP/1.0
		
		//enters all the data into the database
		$authorizenet = $eshopoptions['authorizenet']; 
		$Key=$authorizenet['key'];
		$LID=$authorizenet['id'];
		$secret=$authorizenet['secret'];
		$description=$authorizenet['desc'];
		// a sequence number is randomly generated
		$sequence	= rand(1, 1000);
		// a timestamp is generated
		$timestamp	= time ();
		$pvalue=str_replace(',','',$_POST['amount']);
		if(isset($_SESSION['shipping'.$blog_id]['tax'])) $pvalue += $_SESSION['shipping'.$blog_id]['tax'];
		if(isset($_POST['tax'])) $pvalue += str_replace(',','',$_POST['tax']);
		//$pvalue = number_format($pvalue,2);
		$pvalue = number_format($pvalue, 2, '.', '');
		$subinv=uniqid(rand()).'eShop';
		$invoice=substr($subinv,0,20);
		
		if( phpversion() >= '5.1.2' ){
			$fingerprint = hash_hmac("md5", $LID . "^" . $sequence . "^" . $timestamp . "^" . $pvalue . "^", $Key); 
		}else{ 
			$fingerprint = bin2hex(mhash(MHASH_MD5, $LID . "^" . $sequence . "^" . $timestamp . "^" . $pvalue . "^", $Key)); 
		}

		$md5hash=$secret.$LID.$invoice.$pvalue;
		$checkid=md5($md5hash);
		if(isset($_COOKIE['ap_id'])) $_POST['affiliate'] = $_COOKIE['ap_id'];
		orderhandle($_POST,$checkid);
		if(isset($_COOKIE['ap_id'])) unset($_POST['affiliate']);
		$p = new authorizenet_class; 
		$p->add_field('x_login',$LID);
		$p->add_field('x_amount',$pvalue);
		$p->add_field('x_description',$description);
		$p->add_field('x_invoice_num',$invoice);
		$p->add_field('x_fp_sequence',$sequence);
		$p->add_field('x_fp_timestamp',$timestamp);
		$p->add_field('x_fp_hash',$fingerprint);
		if($eshopoptions['status']=='live'){
			$p->authorizenet_url = 'https://secure.authorize.net/gateway/transact.dll';     // authorizenet url
		}else{
			$p->authorizenet_url = 'https://secure.authorize.net/gateway/transact.dll';   // testing authorizenet url
		}
		//only reqd for the developer
		$authorizenet = $eshopoptions['authorizenet']; 
		$authemail=$authorizenet['email'];
		if(isset($authorizenet['developer']) && $authorizenet['developer']=='1'){
			$p->authorizenet_url = 'https://test.authorize.net/gateway/transact.dll';   // devloper testing authorizenet url
		}
		$echoit.=$p->eshop_submit_authorizenet_post($_POST);
		//$p->dump_fields();      // for debugging, output a table of all the fields
		break;
        
   case 'process':      // Process and order...
	
		// There should be no output at this point.  To process the POST data,
		// the submit_authorizenet_post() function will output all the HTML tags which
		// contains a FORM which is submited instantaneously using the BODY onload
		// attribute.  In other words, don't echo or printf anything when you're
		// going to be calling the submit_authorizenet_post() function.
		
		// This is where you would have your form validation  and all that jazz.
		// You would take your POST vars and load them into the class like below,
		// only using the POST values instead of constant string expressions.

		// For example, after ensureing all the POST variables from your custom
		// order form are valid, you might have:
		//
		// $p->add_field('first_name', $_POST['first_name']);
		// $p->add_field('last_name', $_POST['last_name']);
      
      /****** The order has already gone into the database at this point ******/
      
		//goes direct to this script as nothing needs showing on screen.
		if($eshopoptions['cart_success']!=''){
			$ilink=add_query_arg('eshopaction','authorizenetipn',get_permalink($eshopoptions['cart_success']));
		}else{
			die('<p>'.$derror.'</p>');
		}
		$p->add_field('x_relay_url', $ilink);

		$p->add_field('shipping_1', eshopShipTaxAmt());
		$sttable=$wpdb->prefix.'eshop_states';
		$getstate=$eshopoptions['shipping_state'];
		if($eshopoptions['show_allstates'] != '1'){
			$stateList=$wpdb->get_results("SELECT id,code,stateName FROM $sttable WHERE list='$getstate' ORDER BY stateName",ARRAY_A);
		}else{
			$stateList=$wpdb->get_results("SELECT id,code,stateName,list FROM $sttable ORDER BY list,stateName",ARRAY_A);
		}
		foreach($stateList as $code => $value){
			$eshopstatelist[$value['id']]=$value['code'];
		}
		foreach($_POST as $name=>$value){
			//have to do a discount code check here - otherwise things just don't work - but fine for free shipping codes
			if(strstr($name,'amount_')){
				if(isset($_SESSION['eshop_discount'.$blog_id]) && eshop_discount_codes_check()){
					$chkcode=valid_eshop_discount_code($_SESSION['eshop_discount'.$blog_id]);
					if($chkcode && apply_eshop_discount_code('discount')>0){
						$discount=apply_eshop_discount_code('discount')/100;
						$value = number_format(round($value-($value * $discount), 2),2);
						$vset='yes';
					}
				}
				if(is_discountable(calculate_total())!=0 && !isset($vset)){
					$discount=is_discountable(calculate_total())/100;
					$value = number_format(round($value-($value * $discount), 2),2);
				}
			}
			if(sizeof($stateList)>0 && ($name=='state' || $name=='ship_state')){
				if($value!='')
					$value=$eshopstatelist[$value];
			}
			$p->add_field($name, $value);
		}
	
		if($eshopoptions['status']!='live' && is_user_logged_in() &&  current_user_can('eShop_admin')||$eshopoptions['status']=='live'){
			$echoit .= $p->submit_authorizenet_post(); // submit the fields to authorizenet
    		//$p->dump_fields();      // for debugging, output a table of all the fields
    	}
      	break;
   case 'authorizenetipn':          // authorizenet is calling page for IPN validation...

		// It's important to remember that authorizenet calling this script.  There
		// is no output here.  This is where you validate the IPN data and if it's
		// valid, update your database to signify that the user has payed.  If
		// you try and use an echo or printf function here it's not going to do you
		// a bit of good.  This is on the "backend".  That is why, by default, the
		// class logs all IPN data to a text file.
		// the loggin to a text file isn't working, so we have coded an email to be sent instead.

			// Payment has been received and IPN is verified.  This is where you
			// update your database to activate or process the order, or setup
			// the database with the user's order details, email an administrator,
			// etc.  You can access a slew of information via the ipn_data() array.

			// Check the authorizenet documentation for specifics on what information
			// is available in the IPN POST variables.  Basically, all the POST vars
			// which authorizenet sends, which we send back for validation, are now stored
			// in the ipn_data() array.
 		/*
		updating db.
		*/
		$ps = new authorizenet_class; // initiate an instance of the class

		foreach ($_REQUEST as $field=>$value) { 
		  $ps->ipn_data["$field"] = $value;
		}

		$authorizenet = $eshopoptions['authorizenet']; 
		$LID=$authorizenet['id'];
		$Key=$authorizenet['key'];
		$secret=$authorizenet['secret'];
		$transid=$ps->ipn_data['x_trans_id'];
		$amount=$ps->ipn_data["x_amount"];
		$invoice=$ps->ipn_data["x_invoice_num"];
		$md5hash=$secret.$LID.$invoice.$amount;
		$checked=md5($md5hash);
		$ps->ipn_data["mycheckmd5"]=strtoupper(md5($secret.$LID.$transid.$amount));
		$ps->ipn_data["mycheckedid"]=$checked;
		if('1' == $_REQUEST["x_response_code"] && $ps->ipn_data["mycheckmd5"]==$ps->ipn_data["x_MD5_Hash"]){
			if($eshopoptions['status']=='live'){
				$txn_id = $wpdb->escape($ps->ipn_data['x_trans_id']);
				$subject = __('authorizenet IPN -','eshop');
			}else{
				$txn_id = __("TEST-",'eshop').$wpdb->escape($ps->ipn_data['x_trans_id']);
				$subject = __('Testing: authorizenet IPN - ','eshop');
			}
			//check txn_id is unique
			$astatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' limit 1");
			if(!isset($_REQUEST['x_test_request'])){
				$checktrans=$wpdb->get_results("select transid from $detailstable");
				foreach($checktrans as $trans){
					if(strpos($trans->transid, $ps->ipn_data['x_trans_id'])===true){
						$astatus='Failed';
						$txn_id = __("Duplicated-",'eshop').$wpdb->escape($ps->ipn_data['x_trans_id']);
					}
				}
			}
			//the magic bit  + creating the subject for our email.
			if($astatus=='Pending'){
				$subject .=__("Completed Payment",'eshop');	
				$ok='yes';
				eshop_mg_process_product($txn_id,$checked);
				
			}else{
				//cannot print anything out at this stage. so authorizenet users won't see the download form.
				//then it must be a success
				//close session here.
				$_SESSION = array();
				session_destroy();
				$ok='no';
			}
			// email to business a complete copy of the notification from authorizenet to keep!!!!!

			$subject .=" Ref:".$ps->ipn_data['x_trans_id'];
			$array=eshop_rtn_order_details($checked);
			$ps->ipn_data['payer_email']=$array['ename'].' '.$array['eemail'].' ';
			 $body =  __("An instant payment notification was received",'eshop')."\n";
			 $body .= "\n".__("from ",'eshop').$ps->ipn_data['payer_email'].__(" on ",'eshop').date('m/d/Y');
			 $body .= __(" at ",'eshop').date('g:i A')."\n\n".__('Details','eshop').":\n";
			 if(isset($array['dbid']))
			 	$body .= get_option( 'siteurl' ).'/wp-admin/admin.php?page=eshop-orders.php&view='.$array['dbid']."&eshop\n";

			 foreach ($ps->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
			 $body .= "\n\n".__('Regards, Your friendly automated response.','eshop')."\n\n";

			$headers=eshop_from_address();
			$to = apply_filters('eshop_gateway_details_email', array($authorizenet['email']));
			wp_mail($to, $subject, $body, $headers);
			if($ok=='yes'){
				//only need to send out for the successes!
				//lets make sure this is here and available
				include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
				eshop_send_customer_email($checked, '8');

				do_shortcode('[eshop_show_success]');
			}

		}else{
			$authorizenet = $eshopoptions['authorizenet']; 
			$Key=$authorizenet['key'];
			//$checked=$ps->ipn_data["x_MD5_Hash"];	
			if($eshopoptions['status']=='live'){
				$txn_id = $wpdb->escape($ps->ipn_data['x_trans_id']);
				$subject = __('authorizenet IPN -','eshop');
			}else{
				$txn_id = __("TEST-",'eshop').$wpdb->escape($ps->ipn_data['x_trans_id']);
				$subject = __('Testing: authorizenet IPN - ','eshop');
			}
			$array=@eshop_rtn_order_details($checked);
			$ps->ipn_data['payer_email']=@$array['ename'].' '.@$array['eemail'].' ';
			$astatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' limit 1");
			//the magic bit  + creating the subject for our email.
			if($astatus=='Pending'){
				$query2=$wpdb->query("UPDATE $detailstable set status='Failed',transid='$txn_id' where checkid='$checked'");
				$subject .=__("INVALID Payment",'eshop');	
			}else{
				$query2=$wpdb->query("UPDATE $detailstable set status='Failed',transid='$txn_id' where checkid='$checked'");
				$subject .=__("Invalid and Failed Payment",'eshop');
			}
			$subject .=__(" Ref:",'eshop').$ps->ipn_data['x_trans_id'];
			// email to business a complete copy of the notification from authorizenet to keep!!!!!
			 $body =  __("An instant payment notification was received",'eshop')."\n";
			 $body .= "\n".__('from','eshop')." ".$ps->ipn_data['payer_email'].__(" on ",'eshop').date('m/d/Y');
			 $body .= __(' at ','eshop').date('g:i A')."\n\n".__('Details:','eshop')."\n";
			 foreach ($ps->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
			 $body .= "\n\n".__("Regards, Your friendly automated response.",'eshop')."\n\n";
			 $headers=eshop_from_address();
			 $to = apply_filters('eshop_gateway_details_email', array($authorizenet['email']));
			 wp_mail($to, $subject, $body, $headers);
		}
		
		break;
}
?>