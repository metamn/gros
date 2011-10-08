<?php
/*
	Based on ideal-lite-plugin-for-wordpress-2.8.1-and-e-commerce-3.7 by Martijn Wieringa (info@php-solutions.nl)
	See also his link: http://www.ideal-simulator.nl/ideal-for-wordpress-and-e-commerce-plugin.html
	
	Ported to eShop by Timo Tijhof (info@grizzdesign.nl) 
	Version: 0.0.5 (2009-11-03)
	
	Changelog:
		0.0.1 - Initial version
		0.0.2 - Addes custom statusnames in trxid and mailsubjects
		0.0.3 - Minor bugfixes and code optimalisation
		0.0.4 - Removed hardcoded debugging code, and disabled dynamic ones 
		0.0.5 - Fixed link to order in shopowner-mail
		
		
	Todo:
		- Output setError when set in the class
		- Empty shopping cart on successfull payment
		- Translation to Dutch
		- Test in live environment
		- Remove vars and functions that are now unused (because of merge) 
*/


global $wpdb,$wp_query,$wp_rewrite,$blog_id,$eshopoptions;
$detailstable=$wpdb->prefix.'eshop_orders';
$derror=__('There appears to have been an error, please contact the site admin','eshop');

//sanitise
include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
$_POST=sanitise_array($_POST);

include_once (WP_PLUGIN_DIR.'/eshop/ideallite/index.php');
// Setup class
require_once(WP_PLUGIN_DIR.'/eshop/ideallite/ideallite.class.php');  // include the class file
$p = new ideallite_class;             // initiate an instance of the class

$this_script = site_url();
if($eshopoptions['checkout']!=''){
	$p->autoredirect=add_query_arg('eshopaction','redirect',get_permalink($eshopoptions['checkout']));
}else{
	die('<p>'.$derror.'</p>');
}

