<?php

/*  PHP Paypal IPN Integration Class Demonstration File
 *  4.16.2005 - Micah Carrick, email@micahcarrick.com
 *
 *  This file demonstrates the usage of eshop-paypal.class.php, a class designed  
 *  to aid in the interfacing between your website, paypal, and the instant
 *  payment notification (IPN) interface.  This single file serves as 4 
 *  virtual pages depending on the "action" varialble passed in the URL. It's
 *  the processing page which processes form data being submitted to paypal, it
 *  is the page paypal returns a user to upon success, it's the page paypal
 *  returns a user to upon canceling an order, and finally, it's the page that
 *  handles the IPN request from Paypal.
 *
 *  I tried to comment this file, aswell as the acutall class file, as well as
 *  I possibly could.  Please email me with questions, comments, and suggestions.
 *  See the header of eshop-paypal.class.php for additional resources and information.
*/
global $wpdb,$wp_query,$wp_rewrite,$blog_id,$eshopoptions;
$detailstable=$wpdb->prefix.'eshop_orders';
$derror=__('There appears to have been an error, please contact the site admin','eshop');

//sanitise
include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
$_POST=sanitise_array($_POST);

include_once (WP_PLUGIN_DIR.'/eshop/paypal/index.php');
// Setup class
require_once(WP_PLUGIN_DIR.'/eshop/paypal/eshop-paypal.class.php');  // include the class file
$p = new eshop_paypal_class;             // initiate an instance of the class

if($eshopoptions['status']=='live'){
	$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';     // paypal url
}else{
	$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';   // testing paypal url
}

