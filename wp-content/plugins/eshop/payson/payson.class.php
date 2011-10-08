<?php
if ('payson.class.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
     
/*******************************************************************************
 *                      PHP Payson IPN Integration Class
 *******************************************************************************
 *      Author:     Rich Pedley
 *      Based on: Paypal class
 *      
 *      To submit an order to payson, have your order form POST to a file with:
 *
 *          $p = new payson_class;
 *          $p->add_field('business', 'somebody@domain.com');
 *          $p->add_field('first_name', $_POST['first_name']);
 *          ... (add all your fields in the same manor)
 *          $p->submit_payson_post();
 *
 *      To process an IPN, have your IPN processing file contain:
 *
 *          $p = new payson_class;
 *          if ($p->validate_ipn()) {
 *          ... (IPN is verified.  Details are in the ipn_data() array)
 *          }
 * 
 *******************************************************************************
*/

class payson_class {
    
   var $last_error;                 // holds the last error encountered
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal
   
   function payson_class() {
       
      // initialization constructor.  Called when class is created.
      $this->last_error = '';
      $this->ipn_response = '';
    
   }
   
   function add_field($field, $value) {
      
      // adds a key=>value pair to the fields array, which is what will be 
      // sent to payson as POST variables.  If the value is already in the 
      // array, it will be overwritten.
      
      $this->fields["$field"] = $value;
   }

   function submit_payson_post() {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to payson.

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
      $echo.='<label for="ppsubmit" class="finalize"><small>'.__('<strong>Note:</strong> Submit to finalize order at Payson.','eshop').'</small><br />
      <input class="button submit2" type="submit" id="ppsubmit" name="ppsubmit" value="'.__('Proceed to Checkout &raquo;','eshop').'" /></label>';
	  $echo.="</div></form>\n";
      
      return $echo;
   }
	function eshop_submit_payson_post($_POST) {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to payson.
      global $eshopoptions, $blog_id;
      $payson = $eshopoptions['payson'];
		$echortn='<div id="process">
         <p><strong>'.__('Please wait, your order is being processed&#8230;','eshop').'</strong></p>
	     <p>'. __('If you are not automatically redirected to Payson, please use the <em>Proceed to Payson</em> button.','eshop').'</p>
         <form method="post" id="eshopgateway" class="eshop" action="'.$this->payson_url.'">
          <p>';
          	$replace = array("&#039;","'", "\"","&quot;","&amp;","&");
			$payson = $eshopoptions['payson']; 
			$Key=$payson['key'];
			$theamount=$_POST['amount'];
			if(isset($_POST['tax']))
				$theamount += $_POST['tax'];
			if(isset($_SESSION['shipping'.$blog_id]['tax'])) $theamount += $_SESSION['shipping'.$blog_id]['tax'];
			
			$Cost=str_replace(',','',$theamount)-str_replace(',','',$_POST['shipping_1']);
			$ExtraCost=$_POST['shipping_1'];

			$desc = str_replace($replace, " ", $payson['description']);
			if($theamount<$payson['minimum']){
				$adjust=str_replace(',','',$payson['minimum'])-str_replace(',','',$theamount);
				$Cost=$Cost+$adjust;
				$desc .= ' '.sprintf(__('Payson minimum of %s SEK applied.','eshop'),$payson['minimum']);
			}
			$Cost=number_format($Cost, 2, ',', '');
			$ExtraCost=number_format($ExtraCost, 2, ',', '');
			$OkUrl=$_POST['notify_url'];
			$GuaranteeOffered='1';
			$MD5string = $payson['email'] . ":" . $Cost . ":" . $ExtraCost . ":" . $OkUrl . ":" . $GuaranteeOffered . $Key;
			$MD5Hash = md5($MD5string);
			$refid=$_POST['RefNr'];
			
			$clink='';
			if($eshopoptions['cart_cancel']!=''){
				$clink=add_query_arg('eshopaction','cancel',get_permalink($eshopoptions['cart_cancel']));
			}
			$echortn.='
			<input type="hidden" name="AgentId" value="'.$payson['id'].'" />
			<input type="hidden" name="SellerEmail" value="'.$payson['email'].'" />
			<input type="hidden" name="Description" value="'.$desc.'" />
			<input type="hidden" name="GuaranteeOffered" value="1" />
			<input type="hidden" name="OkUrl" value="'.$OkUrl.'" />
			<input type="hidden" name="CancelUrl" value="'.$clink.'" />
			<input type="hidden" name="MD5" value="'.$MD5Hash.'" />
			<input type="hidden" name="BuyerEmail" value="'.$_POST['email'].'" />
			<input type="hidden" name="BuyerFirstName" value="'.$_POST['first_name'].'" />
			<input type="hidden" name="BuyerLastName" value="'.$_POST['last_name'].'" />
			<input type="hidden" name="Cost" value="'.$Cost.'" />
			<input type="hidden" name="ExtraCost" value="'.$ExtraCost.'" />
			<input type="hidden" name="RefNr" value="'.$refid.'" />

         <input class="button" type="submit" id="ppsubmit" name="ppsubmit" value="'. __('Proceed to Payson &raquo;','eshop').'" /></p>
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