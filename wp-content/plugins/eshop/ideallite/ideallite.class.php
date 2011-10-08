<?php
if ('ideallite.class.php' == basename($_SERVER['SCRIPT_FILENAME']))
  	die ('<h2>Direct File Access Prohibited</h2>');
  	
/*******************************************************************************

		Class to generate an Ideal Lite form. 
		
		Author:		Timo Tijhof
		Email:		info@grizzdesign.nl

		Based on version 0.3 (10-07-2009) of the component
		from Martijn Wieringa, PHP Solutions (info@php-solutions.nl)
		
		
 *******************************************************************************
*/

class ideallite_class {

	// Default settings
	var $sCurrency = 'EUR'; // Ideal only support payments in EURO.
	var $sLanguageCode = 'nl'; // NL
	var $sPaymentType = 'ideal';

	// Account settings
	var $sMerchantId = '';
	var $sSubId = 0;
	var $sHashKey = '';

	var $sAquirerName = '';
	var $sUrlAquirer = '';

	// Order settings
	var $totalCosts100 = '';
	var $desc30 = '';
	var $sValidUntil = '';
	var $sUrlCancel = '';
	var $sUrlError = '';
	var $sUrlSuccess = '';
		var $hCANCEL;
		var $hERROR;
		var $hSUCCESS;
	
	// Security & Identification
	var $sTransactionId;
	var $bTestMode;
	var $sHash;
	var $refid;
	var $setError;

 	// eShop vars
	var $last_error;  					// holds the last error encountered
	var $ipn_response;					// holds the IPN response from paypal	
	var $ipn_data = array();			// array contains the POST values for IPN
	var $fields = array();  			// array holds the fields to submit to paypal
	
/* ****************
* iDEAL functions *
***************** */	
	function escapeHtml($string) {
		return str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
	}
	

/* ****************
* eShop functions *
***************** */
	
	function ideallite_class() {
		// initialization constructor.  Called when class is created.
		$this->last_error = '';
		$this->ipn_response = '';
	}
	
	function add_field($field, $value) {
		// adds a key=>value pair to the fields array, which is what will be 
		// sent to ideallite as POST variables.  If the value is already in the 
		// array, it will be overwritten.
		
		$this->fields["$field"] = $value;
	}
	function submit_ideallite_post() {
		// The user will briefly see a message on the screen that reads:
		// "Please wait, your order is being processed..." and then immediately
		// is redirected to ideallite.

		$echo= "<form method=\"post\" class=\"eshop eshop-confirm\" action=\"".$this->autoredirect."\"><div>\n";

		foreach ($this->fields as $name => $value) {
			$pos = strpos($name, 'amount');
			if ($pos === false) {
			   $echo.= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
			}else{
				$echo .= eshopTaxCartFields($name,$value);
      	    }
      	}
		$refid=uniqid();
		$echo .= "<input type=\"hidden\" name=\"RefNr\" value=\"$refid\" />\n";
		$echo.='<label for="ppsubmit" class="finalize"><small>'.__('<strong>Note:</strong> Submit to finalize order at iDeal.','eshop').'</small><br />
		<input class="button submit2" type="submit" id="ppsubmit" name="ppsubmit" value="'.__('Proceed to Checkout &raquo;','eshop').'" /></label>';
	  $echo.="</div></form>\n";
		
		return $echo;
	}
	
	function eshop_submit_ideallite_post($_POST) {
		// The user will briefly see a message on the screen that reads:
		// "Please wait, your order is being processed..." and then immediately
		// is redirected to ideallite.
		global $eshopoptions;
		$ideallite = $eshopoptions['ideallite'];
				
		$echortn='<div id="process">
			<p><strong>'.__('Please wait, your order is being processed&#8230;','eshop').'</strong></p>
	  	<p>'. __('If you are not automatically redirected to iDeal, please use the <em>Proceed to iDeal</em> button.','eshop').'</p>
			<form method="post" id="eshopgateway" class="eshop" action="' . $this->escapeHtml($this->sUrlAquirer) . '">
 			<p>';
			
			$ideallite = $eshopoptions['ideallite'];
			
			$echortn.='
			<input type="hidden" name="merchantID" value="'.$this->sMerchantId.'" />
			<input type="hidden" name="subID" value="'.$this->sSubId.'" />
			<input type="hidden" name="amount" value="'.$this->totalCosts100.'" />
			<input type="hidden" name="purchaseID" value="'.$this->escapeHtml($this->refid).'" />
			<input type="hidden" name="language" value="'.$this->escapeHtml($this->sLanguageCode).'" />
			<input type="hidden" name="currency" value="'.$this->escapeHtml($this->sCurrency).'" />
			<input type="hidden" name="description" value="'.$this->desc30.'" />
			<input type="hidden" name="hash" value="' . $this->sHash . '" />
			<input type="hidden" name="paymentType" value="'.$this->escapeHtml($this->sPaymentType).'" />
			<input type="hidden" name="validUntil" value="'.$this->sValidUntil.'" />
			<input type="hidden" name="itemNumber1" value="1" />
			<input type="hidden" name="itemDescription1" value="'.$this->desc30.'" />
			<input type="hidden" name="itemQuantity1" value="1" />
			<input type="hidden" name="itemPrice1" value="'.$this->totalCosts100.'" />
			<input type="hidden" name="urlCancel" value="' . $this->escapeHtml($this->sUrlCancel) . '" />
			<input type="hidden" name="urlError" value="' . $this->escapeHtml($this->sUrlError) . '" />
			<input type="hidden" name="urlSuccess" value="' . $this->escapeHtml($this->sUrlSuccess) . '" />
			<input class="button" type="submit" id="ppsubmit" name="ppsubmit" value="'. __('Proceed to iDeal &raquo;','eshop').'" /></p>
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