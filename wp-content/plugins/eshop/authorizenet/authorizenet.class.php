<?php
if ('authorizenet.class.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>'.'Direct File Access Prohibited'.'</h2>');
     
/*******************************************************************************
 *                      PHP authorize.net IPN Integration Class
 *******************************************************************************
 *      Author:     Rich Pedley
 *      Based on: Paypal class
 *      
 *      To submit an order to authorize.net, have your order form POST to a file with:
 *
 *          $p = new authorize.net_class;
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

class authorizenet_class {
    
   var $last_error;                 // holds the last error encountered
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal
   
   function authorizenet_class() {
       
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

   function submit_authorizenet_post() {
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
      $refid=uniqid(rand());
      $echo .= "<input type=\"hidden\" name=\"RefNr\" value=\"$refid\" />\n";
      $echo.='<label for="ppsubmit" class="finalize"><small>'.__('<strong>Note:</strong> Submit to finalize order at Authorize.net.','eshop').'</small><br />
      <input class="button submit2" type="submit" id="ppsubmit" name="ppsubmit" value="'.__('Proceed to Checkout &raquo;','eshop').'" /></label>';
	  $echo.="</div></form>\n";
      
      return $echo;
   }
	function eshop_submit_authorizenet_post($_POST) {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to authorize.net.
      global $eshopoptions;
      $authorizenet = $eshopoptions['authorizenet'];
		$echortn='<div id="process">
         <p><strong>'.__('Please wait, your order is being processed&#8230;','eshop').'</strong></p>
	     <p>'. __('If you are not automatically redirected to authorize.net, please use the <em>Proceed to Authorize.net</em> button.','eshop').'</p>
         <form method="post" id="eshopgateway" class="eshop" action="'.$this->authorizenet_url.'">
          <p>';
			$relayURL=$_POST['x_relay_url'];
			$amount=str_replace(',','',$_POST['amount']);
			$shipping=str_replace(',','',$_POST['shipping_1']);

			$echortn.='
			<input type="hidden" name="x_show_form" value="PAYMENT_FORM" />
			<input type="hidden" name="x_relay_response" value="TRUE" />
			<input type="hidden" name="x_relay_url" value="'.$relayURL.'" />
			<input type="hidden" name="x_version" value="3.1" />
			';
			if($eshopoptions['status']!='live'){
			 $echortn.='<input type="hidden" name="x_test_request" value="TRUE" />';
			}
			//convert from paypal to authorize.net
			$invarray=array('first_name','last_name','company','email','phone','city','state','zip','country');
			$shiparray=array('ship_company','ship_address','ship_city','ship_state','ship_postcode','ship_country');
			foreach ($_POST as $key=>$value){
				if(in_array($key,$invarray)){
					$echortn.='<input type="hidden" name="x_'.$key.'" value="'.$value.'" />'."\n";
				}
				if($key=='address1')
					$echortn.='<input type="hidden" name="x_address" value="'.$value.'" />'."\n";
				if(in_array($key,$shiparray)){
					$key=substr($key, 4);
					$echortn.='<input type="hidden" name="x_ship_to'.$key.'" value="'.$value.'" />'."\n";
				}			
			}
			foreach ($this->fields as $name => $value) {
				if($name=='x_amount') $value= str_replace(',','',$value);
			   $echortn.= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
      		}
			//convert items to one liners - oh joy is me
			$numberofproducts=$_POST['numberofproducts'];
			$taxamount=0;
			$sep='<|>';
			$extracost=0;
			for($i=1;$i<=$numberofproducts;$i++){
				$taxable='N';
				if(strlen($_POST['item_name_'.$i]) > 25)
					$_POST['item_name_'.$i] = substr($_POST['item_name_'.$i],0,25).'...';
				if(strlen($_POST['item_number_'.$i]) > 249)
					$_POST['item_number_'.$i] = substr($_POST['item_number_'.$i],0,249).'...';
				
				$lineamount=str_replace(',','',$_POST['amount_'.$i]);
				if(isset($_POST['tax_'.$i])){
					$linetax=$_POST['tax_'.$i] / $_POST['quantity_'.$i];
					$lineamount += str_replace(',','',$linetax);
					$taxamount+=$_POST['tax_'.$i];
					$taxable='Y';
				}
				if((str_replace(",","",$lineamount)) != (0.01*floor(str_replace(",","",$lineamount)*100))){
					$extracost+=$_POST['quantity_'.$i] * (str_replace(",","",$lineamount) - (0.01*floor(str_replace(",","",$lineamount)*100)));
					$lineamount=0.01*floor(str_replace(",","",$lineamount)*100);
				}
				$value='item'.$i.$sep.$_POST['item_name_'.$i].$sep.$_POST['item_number_'.$i].$sep.$_POST['quantity_'.$i].$sep.str_replace(",","",$lineamount).$sep.$taxable;
				$echortn.='<input type="hidden" name="x_line_item" value="'.$value.'" />'."\n";
			}
			if($extracost!=0){
					$extracost = number_format($extracost, 2, '.', '');
					$value='itemdecpriceamd'.$sep.__('decimal price amendment').$sep.__('amend').$sep.'1'.$sep.str_replace(",","",$extracost).$sep.'Y';
					$echortn.='<input type="hidden" name="x_line_item" value="'.$value.'" />'."\n";
			}

			if($shipping>0){
				$value='item_s'.$sep.'Shipping'.$sep.''.$sep.'1'.$sep.$shipping.$sep.'N';
				$echortn.='<input type="hidden" name="x_line_item" value="'.$value.'" />'."\n";
			}
			if($taxamount>0)
				$echortn.='<input type="hidden" name="x_tax" value="'.str_replace(",","",$taxamount).'" />'."\n";

			$echortn.='
         <input class="button" type="submit" id="ppsubmit" name="ppsubmit" value="'. __('Proceed to authorize.net &raquo;','eshop').'" /></p>
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