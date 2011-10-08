<?php
if ('epn.class.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
     
/*******************************************************************************
 *                      PHP epn IPN Integration Class
 *******************************************************************************
 *      Author:     Rich Pedley
 *      Based on: Paypal class
 *      
 *      To submit an order to epn, have your order form POST to a file with:
 *
 *          $p = new epn_class;
 *          $p->add_field('business', 'somebody@domain.com');
 *          $p->add_field('first_name', $_POST['first_name']);
 *          ... (add all your fields in the same manor)
 *          $p->submit_epn_post();
 *
 *      To process an IPN, have your IPN processing file contain:
 *
 *          $p = new epn_class;
 *          if ($p->validate_ipn()) {
 *          ... (IPN is verified.  Details are in the ipn_data() array)
 *          }
 * 
 *******************************************************************************
*/

class epn_class {
    
   var $last_error;                 // holds the last error encountered
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal
   
   function epn_class() {
       
      // initialization constructor.  Called when class is created.
      $this->last_error = '';
      $this->ipn_response = '';
    
   }
   
   function add_field($field, $value) {
      
      // adds a key=>value pair to the fields array, which is what will be 
      // sent to epn as POST variables.  If the value is already in the 
      // array, it will be overwritten.
      
      $this->fields["$field"] = $value;
   }

   function submit_epn_post() {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to epn.
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
      $echo.='<label for="ppsubmit" class="finalize"><small>'.__('<strong>Note:</strong> Submit to finalize order at eProcessingNetwork.','eshop').'</small><br />
      <input class="button submit2" type="submit" id="ppsubmit" name="ppsubmit" value="'.__('Proceed to Checkout &raquo;','eshop').'" /></label>';
	  $echo.="</div></form>\n";
      
      return $echo;
   }
	function eshop_submit_epn_post($_POST) {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to epn.
      global $eshopoptions, $blog_id;
      $epn = $eshopoptions['epn'];
		$echortn='<div id="process">
         <p><strong>'.__('Please wait, your order is being processed&#8230;','eshop').'</strong></p>
	     <p>'. __('If you are not automatically redirected to eProcessingNetwork, please use the <em>Proceed to eProcessingNetwork</em> button.','eshop').'</p>
         <form method="post" id="eshopgateway" class="eshop" action="'.$this->epn_url.'">
          <p>';
		$replace = array("&#039;","'", "\"","&quot;","&amp;","&");
		$epn = $eshopoptions['epn']; 
		$Cost=$_POST['amount'];
		if(isset($_POST['tax']))
			$Cost += $_POST['tax'];
		if(isset($_SESSION['shipping'.$blog_id]['tax'])) $Cost += $_SESSION['shipping'.$blog_id]['tax'];
		
		$desc = str_replace($replace, " ", $epn['description']);
		$address=$_POST['address1'].' '. $_POST['address2'];
		$ReturnApprovedURL=$_POST['ReturnApprovedURL'];
		$ReturnDeclinedURL=$_POST['ReturnDeclinedURL'];
		$refid=$_POST['RefNr'];
		if($eshopoptions['status']!='live'){
			$epn['id']='080880';
		}
		$echortn.='
			<input type="hidden" name="ePNAccount" value="'.$epn['id'].'" />
			<input type="hidden" name="Description" value="'.$desc.'" />
			<input type="hidden" name="ReturnApprovedURL" value="'.$ReturnApprovedURL.'" />
			<input type="hidden" name="ReturnDeclinedURL" value="'.$ReturnDeclinedURL.'" />
			<input type="hidden" name="Address" value="'.$address.'" />
			<input type="hidden" name="Company" value="'.$_POST['company'].'" />
			<input type="hidden" name="City" value="'.$_POST['city'].'" />
			<input type="hidden" name="State" value="'.$_POST['state'].'" />
			<input type="hidden" name="Country" value="'.$_POST['country'].'" />
			<input type="hidden" name="Phone" value="'.$_POST['phone'].'" />
			<input type="hidden" name="Zip" value="'.$_POST['zip'].'" />
			<input type="hidden" name="EMail" value="'.$_POST['email'].'" />
			<input type="hidden" name="FirstName" value="'.$_POST['first_name'].'" />
			<input type="hidden" name="LastName" value="'.$_POST['last_name'].'" />
			<input type="hidden" name="Total" value="'.$Cost.'" />
			<input type="hidden" name="ID" value="'.$refid.'" />

         <input class="button" type="submit" id="ppsubmit" name="ppsubmit" value="'. __('Proceed to eProcessingNetwork &raquo;','eshop').'" /></p>
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
?>