<?php
// Setup class
require_once(WP_PLUGIN_DIR.'/eshop/epn/epn.class.php');  // include the class file
include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
global $wpdb,$eshopoptions;
$detailstable=$wpdb->prefix.'eshop_orders';
$ps = new epn_class; 
foreach ($_REQUEST as $field=>$value) { 
  $ps->ipn_data["$field"] = $value;
}
$epn = $eshopoptions['epn']; 
if(isset($ps->ipn_data['ID'])){
	$checked=md5($ps->ipn_data['ID']);
	if(isset($_POST['approved']) && isset($_GET['epn']) && $_GET['epn']=='ok' && $_POST['approved']=='Y'){
		$eshopdosend='yes';

		if($eshopoptions['status']=='live'){
			$txn_id = $wpdb->escape($ps->ipn_data['transid']);
			$subject = __('epn IPN -','eshop');
		}else{
			$txn_id = __("TEST-",'eshop').$wpdb->escape($ps->ipn_data['transid']);
			$subject = __('Testing: epn IPN - ','eshop');
		}
		//check txn_id is unique
		$checktrans=$wpdb->get_results("select transid from $detailstable");
		$astatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' limit 1");
		foreach($checktrans as $trans){
			if(strpos($trans->transid, $ps->ipn_data['ID'])===true){
				$astatus='Failed';
				$txn_id = __("Duplicated-",'eshop').$wpdb->escape($ps->ipn_data['ID']);
			}
		}
		//the magic bit  + creating the subject for our email.
		if($astatus=='Pending'){
			$subject .=__("Completed Payment",'eshop');	
			$ok='yes';
			eshop_mg_process_product($txn_id,$checked);
		}else{
			//cannot print anything out at this stage. so epn users won't see the download form.
			//then it must be a success
			//close session here.
			$_SESSION = array();
			session_destroy();
			$eshopdosend='no';
		}
		if($ok=='yes'){
			//only need to send out for the successes!
			//lets make sure this is here and available
			include_once(ABSPATH.'wp-content/plugins/eshop/cart-functions.php');
			eshop_send_customer_email($checked, '6');
		/*
			//this is an email sent to the customer:
			//first extract the order details
			$array=eshop_rtn_order_details($checked);

			$etable=$wpdb->prefix.'eshop_emails';
			//grab the template
			$thisemail=$wpdb->get_row("SELECT emailSubject,emailContent FROM ".$etable." WHERE (id='6' AND emailUse='1') OR id='1'  order by id DESC limit 1");
			$this_email = stripslashes($thisemail->emailContent);
			// START SUBST
			$csubject=stripslashes($thisemail->emailSubject);
			$this_email = eshop_email_parse($this_email,$array);

			//try and decode various bits - may need tweaking Mike, we may have to write 
			//a function to handle this depending on what you are using - but for now...
			$this_email=html_entity_decode($this_email,ENT_QUOTES);
			$headers=eshop_from_address();
			wp_mail($array['eemail'], $csubject, $this_email,$headers);
			//affiliate
			if($array['affiliate']!=''){
				do_action('eShop_process_aff_commission', array("id" =>$array['affiliate'],"sale_amt"=>$array['total'], 
				"txn_id"=>$array['transid'], "buyer_email"=>$array['eemail']));
			}
		*/
		}
	}elseif(isset($_POST['approved']) && isset($_GET['epn']) && $_GET['epn']=='fail' && $_POST['approved']=='N'){
		$eshopdosend='yes';
		if($eshopoptions['status']=='live'){
			$txn_id = $wpdb->escape($ps->ipn_data['auth_response']);
			$subject = __('epn IPN -','eshop');
		}else{
			$txn_id = __("TEST-",'eshop').$wpdb->escape($ps->ipn_data['auth_response']);
			$subject = __('Testing: epn IPN - ','eshop');
		}
		$array=@eshop_rtn_order_details($checked);
		$ps->ipn_data['payer_email']=@$array['ename'].' '.@$array['eemail'].' ';
		$astatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' limit 1");
		//the magic bit  + creating the subject for our email.
		$query2=$wpdb->query("UPDATE $detailstable set status='Failed',transid='$txn_id' where checkid='$checked'");
		$subject .=__("DECLINED Payment",'eshop');	
		$subject .=" ID Ref:".$ps->ipn_data['ID'];
		$echo.='<p>'.__('Your payment was not accepted at eProcessingNetwork and your order has been cancelled','eshop').'</p>';
	}
	if(isset($eshopdosend) && $eshopdosend=='yes'){
		$subject .=__(" Ref:",'eshop').$ps->ipn_data['ID'];
		// email to business a complete copy of the notification from epn to keep!!!!!
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
		$to = apply_filters('eshop_gateway_details_email', array($epn['email']));
		wp_mail($to, $subject, $body, $headers);
	}
}
?>