// setup a variable for this script (ie: 'http://www.micahcarrick.com/paypal.php')
//e.g. $this_script = 'http://'.$_SERVER['HTTP_HOST'].htmlentities($_SERVER['PHP_SELF']);
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
		$token = uniqid(md5($_SESSION['date'.$blog_id]), true);
		//$checkid=md5($eshopoptions['business'].$token.number_format($_SESSION['final_price'.$blog_id],2));
		$pvalue = $_POST['amount'];
		if(isset($_SESSION['shipping'.$blog_id]['tax'])) $pvalue += $_SESSION['shipping'.$blog_id]['tax'];
		//eShop own check for extra security
		$eshopemailbus=$eshopoptions['business'];
		if(isset( $eshopoptions['eshop_business_sec'] ) && $eshopoptions['eshop_business_sec'] !=''){
			$eshopemailbus=$eshopoptions['business_sec'];
			$_POST['business']=$eshopemailbus;
		}
		$checkid=md5($eshopemailbus.$token.number_format($pvalue,2));
		//affiliates
		if(isset($_COOKIE['ap_id'])) $_POST['affiliate'] = $_COOKIE['ap_id'];
		orderhandle($_POST,$checkid);
		if(isset($_COOKIE['ap_id'])) unset($_POST['affiliate']);
		$_POST['custom']=$token;
		$p = new eshop_paypal_class; 
		if($eshopoptions['status']=='live'){
			$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';     // paypal url
		}else{
			$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';   // testing paypal url
		}
		if('no'==$eshopoptions['paypal_noemail']){
			unset($_POST['email']);
		}
		$echoit.=$p->eshop_submit_paypal_post($_POST);
		//$p->dump_fields();      // for debugging, output a table of all the fields
		break;
        
   case 'process':      // Process and order...
	
		// There should be no output at this point.  To process the POST data,
		// the submit_paypal_post() function will output all the HTML tags which
		// contains a FORM which is submited instantaneously using the BODY onload
		// attribute.  In other words, don't echo or printf anything when you're
		// going to be calling the submit_paypal_post() function.
		
		// This is where you would have your form validation  and all that jazz.
		// You would take your POST vars and load them into the class like below,
		// only using the POST values instead of constant string expressions.

		// For example, after ensureing all the POST variables from your custom
		// order form are valid, you might have:
		//
		// $p->add_field('first_name', $_POST['first_name']);
		// $p->add_field('last_name', $_POST['last_name']);
      
      /****** The order has already gone into the database at this point ******/
      
		$p->add_field('business', $eshopoptions['business']);
		if($eshopoptions['cart_success']!=''){
			$slink=add_query_arg('eshopaction','success',get_permalink($eshopoptions['cart_success']));
			$slink=apply_filters('eshop_paypal_return_link',$slink);
		}else{
			die('<p>'.$derror.'</p>');
		}
		if($eshopoptions['cart_cancel']!=''){
			$clink=add_query_arg('eshopaction','cancel',get_permalink($eshopoptions['cart_cancel']));
		}else{
			die('<p>'.$eshopoptions['cart_cancel'].$derror.'</p>');
		}
		
		$p->add_field('return', $slink);
		$p->add_field('cancel_return', $clink);
		//goes direct to this script as nothing needs showing on screen.
		if($eshopoptions['cart_success']!=''){
			$ilink=add_query_arg('eshopaction','paypalipn',get_permalink($eshopoptions['cart_success']));
		}else{
			die('<p>'.$derror.'</p>');
		}
		$p->add_field('notify_url', $ilink);

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

	//	$p->add_field('return_method','2'); //1=GET 2=POST
	// was return method now rm - go figure.
		$p->add_field('rm','2'); //1=GET 2=POST
		
		//settings in paypal/index.php to change these
		$p->add_field('currency_code',$eshopoptions['currency']);
		$p->add_field('lc',$eshopoptions['location']);
		$p->add_field('cmd','_ext-enter');
		$p->add_field('redirect_cmd','_cart');
		$p->add_field('upload','1');
		if('yes' == $eshopoptions['downloads_only'])
			$p->add_field('no_shipping','1');
		
		//$p->add_field('address_override','1');//causes errors :(
		if($eshopoptions['status']!='live' && is_user_logged_in() &&  current_user_can('eShop_admin')||$eshopoptions['status']=='live'){
			$echoit .= $p->submit_paypal_post(); // submit the fields to paypal
    		//$p->dump_fields();      // for debugging, output a table of all the fields
    	}
      	break;
      
   case 'success':      // Order was successful...
		// This is where you would probably want to thank the user for their order
		// or what have you.  The order information at this point is in POST 
		// variables.  However, you don't want to "process" the order until you
		// get validation from the IPN.  That's where you would have the code to
		// email an admin, update the database with payment status, activate a
		// membership, etc.  
		$_SESSION = array();
      	session_destroy();
      	if($eshopoptions['status']=='live'){
			$txn_id = $wpdb->escape($_POST['txn_id']);
		}else{
			$txn_id = "TEST-".$wpdb->escape($_POST['txn_id']);
		}
		$frow=$wpdb->get_var("select first_name from $detailstable where transid='$txn_id' limit 1");
		$lrow=$wpdb->get_var("select last_name from $detailstable where transid='$txn_id' limit 1");
		if($frow!='' && $lrow!=''){
			$echoit .= "<h3>".__('Thank you for your order','eshop').", ".$frow." ".$lrow."!</h3>";
		}else{
			$echoit .= "<h3>".__('Thank you for your order!','eshop')."</h3>";
		}
		//echo 'name='.$row->first_name.' '.$row->last_name.'<br>';
		// You could also simply re-direct them to another page, or your own 
		// order status page which presents the user with the status of their
		// order based on a database (which can be modified with the IPN code 
		// below).
       	break;
      	
    case 'cancel':       // Order was canceled...
	  		/*
	  		The script doesn't get here, so for cancelled orders see the bottom of cart.php
	  		Unfortunate side effect is that the order is left in pending
	  		*/
	  		// The order was canceled before being completed.
	  		/* commented out until i can think of a way to get this to work :(  -Rich
	  		$checked=md5($p->ipn_data['business'].$p->ipn_data['custom'].$p->ipn_data['payer_email'].$p->ipn_data['mc_gross']);
	  		$tstatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' limit 1");
	  		if($eshopoptions['status']=='live'){
	  			$txn_id = 'Cancelled-'.$wpdb->escape($p->ipn_data['txn_id']);
	  		}else{
	  			$txn_id = "TEST-Cancelled-".$wpdb->escape($p->ipn_data['txn_id']);
	  		}
	  		if($tstatus=='Pending'){
	  			$query2=$wpdb->query("UPDATE $detailstable set status='Failed',transid='$txn_id' where checkid='$checked'");
	  		}
	  		*/

		break;
   
      
   case 'paypalipn':          // Paypal is calling page for IPN validation...
   		// It's important to remember that paypal calling this script.  There
		// is no output here.  This is where you validate the IPN data and if it's
		// valid, update your database to signify that the user has payed.  If
		// you try and use an echo or printf function here it's not going to do you
		// a bit of good.  This is on the "backend".  That is why, by default, the
		// class logs all IPN data to a text file.
		// the loggin to a text file isn't working, so we have coded an email to be sent instead.
		$extradetails ='';
		if ($p->validate_ipn()) {
			// Payment has been received and IPN is verified.  This is where you
			// update your database to activate or process the order, or setup
			// the database with the user's order details, email an administrator,
			// etc.  You can access a slew of information via the ipn_data() array.

			// Check the paypal documentation for specifics on what information
			// is available in the IPN POST variables.  Basically, all the POST vars
			// which paypal sends, which we send back for validation, are now stored
			// in the ipn_data() array.
 		/*
		updating db.
		*/
			$chkamt=number_format((($p->ipn_data['mc_gross'])-($p->ipn_data['tax'])),2);
			$checked=md5($p->ipn_data['business'].$p->ipn_data['custom'].$chkamt);
			
			if($eshopoptions['status']=='live'){
				$txn_id = $wpdb->escape($p->ipn_data['txn_id']);
				$subject = __('Paypal IPN -','eshop');
			}else{
				$txn_id = __("TEST-",'eshop').$wpdb->escape($p->ipn_data['txn_id']);
				$subject = __('Testing: Paypal IPN - ','eshop');
			}
			//check txn_id is unique
			$checktrans=$wpdb->get_results("select transid from $detailstable");
			$astatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' limit 1");
			foreach($checktrans as $trans){
				if(strpos($trans->transid, $p->ipn_data['txn_id'])===true){
					$astatus='Failed';
					$txn_id .= __(" - Duplicated",'eshop');
					$extradetails .= __("Duplicated Transaction Id.",'eshop');
				}
			}
			//stop the evil buggers changing the currency
			if($p->ipn_data['mc_currency']!=$eshopoptions['currency']){
				$astatus='Failed';
				$txn_id .= __(" - Fraud",'eshop');
				$extradetails .= __("Currency codes do not match, someone was trying to make a fraudulent purchase!",'eshop');
			}
			
			//check reciever email is correct - we will use business for now
			if($p->ipn_data['receiver_email']!= $eshopoptions['business']){
				$astatus='Failed';
				$txn_id .= __(" - Fraud",'eshop');
				$extradetails .= __("The business email address in eShop does not match your main email address at Paypal.",'eshop');
			}
			//add any memo from user at paypal here
			$memo=$wpdb->escape($p->ipn_data['memo']);
			$mquery=$wpdb->query("UPDATE $detailstable set thememo='$memo' where checkid='$checked'");
			//the magic bit  + creating the subject for our email.
			if($astatus=='Pending' && $p->ipn_data['payment_status']=='Completed'){
				$subject .=__("Completed Payment",'eshop');	
				$ok='yes';
				eshop_mg_process_product($txn_id,$checked);
			}elseif($p->ipn_data['payment_status']==' Refunded'){
				$subject .=__("Refunded Payment",'eshop');
				$ok='no';
				$extradetails .= __("You have received a refund notification, eShop doesn't know how to handle these, but details of the notification are included below.",'eshop');
			}else{
				$query2=$wpdb->query("UPDATE $detailstable set status='Failed',transid='$txn_id' where checkid='$checked'");
				$subject .=__("A Failed Payment",'eshop');
				$ok='no';
				$extradetails .= __("The transaction was not completed successfully. eShop could not validate the order.",'eshop');
				if($p->ipn_data['payment_status']!='Completed' && isset($p->ipn_data['pending_reason']))
					$extradetails .= __("The transaction was not completed successfully at Paypal. The pending reason for this is",'eshop').' '.$_POST['pending_reason'];
			}
			$subject .=" Ref:".$txn_id;
			$array=eshop_rtn_order_details($checked);
			// email to business a complete copy of the notification from paypal to keep!!!!!
			 $body =  __("An instant payment notification was received",'eshop')."\n";
			 $body .= "\n".__("from ",'eshop').$p->ipn_data['payer_email'].__(" on ",'eshop').date('m/d/Y');
			 $body .= __(" at ",'eshop').date('g:i A')."\n\n".__('Details','eshop').":\n";
			 if(isset($array['dbid']))
			 	$body .= get_option( 'siteurl' ).'/wp-admin/admin.php?page=eshop-orders.php&view='.$array['dbid']."&eshop\n";

			 //debug
			//$body .= 'checked:'.$checked."\n".$p->ipn_data['business'].$p->ipn_data['custom'].$p->ipn_data['payer_email'].$chkamt."\n";
			if($extradetails!='') $body .= $extradetails."\n\n";
			 foreach ($p->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
			 $body .= "\n\n".__('Regards, Your friendly automated response.','eshop')."\n\n";

			$headers=eshop_from_address();
			$eshopemailbus=$eshopoptions['business'];
			if(isset( $eshopoptions['business_sec'] ) && $eshopoptions['business_sec'] !=''){
				$eshopemailbus=$eshopoptions['business_sec'];
			}
			$to = apply_filters('eshop_gateway_details_email', array($eshopemailbus));
			wp_mail($to, $subject, $body, $headers);

			if($ok=='yes'){
				//only need to send out for the successes!
				//lets make sure this is here and available
				include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
				eshop_send_customer_email($checked, '3');
			}
			
      	}else{
      		//not validated
      		$chkamt=number_format($p->ipn_data['mc_gross']-$p->ipn_data['tax'],2);
			$checked=md5($p->ipn_data['business'].$p->ipn_data['custom'].$chkamt);
			if($eshopoptions['status']=='live'){
				$txn_id = $wpdb->escape($p->ipn_data['txn_id']);
				$subject = __('Paypal IPN -','eshop');
			}else{
				$txn_id = __("TEST-",'eshop').$wpdb->escape($p->ipn_data['txn_id']);
				$subject = __('Testing: Paypal IPN - ','eshop');
			}

			$astatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' limit 1");
			//add any memo from user at paypal here
			$memo=$wpdb->escape($p->ipn_data['memo']);
			$mquery=$wpdb->query("UPDATE $detailstable set thememo='$memo' where checkid='$checked'");
			//the magic bit  + creating the subject for our email.
			if($astatus=='Pending' && $p->ipn_data['payment_status']=='Completed'){
				$query2=$wpdb->query("UPDATE $detailstable set status='Failed',transid='$txn_id' where checkid='$checked'");
				$subject .=__("INVALID Payment",'eshop');	
				$extradetails .= __("Paypal has reported an invalid payment.",'eshop');	
			}else{
				$query2=$wpdb->query("UPDATE $detailstable set status='Failed',transid='$txn_id' where checkid='$checked'");
				$subject .=__("Invalid and Failed Payment",'eshop');
				$extradetails .= __("Paypal has reported an invalid, and failed payment.",'eshop');
				if($_POST['payment_status']!='Completed' && isset($_POST['pending_reason']))
					$extradetails .= __("Paypal has reported an invalid, and failed payment. The pending reason for this is",'eshop').' '.$_POST['pending_reason'];

			}
			$subject .=__(" Ref:",'eshop').$txn_id;
			// email to business a complete copy of the notification from paypal to keep!!!!!
			 $body =  __("An instant payment notification was received",'eshop')."\n";
			 $body .= "\n".__('from','eshop')." ".$p->ipn_data['payer_email'].__(" on ",'eshop').date('m/d/Y');
			 $body .= __(' at ','eshop').date('g:i A')."\n\n".__('Details:','eshop')."\n";
			 if($extradetails!='') $body .= $extradetails."\n\n";
			 foreach ($p->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
			 $body .= "\n\n".__("Regards, Your friendly automated response.",'eshop')."\n\n";
			 $headers=eshop_from_address();
			 $eshopemailbus=$eshopoptions['business'];
			 if(isset( $eshopoptions['business_sec'] ) && $eshopoptions['business_sec'] !=''){
			 	$eshopemailbus=$eshopoptions['business_sec'];
			 }
			 $to = apply_filters('eshop_gateway_details_email', array($eshopemailbus));
			 wp_mail($to, $subject, $body, $headers);
		}
      	break;
 }     
?>