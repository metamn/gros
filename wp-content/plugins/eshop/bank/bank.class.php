<?php
if ('bank.class.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
     
/*******************************************************************************
 *                      PHP bank IPN Integration Class
 *******************************************************************************
 *      Author:     Rich Pedley
 *      Based on: Paypal class
 *      
 *      To submit an order to bank, have your order form POST to a file with:
 *
 *          $p = new bank_class;
 *          $p->add_field('business', 'somebody@domain.com');
 *          $p->add_field('first_name', $_POST['first_name']);
 *          ... (add all your fields in the same manor)
 *          $p->submit_bank_post();
 *
 *      To process an IPN, have your IPN processing file contain:
 *
 *          $p = new bank_class;
 *          if ($p->validate_ipn()) {
 *          ... (IPN is verified.  Details are in the ipn_data() array)
 *          }
 * 
 *******************************************************************************
*/

class bank_class {
    
   var $last_error;                 // holds the last error encountered
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal
   
   function bank_class() {
       
      // initialization constructor.  Called when class is created.
      $this->last_error = '';
      $this->ipn_response = '';
    
   }
   
   function add_field($field, $value) {
      
      // adds a key=>value pair to the fields array, which is what will be 
      // sent to bank as POST variables.  If the value is already in the 
      // array, it will be overwritten.
      
      $this->fields["$field"] = $value;
   }

   function submit_bank_post() {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected.

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
      $echo.='<label for="ppsubmit" class="finalize"><small>'.__('<strong>Note:</strong> Submit to finalize your order.','eshop').'</small><br />
      <input class="button submit2" type="submit" id="ppsubmit" name="ppsubmit" value="'.__('Proceed to Checkout &raquo;','eshop').'" /></label>';
	  $echo.="</div></form>\n";
      
      return $echo;
   }
	function eshop_submit_bank_post($_POST) {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected.
      global $eshopoptions;
      $bank = $eshopoptions['bank'];
		$echortn ='<div id="process">
         <p><strong>'. __('Please wait, your order is being processed&#8230;','eshop').'</strong></p>
	     <p>'. __('If you are not automatically redirected, please use the <em>Proceed</em> button.','eshop').'</p>
         <form method="post" id="eshopgateway" class="eshop" action="'.$this->bank_url.'">
          <p>';
          	$replace = array("&#039;","'", "\"","&quot;","&amp;","&");
			$refid=$_POST['RefNr'];
			$echortn .='<input type="hidden" name="BuyerEmail" value="'.$_POST['email'].'" />
			<input type="hidden" name="BuyerFirstName" value="'.$_POST['first_name'].'" />
			<input type="hidden" name="BuyerLastName" value="'.$_POST['last_name'].'" />
			<input type="hidden" name="RefNr" value="'.$refid.'" />
         <input class="button" type="submit" id="ppsubmit" name="ppsubmit" value="'. __('Proceed &raquo;','eshop').'" /></p>
	     </form>
	  </div>';
		return $echortn;
   }   
}   