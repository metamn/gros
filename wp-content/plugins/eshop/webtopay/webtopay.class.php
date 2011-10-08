<?php
if ('webtopay.class.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
     
/*******************************************************************************
 *                      PHP webtopay IPN Integration Class
 *******************************************************************************
 *      Author:     Rich Pedley
 *      Based on: Paypal class
 *      
 *      To submit an order to webtopay, have your order form POST to a file with:
 *
 *          $p = new webtopay_class;
 *          $p->add_field('business', 'somebody@domain.com');
 *          $p->add_field('first_name', $_POST['first_name']);
 *          ... (add all your fields in the same manor)
 *          $p->submit_webtopay_post();
 *
 *      To process an IPN, have your IPN processing file contain:
 *
 *          $p = new webtopay_class;
 *          if ($p->validate_ipn()) {
 *          ... (IPN is verified.  Details are in the ipn_data() array)
 *          }
 * 
 *******************************************************************************
*/
function signRequest($request, $password) {
	  $fields = array(
			   'projectid', 'orderid', 'lang', 'amount', 'currency',
			   'accepturl', 'cancelurl', 'callbackurl', 'payment', 'country',
			   'p_firstname', 'p_lastname', 'p_email', 'p_street',
			   'p_city', 'p_state', 'p_zip', 'p_countrycode', 'test',
			   'version'
		   );
	   $data = '';
	   foreach ($fields as $key) {
		   if (isset($request[$key]) && trim($request[$key]) != '') {
			   $data .= $request[$key];
		   }
	   }
	   $request['sign'] = md5($data . $password);

	   return $request;
}
class webtopay_class {
    
   var $last_error;                 // holds the last error encountered
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal
   
   function webtopay_class() {
       
      // initialization constructor.  Called when class is created.
      $this->last_error = '';
      $this->ipn_response = '';
    
   }
   
   function add_field($field, $value) {
      
      // adds a key=>value pair to the fields array, which is what will be 
      // sent to webtopay as POST variables.  If the value is already in the 
      // array, it will be overwritten.
      
      $this->fields["$field"] = $value;
   }

   function submit_webtopay_post() {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to webtopay.

      $echo= "<form method=\"post\" class=\"eshop eshop-confirm\" action=\"".$this->autoredirect."\"><div>\n";

      foreach ($this->fields as $name => $value) {
			$pos = strpos($name, 'amount');
			if ($pos === false) {
			   $echo.= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
			}else{
				$echo .= eshopTaxCartFields($name,$value);
      	    }
      }
      $refid=uniqid(rand());
      $echo .= "<input type=\"hidden\" name=\"RefNr\" value=\"$refid\" />\n";
      $echo.='<label for="ppsubmit" class="finalize"><small>'.__('<strong>Note:</strong> Submit to finalize order at webtopay.','eshop').'</small><br />
      <input class="button submit2" type="submit" id="ppsubmit" name="ppsubmit" value="'.__('Proceed to Checkout &raquo;','eshop').'" /></label>';
	  $echo.="</div></form>\n";
      
      return $echo;
   }
	function eshop_submit_webtopay_post($_POST) {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to webtopay.
      global $eshopoptions, $blog_id;
      $webtopay = $eshopoptions['webtopay'];
		$echortn='<div id="process">
         <p><strong>'.__('Please wait, your order is being processed&#8230;','eshop').'</strong></p>
	     <p>'. __('If you are not automatically redirected to webtopay, please use the <em>Proceed to webtopay</em> button.','eshop').'</p>
         <form method="post" id="eshopgateway" class="eshop" action="'.$this->webtopay_url.'">
          <p>';
          	$replace = array("&#039;","'", "\"","&quot;","&amp;","&");
          	
			$webtopay = $eshopoptions['webtopay']; 
			
			$theamount=str_replace(',','',$_POST['amount']);
			if(isset($_POST['tax']))
				$theamount += str_replace(',','',$_POST['tax']);
			
			if(isset($_SESSION['shipping'.$blog_id]['tax'])) $theamount += $_SESSION['shipping'.$blog_id]['tax'];
			
			$Cost = $theamount-$_POST['shipping_1'];
			
			$ExtraCost = $_POST['shipping_1'];
			
			
			// - Callback cannot be with GET vars -
			
			//$callbackURL = strtr($_POST['notify_url'], array('&amp;' => '&'));
			$_POST['notify_url']=strtr($_POST['notify_url'], array('&amp;' => '&'));
			//list($callbackURL, $getCallback) = explode('?', $callbackURL);
			
			//some location may be inaccurate, change them here:
			$changeloc=array('GB'=>'UK');
			$eshoploc=$eshopoptions['location'];
			if(isset($changeloc[$eshopoptions['location']]))
				$eshoploc=$changeloc[$eshopoptions['location']];
						
			# *************************************************
			# -- How we create sign param --
			$signFields = array( 
				'projectid' => $webtopay['projectid'], 
				'orderid' => $_POST['RefNr'], 
				'lang' => $webtopay['lang'], 
				'amount' => (($Cost + $ExtraCost) * 100), 
				'currency' => $eshopoptions['currency'], 
				'accepturl' => get_permalink($eshopoptions['cart_success']), 
				'cancelurl' => get_permalink($eshopoptions['cart_cancel']), 
				'callbackurl' => $_POST['notify_url'],
				'payment'=>'',
				'country' => $eshoploc, 
				'p_firstname' => $_POST['first_name'], 
				'p_lastname' => $_POST['last_name'], 
				'p_email' => $_POST['email'], 
				'p_street' => trim($_POST['address1'].' '. $_POST['address2']), 
				'p_city' => $_POST['city'], 
				'p_state' => $_POST['state'], 
				'p_zip' => $_POST['zip'], 
				'p_countrycode' => $_POST['country'],
				'test'=>($eshopoptions['status']=='live' ? 0 : 1),
				'version'=>'1.3'
			);
			$asigned=signRequest($signFields,$webtopay['signature']);
			
			$sign = $asigned['sign'];
			# *************************************************
						
			$echortn.=' 
			
			<input type="hidden" name="projectid" value="'.$webtopay['projectid'].'" />
			<input type="hidden" name="orderid" value="'.$_POST['RefNr'].'" />
			<input type="hidden" name="lang" value="' . $webtopay['lang'] . '" />
			<input type="hidden" name="amount" value="'. (($Cost + $ExtraCost) * 100) .'" />
			<input type="hidden" name="currency" value="' . $eshopoptions['currency'] . '" />
			<input type="hidden" name="accepturl" value="'.get_permalink($eshopoptions['cart_success']).'" />
			<input type="hidden" name="cancelurl" value="'.get_permalink($eshopoptions['cart_cancel']).'" />
			<input type="hidden" name="callbackurl" value="'.$_POST['notify_url'].'" />
			<input type="hidden" name="country" value="'.$eshoploc.'">	
			<input type="hidden" name="paytext" value="'. __('Payment for goods and services (of no. [order_nr]) ([site_name])','eshop').'" />
			<input type="hidden" name="p_firstname" value="'.$_POST['first_name'].'">			
			<input type="hidden" name="p_lastname" value="'.$_POST['last_name'].'">			
			<input type="hidden" name="p_email" value="'.$_POST['email'].'">			
			<input type="hidden" name="p_street" value="' . trim($_POST['address1'].' '. $_POST['address2']) . '">			
			<input type="hidden" name="p_city" value="'.$_POST['city'].'">			
			<input type="hidden" name="p_state" value="'.$_POST['state'].'">			
			<input type="hidden" name="p_zip" value="'.$_POST['zip'].'">			
			<input type="hidden" name="p_countrycode" value="'.$_POST['country'].'">	
			<input type="hidden" name="test" value="'.($eshopoptions['status']=='live' ? 0 : 1).'" />
			<input type="hidden" name="version" value="1.3" />
			<input type="hidden" name="sign" value="'.$sign.'">
			<input type="hidden" name="RefNr" value="'.$_POST['RefNr'].'" />';

			$echortn.=' 			
         <input class="button" type="submit" id="ppsubmit" name="ppsubmit" value="'. __('Proceed to webtopay &raquo;','eshop').'" /></p>
	     </form>
	  </div>';
		return $echortn;
   }   
   function validate_ipn() {
      // generate the post string from the _POST vars aswell as load the
      // _POST vars into an arry so we can play with them from the calling
      // script.
      foreach ($_REQUEST as $field=>$value) { 
         $this->ipn_data["$field"] = $value;
      }
     
   }
   

}   