// iDeal Stuff
	$ideallite = $eshopoptions['ideallite']; 
	// Set ideallite variables
		if(!empty($ideallite['IDEAL_HASH_KEY'])) {
			$p->sHashKey = $ideallite['IDEAL_HASH_KEY'];
		} elseif(!empty($ideallite['IDEAL_PRIVATE_KEY'])) {
			$p->sHashKey = $ideallite['IDEAL_PRIVATE_KEY'];
		}
		
		if(!empty($ideallite['IDEAL_MERCHANT_ID'])) {
			$p->sMerchantId = $ideallite['IDEAL_MERCHANT_ID'];
		}
		
		if(!empty($ideallite['IDEAL_SUB_ID'])) {
			$p->sSubId = $ideallite['IDEAL_SUB_ID'];
		} else { $p->sSubId = "0"; }
		
		if(!empty($ideallite['IDEAL_AQUIRER'])) {
			$p->sAquirerName = $ideallite['IDEAL_AQUIRER'];
		}
		
		if(!empty($ideallite['IDEAL_TEST_MODE'])) {
			$p->bTestMode = $ideallite['IDEAL_TEST_MODE'];
		}
	
	// Set aquirer (Use: Rabobank, ING Bank or ABN Amro)
		$sAquirerName = $p->sAquirerName;
		$bTestMode = $p->bTestMode;
		
		if(stripos($sAquirerName, 'rabo') !== false) // Rabobank
		{
			$p->sUrlAquirer = 'https://ideal' . ($bTestMode ? 'test' : '') . '.rabobank.nl/ideal/mpiPayInitRabo.do';
		}
		elseif(stripos($sAquirerName, 'ing') !== false) // ING Bank
		{
			$p->sUrlAquirer = 'https://ideal' . ($bTestMode ? 'test' : '') . '.secure-ing.com/ideal/mpiPayInitIng.do';
		}
		elseif(stripos($sAquirerName, 'sim') !== false) // Simulator
		{
			$p->sUrlAquirer = 'https://www.ideal-simulator.nl/lite/';
		}
		else // Unknown aquirer
		{
			$p->setError = 'Unknown aquirer. Please use Rabobank, ING Bank or Simulator.'.__FILE__.__LINE__;
			return false;
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
		$ideallite = $eshopoptions['ideallite']; 
				
		$checkid=md5($_POST['RefNr']);
		
		// Set codes & hash
			$replace = array("&#039;","'", "\"","&quot;","&amp;","&");
			$desc = str_replace($replace, " ", $ideallite['idealdescription']);
			$p->desc30 =substr($desc, 0, 30);
			
			$p->refid=$_POST['RefNr'];
			
			$totalCosts = $_POST['amount'];
			
			if(isset($_POST['tax']))
				$totalCosts += $_POST['tax'];
			if(isset($_SESSION['shipping'.$blog_id]['tax'])) $totalCosts += $_SESSION['shipping'.$blog_id]['tax'];	
			
			$p->totalCosts100 = round($totalCosts * 100);
			
			$p->sValidUntil = date('Y-m-d\TG:i:s\Z', strtotime('+1 hour'));
			
		// Setup iDEAL-hash string
			$sHashString = $p->sHashKey . $p->sMerchantId . $p->sSubId . $p->totalCosts100 . $p->refid . 
			$p->sPaymentType . $p->sValidUntil . '1' . $p->desc30 . '1' . $p->totalCosts100;
			
			$sHashString = html_entity_decode($sHashString); // Remove HTML Entities
			
			// Remove space characters: "\t", "\n", "\r" and " "
			$sHashString = str_replace(array("\t", "\n", "\r", " "), '', $sHashString);
			
			$p->sHash = sha1($sHashString);	// Generate iDEAL-hash and send to class for submitting to ideallite
		
		// Send generated hashes to class for submitting to ideallite
		$p->sTransactionId = sha1($p->refid . '_' . $p->sHash . '_' . rand(100000000, 999999999));
		$p->sEntranceCode = sha1($p->refid . '_' . $p->sHash . '_' . rand(100000000, 999999999));
			
	
		// Set URLs
		$sUrlBase = $_POST['notify_url'];
		
		$p->sUrlCancel = $sUrlBase.'&ideal[trxid]=' . $p->sTransactionId . '&ideal[ec]=' . $p->sEntranceCode . '&RefNr=' . $p->refid . '&ideal[status]=' . md5('CANCEL');
		$p->sUrlError = $sUrlBase.'&ideal[trxid]=' . $p->sTransactionId . '&ideal[ec]=' . $p->sEntranceCode . '&RefNr=' . $p->refid . '&ideal[status]=' . md5('ERROR');
		$p->sUrlSuccess = $sUrlBase.'&ideal[trxid]=' . $p->sTransactionId . '&ideal[ec]=' . $p->sEntranceCode . '&RefNr=' . $p->refid . '&ideal[status]=' . md5('SUCCESS');


		if(isset($_COOKIE['ap_id'])) $_POST['affiliate'] = $_COOKIE['ap_id'];
		orderhandle($_POST,$checkid);
		if(isset($_COOKIE['ap_id'])) unset($_POST['affiliate']);
		
		$traxid = $p->sTransactionId;
		$_POST['custom']=$traxid;
		
		if($eshopoptions['status']=='live'){
			//$p->ideallite_url = 'http://www.google.com/the-real-bank-site.html';     // ideallite url
			$p->ideallite_url =$p->sUrlAquirer;//why was it crippled?
		}else{
			$p->ideallite_url = 'https://www.ideal-simulator.nl/lite/';   // testing ideallite url
		}
		$echoit.=$p->eshop_submit_ideallite_post($_POST);
		//$p->dump_fields();      // for debugging, output a table of all the fields
		break;
        
   case 'process':      // Process and order...
	
		// There should be no output at this point.  To process the POST data,
		// the submit_ideallite_post() function will output all the HTML tags which
		// contains a FORM which is submited instantaneously using the BODY onload
		// attribute.  In other words, don't echo or printf anything when you're
		// going to be calling the submit_ideallite_post() function.
		
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
				$ilink=add_query_arg('eshopaction','idealliteipn',get_permalink($eshopoptions['cart_success']));
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
	
		if($eshopoptions['status']!='live' && is_user_logged_in() &&  current_user_can('eShop_admin')||$eshopoptions['status']=='live'){
			$echoit .= $p->submit_ideallite_post(); // submit the fields to ideallite
    		//$p->dump_fields();      // for debugging, output a table of all the fields
    	}
      	break;
   case 'idealliteipn':          // ideallite is calling page for IPN validation...
   
		// It's important to remember that ideallite calling this script.  There
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

			// Check the ideallite documentation for specifics on what information
			// is available in the IPN POST variables.  Basically, all the POST vars
			// which ideallite sends, which we send back for validation, are now stored
			// in the ipn_data() array.
 		/*
		updating db.
		*/
		foreach ($_REQUEST as $field=>$value) { 
		  $ps->ipn_data["$field"] = $value;
		}

		$ideallite = $eshopoptions['ideallite']; 
		
		$urlTransactionId = $_GET['ideal']['trxid'];
		$urlEntranceCode = $_GET['ideal']['ec'];
		$urlStatus = $_GET['ideal']['status'];
		$urlRefNr = $_GET['RefNr'];
			
		$checked = md5($urlRefNr);
		
		$eshopdosend='yes';
			
		if(isset($_GET['ideal']['trxid']) && isset($_GET['ideal']['ec']) && isset($_GET['ideal']['status'])) {
		
			$sTransactionId = $_GET['ideal']['trxid'];
			$sEntranceCode = $_GET['ideal']['ec'];
			$sStatus = $_GET['ideal']['status'];
			
			if(in_array($sStatus, array(md5('CANCEL'), md5('ERROR'), md5('SUCCESS'))) == false) {
				$sStatus = md5('ERROR');
			}
			
			// Convert status to text
			if ($sStatus == md5('CANCEL')) {
				$sStatus = "CANCEL";
			} elseif ($sStatus == md5('ERROR')) {
				$sStatus = "ERROR";
			} elseif ($sStatus == md5('SUCCESS')) {
				$sStatus = "SUCCESS";
			} else {
				echo "Invalid iDEAL-status";
				return false;
			} //echo "<h1>".$sStatus."</h1>"; // for debugging, outputs cleartext statuscode that is being used from here on
			
			
			//
			// If payment was marked by iDEAL as SUCCESS
			//
			if(strcasecmp($sStatus, 'SUCCESS') === 0) {
				//Set transaction IDs
				if($eshopoptions['status']=='live'){
					$txn_id = $wpdb->escape($ps->ipn_data['RefNr']);
					$subject = __('iDeal -','eshop');
				}else{
					$txn_id = __("TEST-",'eshop').$wpdb->escape($ps->ipn_data['RefNr']);
					$subject = __('Testing: ideallite IPN - ','eshop');
				}
				//check txn_id is unique
				$checktrans=$wpdb->get_results("select transid from $detailstable");
				$astatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' limit 1");
				foreach($checktrans as $trans){
					if(strpos($trans->transid, $ps->ipn_data['RefNr'])===true){
						$astatus='Failed';
						$txn_id = __("Duplicated-",'eshop').$wpdb->escape($ps->ipn_data['RefNr']);
					}
				}
				//the magic bit  + creating the subject for our email.
				if($astatus=='Pending'){
					$subject .=__("Completed Payment",'eshop');	
					$ok='yes';
					eshop_mg_process_product($txn_id,$checked);
					
				}else{
					//cannot print anything out at this stage. so ideallite users won't see the download form.
					//then it must be a success
					//close session here.
					$_SESSION = array();
					session_destroy();
					$eshopdosend='no';
				}
	
				if($eshopdosend=='yes'){
					$subject .=" Ref:".$ps->ipn_data['RefNr'];
					$orderID=$wpdb->get_var("select id from $detailstable where checkid='$checked' limit 1");
					$viewOrdersURL = get_option( 'siteurl' ) . '/wp-admin/admin.php?page=eshop-orders.php&view='. $orderID . '&eshop';
					// email to business a complete copy of the notification from ideallite to keep!!!!!
					$array=eshop_rtn_order_details($checked);
					$ps->ipn_data['payer_email']=$array['ename'].' '.$array['eemail'].' ';
					 $body =  __("An instant payment notification was received",'eshop')."\n";
					 $body .= "\n".__("from ",'eshop').$ps->ipn_data['payer_email'].__(" on ",'eshop').date('m/d/Y');
					 $body .= __(" at ",'eshop').date('g:i A')."\n";
					 $body .= __('More information', 'shop').": ".$viewOrdersURL."\n\n";
					 $body .= __('Details','eshop').":\n";
					 foreach ($ps->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
					 $body .= "\n\n".__('Regards, Your friendly automated response.','eshop')."\n\n";
					$headers=eshop_from_address();
					$to = apply_filters('eshop_gateway_details_email', array($ideallite['idealownermail']));
					wp_mail($to, $subject, $body, $headers);
				}
				if($ok=='yes'){
					//only need to send out for the successes!
					//lets make sure this is here and available
					include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
					eshop_send_customer_email($checked, '9');
					// Clear the session - Empty the cart
					$_SESSION = array();
					session_destroy();
				}
	
			}
			//
			// Payment was marked by iDEAL as ERROR, or was CANCELed (statuscode = $sStatus)
			//
			else{
				$ideallite = $eshopoptions['ideallite']; 
				if($eshopoptions['status']=='live'){
					$txn_id = $wpdb->escape($ps->ipn_data['RefNr']);
					$subject = __('iDeal -','eshop');
				}else{
					$txn_id = __("TEST-",'eshop').$wpdb->escape($ps->ipn_data['RefNr']);
					$subject = __('Testing: ideallite IPN - ','eshop');
				}
				$txn_id = $sStatus.': '.$txn_id; //prepend statuscode to transaction-id for the indication in the backend
				
				$array=@eshop_rtn_order_details($checked);
				$ps->ipn_data['payer_email']=@$array['ename'].' '.@$array['eemail'].' ';
				$astatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' limit 1");
				//the magic bit  + creating the subject for our email.
				if($astatus=='Pending'){
					$query2=$wpdb->query("UPDATE $detailstable set status='Failed',transid='$txn_id' where checkid='$checked'");
					
					if(strcasecmp($sStatus, 'ERROR') === 0) {
						// If marked by iDEAL as ERROR
						$subject .=__("ERROR Payment",'eshop');
					}
					elseif(strcasecmp($sStatus, 'CANCEL') === 0) {
						// Else, if marked by iDEAL as CANCEL
						$subject .=__("Cancelled Payment",'eshop');
					}
					else {
						// Fallback to default just in case
						$subject .=__("INVALID Payment",'eshop');
					}
				}else{
					$query2=$wpdb->query("UPDATE $detailstable set status='Failed',transid='$txn_id' where checkid='$checked'");
					$subject .=__("Invalid and Failed Payment",'eshop');
				}
				$subject .=__(" Ref:",'eshop').$ps->ipn_data['RefNr'];
				// email to business a complete copy of the notification from ideallite to keep!!!!!
				 $body =  __("An instant payment notification was received",'eshop')."\n";
				 $body .= "\n".__('from','eshop')." ".$ps->ipn_data['payer_email'].__(" on ",'eshop').date('m/d/Y');
				 $body .= __(' at ','eshop').date('g:i A')."\n\n".__('Details:','eshop')."\n";
				 foreach ($ps->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
				 $body .= "\n\n".__("Regards, Your friendly automated response.",'eshop')."\n\n";
				 $headers=eshop_from_address();
				 $to = apply_filters('eshop_gateway_details_email', array($ideallite['idealownermail']));
				 wp_mail($to, $subject, $body, $headers);
			}
		}
		break;
}
?>