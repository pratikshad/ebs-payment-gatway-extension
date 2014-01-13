<?php 
class CRM_Core_Payment_Ebs extends CRM_Core_Payment {

	CONST CHARSET = 'iso-8859-1';
	protected $_mode = NULL;
	static private $_singleton = NULL;
	
	function __construct($mode, &$paymentProcessor) {
		$this->_mode = $mode;
		$this->_paymentProcessor = $paymentProcessor;
		$this->_processorName = ts('EBS');
		
		if (!$this->_paymentProcessor['user_name']) {
			CRM_Core_Error::fatal(ts('Could not find user name for payment processor'));
		}
	}
	
	static
	function &singleton($mode, &$paymentProcessor) {
		$processorName = $paymentProcessor['name'];
		if (!empty($processorName) || self::$_singleton[$processorName] === NULL) {
			self::$_singleton[$processorName] = new CRM_Core_Payment_Ebs($mode, $paymentProcessor);
		}
		return self::$_singleton[$processorName];
	}
	
	function checkConfig() {
		$config = CRM_Core_Config::singleton();
		
	    $error = array();   	    
	    if (empty($this->_paymentProcessor['user_name'])) {
	      $errorMsg[] = ' ' . ts('The EBS merchant ID is not in Administer CiviCRM Payment Processor');
	    }	    
		if (empty($this->_paymentProcessor['password'])) {
	      $errorMsg[] = ' ' . ts('The EBS secret key is not in Administer CiviCRM Payment Processor');
	    }	
	    if (!empty($error)) {
	      return implode('<p>', $error);
	    }
	    else {
	      return NULL;
	    }
	}
	
	function doDirectPayment(&$params, $component = 'contribute') {
		CRM_Core_Error::fatal(ts('This function is not implemented'));
	}


	function doTransferCheckout(&$params, $component = 'contribute') {
	    require_once 'CRM/Core/Session.php';
        CRM_Core_Session::storeSessionObjects( );
      
	    $config = CRM_Core_Config::singleton();	
	    if ($component != 'contribute' && $component != 'event') {
	      CRM_Core_Error::fatal(ts('Component is invalid'));
	    }	
	    $returnURL = $config->userFrameworkResourceURL . "extern/ebsNotify.php";	    
	    $returnURL .= "?reset=1&contactID={$params['contactID']}" . "&contributionID={$params['contributionID']}" . "&contributionTypeID={$params['contributionTypeID']}" . "&module={$component}";
	
	    if ($component == 'event') {
	      $returnURL .= "&eventID={$params['eventID']}&participantID={$params['participantID']}";
	    }
	    else {
	      $membershipID = CRM_Utils_Array::value('membershipID', $params);
	      if ($membershipID) {
	        $returnURL .= "&membershipID=$membershipID";
	      }
	      $relatedContactID = CRM_Utils_Array::value('related_contact', $params);
	      if ($relatedContactID) {
	        $returnURL .= "&relatedContactID=$relatedContactID";
	
	        $onBehalfDupeAlert = CRM_Utils_Array::value('onbehalf_dupe_alert', $params);
	        if ($onBehalfDupeAlert) {
	          $returnURL .= "&onBehalfDupeAlert=$onBehalfDupeAlert";
	        }
	      }
	    }
	    	
	    $url       = ($component == 'event') ? 'civicrm/event/register' : 'civicrm/contribute/transact';
	    $cancel    = ($component == 'event') ? '_qf_Register_display' : '_qf_Main_display';
	    $returnURL .= "&qfKey=".$params['qfKey'];
	    $returnURL .= "&DR={DR}";
	    	
	    // ensure that the returnURL is absolute.
	    if (substr($returnURL, 0, 4) != 'http') {
	      $fixUrl = CRM_Utils_System::url("civicrm/admin/setting/url", '&reset=1');
	      CRM_Core_Error::fatal(ts('Return url error', array(1 => $fixUrl)));
	    }
	    
	    $reference_no = $params['contributionID'];
	    $amount = $params['amount'];
	    $account_id = $this->_paymentProcessor['user_name'];
	    $secret_key = $this->_paymentProcessor['password'];
	    $mode = strtoupper($this->_mode);
	    $hash = $secret_key ."|". $account_id. "|". $amount . "|".$reference_no."|".html_entity_decode($returnURL)."|". $mode;
		$securehash = md5($hash);	

		$name = $params['billing_first_name'].' '.$params['billing_last_name'];
		$address = $params["billing_street_address-5"];
		$city = $params["billing_city-5"];
		$postal_code = $params["billing_postal_code-5"];
		$state_id = $params["billing_state_province_id-5"];
		if(isset($state_id)){
			$stateName = CRM_Core_PseudoConstant::stateProvinceAbbreviation($state_id);
		}
		$state = $stateName;
		$country_id = $params["billing_country_id-5"];
		if(isset($state_id)){
			$countryName = CRM_Core_PseudoConstant::countryIsoCode($country_id);
		}
		$country = $countryName;
		$phone = $params["billing_phone-5"];
    /*pratiksha*/
    if($component == 'event')
      {
        $params['email-5']=$params['email-Primary'];
      }
    /*pratiksha*/
	    $ebsParams = array(    	    
			'account_id' => $this->_paymentProcessor['user_name'],
			'mode'	=> $mode,
			'reference_no' => $reference_no,
			'amount' => $amount,
	    	'email' => $params['email-5'],
			'description' => $params['description'],					
			'return_url' => $returnURL,
			'secure_hash' => $securehash,
	      	'name' => $name,
	      	'address' => $address,
	      	'city' => $city,
	      	'state' => $stateName,
	      	'country' => $countryName,    
	      	'postal_code' => $postal_code,
	    	'phone' => $phone,
	     	'ship_name'=> $name,
	     	'ship_address' => $address,
	      	'ship_city' => $city,
	      	'ship_state' => $stateName,
	      	'ship_postal_code' => $postal_code,
	      	'ship_country' => $countryName,
	    );
	
	
	    // Allow further manipulation of the arguments via custom hooks ..
	    $form = "";
	    CRM_Utils_Hook::alterPaymentProcessorParams($this, $params, $ebsParams);	    
	    $form .= "<body onLoad='document.ebsform.submit();'>";	
		$form .= "<form name='ebsform' id='ebsformId' method='post' action='".$this->_paymentProcessor['url_site']."'>";
		$form .= "<p>Please wait while your payment is being processed...</p>";
		foreach ($ebsParams as $key => $value){ 
			if(!empty($value)){
				if ($key == 'return' || $key == 'cancel_return' || $key == 'notify_url') {
					$value = str_replace('%2F', '/', $value);
				}
				$form .= "<input type='hidden' name='".$key."' value='".$value."' size='60' />";
			}
		}	
		$form .= "</form></body>";
	    echo $form;
	    exit;
	}
}
?>