<?php
if ('ogone.class.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>'.'Direct File Access Prohibited'.'</h2>');
     
/*******************************************************************************
 *                      PHP authorize.net IPN Integration Class
 *******************************************************************************
 *      Author:     Rich Pedley
 *      Based on: Paypal class
 *      
 *      To submit an order to authorize.net, have your order form POST to a file with:
 *
 *          $p = new ogone_class;
 *          $p->add_field('business', 'somebody@domain.com');
 *          $p->add_field('first_name', $_POST['first_name']);
 *          ... (add all your fields in the same manor)
 *          $p->submit_authorize.net_post();
 *
 *      To process an IPN, have your IPN processing file contain:
 *
 *          $p = new authorize.net_class;
 *          if ($p->validate_ipn()) {
 *          ... (IPN is verified.  Details are in the ipn_data() array)
 *          }
 * 
 *******************************************************************************
*/

class ogone_class {
    
   var $last_error;                 // holds the last error encountered
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal
   
   function ogone_class() {
       
      // initialization constructor.  Called when class is created.
      $this->last_error = '';
      $this->ipn_response = '';
    
   }
   
   function add_field($field, $value) {
      
      // adds a key=>value pair to the fields array, which is what will be 
      // sent to authorize.net as POST variables.  If the value is already in the 
      // array, it will be overwritten.
      
      $this->fields["$field"] = $value;
   }

   function submit_ogone_post() {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to authorize.net.

      $echo= "<form method=\"post\" class=\"eshop eshop-confirm\" action=\"".$this->autoredirect."\"><div>\n";

      foreach ($this->fields as $name => $value) {
			$pos = strpos($name, 'amount');
			if ($pos === false) {
			   $echo.= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
			}else{
				$echo .= eshopTaxCartFields($name,$value);
      	    }
      }
      $echo.='<label for="ppsubmit" class="finalize"><small>'.__('<strong>Note:</strong> Submit to finalize order at ogone.','eshop').'</small><br />
      <input class="button submit2" type="submit" id="ppsubmit" name="ppsubmit" value="'.__('Proceed to Checkout &raquo;','eshop').'" /></label>';
	  $echo.="</div></form>\n";
      
      return $echo;
   }
	function eshop_submit_ogone_post($_POST) {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to authorize.net.
      global $eshopoptions;
      $ogone = $eshopoptions['ogone'];
		$echortn='<div id="process">
         <p><strong>'.__('Please wait, your order is being processed&#8230;','eshop').'</strong></p>
	     <p>'. __('If you are not automatically redirected to ogone, please use the <em>Proceed to ogone</em> button.','eshop').'</p>
         <form method="post" id="eshopgateway" class="eshop" action="'.$this->ogone_url.'">
          <p>';
			foreach ($this->fields as $name => $value) {
			  $echortn.= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
     		 }
			$echortn.='
         <input class="button" type="submit" id="ppsubmit" value="'. __('Proceed to ogone','eshop').'" /></p>
	     </form>
	  </div>';
	  
	  //debug
	  	
	  
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