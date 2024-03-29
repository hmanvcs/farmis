<?php

class PaymentController extends IndexController   {

	public function editAction() {
    	$this->_setParam("action", ACTION_EDIT);
		// debugMessage($this->_getAllParams());
    	// exit();
    	$this->createAction();
    }
    
	public function subscriptionAction() {
    	
    }
    
	public function processsubscriptionAction() {
    	$this->_setParam("action", ACTION_CREATE);
		// debugMessage($this->_getAllParams());
    	// exit();
    	parent::createAction();
    }
    
	public function viewAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
    }
    
	public function viewsubscriptionAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
    }
    
	function deleteAction() {
    	$session = SessionWrapper::getInstance(); 
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		$successurl = decode($formvalues['successurl']);
		$classname = $formvalues['entityname'];
		// debugMessage($successurl);
		
    	$obj = new $classname;
    	$obj->populate($formvalues['id']);
    	// debugMessage($obj->toArray());
    	// exit();
    	if($obj->delete()) {
    		$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate("global_delete_success"));
    		$this->_helper->redirector->gotoUrl($successurl);
    	}
    	
    	return false;
    }
    
	public function paytestAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		debugMessage($formvalues);
		
		$user = new UserAccount();
		$user->populate(2790);
		
	    $test = array(
	    	"farmgroupid" => $user->getFarmGroupID(),
	    	"trxcode" => '451541',
	    	"stem" => 1,
	    	"item" => 4,
	    	"amount" => 360000,
	    	"phone" => getFullPhone($user->getPhone()),
	    	"verifier" => "herman"
	    );
	    debugMessage($test);
	    $payment = new Payment();
	    $payment->processPost($test);
	    debugMessage('error is '.$payment->getErrorStackAsString());
	    debugMessage($payment->toArray());
	    
	    $payment->save();
	    debugMessage($payment->toArray());
    }
    
    public function paynowAction(){
		
    }
    
	public function step2authAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		debugMessage($formvalues);
		debugMessage(decode($this->_getParam('successurl')));
		// exit();
		$this->_helper->redirector->gotoUrl(decode($this->_getParam('successurl')));
    }
    
    public function processpaynowAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
    }
    
	public function pesapalAction(){
		$this->_helper->layout->disableLayout();
    	$session = SessionWrapper::getInstance();
    	
	}
	
	public function pesapaliframeAction(){
		require_once APPLICATION_PATH.'/includes/OAuth.php';
		$this->_helper->layout->disableLayout();
		// $this->_helper->viewRenderer->setNoRender(TRUE);    
    	$session = SessionWrapper::getInstance();
    	
    	$formvalues = $this->_getAllParams(); // debugMessage($formvalues); exit;
    	
    	//pesapal params
		$token = $params = NULL;
		
		# merchant key and secret
		$consumer_key = PESAPAL_MERCHANT_KEY;//Register a merchant account on
		$consumer_secret = PESAPAL_MERCHANT_SECRET;// Use the secret from your test
		$iframelink = PESAPAL_POST_URL; // when you are ready to go live!
		
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		
		//get form details
		$amount = $this->_getParam('amount');
		$amount = number_format($amount, 2);//format amount to 2 decimal places
		$desc = $this->_getParam('description');
		$type = $this->_getParam('type'); //default value = MERCHANT
		$reference = $this->_getParam('reference');//unique order id of the transaction, generated by merchant
		$first_name = $this->_getParam('first_name');
		$last_name = $this->_getParam('last_name');
		$email = $this->_getParam('email');
		$phonenumber = $this->_getParam('phone');//ONE of email or phonenumber is required
		
		$callback_url = $this->view->serverUrl($this->view->baseUrl('payment/confirm/type/1')); //redirect url, the page that will handle the response from pesapal.
		
		$post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><PesapalDirectOrderInfo xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" Amount=\"".$amount."\" Description=\"".$desc."\" Type=\"".$type."\" Reference=\"".$reference."\" FirstName=\"".$first_name."\" LastName=\"".$last_name."\" Email=\"".$email."\" PhoneNumber=\"".$phonenumber."\" xmlns=\"http://www.pesapal.com\" />";
		$post_xml = htmlentities($post_xml);
		
		$consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		
		//post transaction to pesapal
		$iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);
		$iframe_src->set_parameter("oauth_callback", $callback_url);
		$iframe_src->set_parameter("pesapal_request_data", $post_xml);
		$iframe_src->sign_request($signature_method, $consumer, $token);
		
		// debugMessage($iframe_src);
		$this->view->iframe_src = $iframe_src;
	}
	
	public function confirmAction(){
		$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);   
	    
    	$session = SessionWrapper::getInstance();
    	
    	$formvalues = $this->_getAllParams(); debugMessage($formvalues); // exit();
    	$serialformvalues = serialize($formvalues); // debugMessage(unserialize($serialformvalues));
    	$entrydate = date("Y-m-d H:i:s");
    	
    	$pesapalNotification = $this->_getParam('pesapal_notification_type');
		$pesapalTrackingId = $this->_getParam('pesapal_transaction_tracking_id');
		$pesapal_merchant_reference = $this->_getParam('pesapal_merchant_reference');
		
    	//UPDATE YOUR DB TABLE WITH NEW STATUS FOR TRANSACTION WITH pesapal_transaction_tracking_id $pesapalTrackingId
		$conn = Doctrine_Manager::connection(); 
		$insertquery = "INSERT INTO payment SET trxcode = '$pesapalTrackingId', ref = '$pesapal_merchant_reference', description='$serialformvalues', datecreated='$entrydate' ";
		$result = $conn->execute($insertquery); 
		$paymentid = $conn->lastInsertId();	
		debugMessage('confirm pay id is '.$paymentid);
    	
		$this->_helper->redirector->gotoUrl($this->view->baseUrl('payment/ipn/pesapal_notification_type/CHANGE/pesapal_transaction_tracking_id/'.$pesapalTrackingId.'/pesapal_merchant_reference/'.$pesapal_merchant_reference));
    	exit();
	}
	
	public function ipnAction(){
		$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);   
	    require_once APPLICATION_PATH.'/includes/OAuth.php';
    	$session = SessionWrapper::getInstance();
    	
    	$formvalues = $this->_getAllParams(); debugMessage($formvalues); // exit();
    	$serialformvalues = serialize($formvalues); // debugMessage(unserialize($serialformvalues));
    	$entrydate = date("Y-m-d H:i:s");
    	
		# merchant key and secret
		$consumer_key = PESAPAL_MERCHANT_KEY;//Register a merchant account on
		$consumer_secret = PESAPAL_MERCHANT_SECRET;// Use the secret from your test
		$statusrequestAPI = PESAPAL_STATUS_URL; // when you are ready to go live!
		
		// Parameters sent to you by PesaPal IPN
		$pesapalNotification = $this->_getParam('pesapal_notification_type');
		$pesapalTrackingId = $this->_getParam('pesapal_transaction_tracking_id');
		$pesapal_merchant_reference = $this->_getParam('pesapal_merchant_reference');
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		
		if($pesapalNotification=="CHANGE" && !isEmptyString($pesapalTrackingId)){
		   $token = $params = NULL;
		   $consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		
		   //get transaction status
		   $request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $statusrequestAPI, $params);
		   $request_status->set_parameter("pesapal_merchant_reference", $pesapal_merchant_reference);
		   $request_status->set_parameter("pesapal_transaction_tracking_id",$pesapalTrackingId);
		   $request_status->sign_request($signature_method, $consumer, $token);
		
		   $ch = curl_init();
		   curl_setopt($ch, CURLOPT_URL, $request_status);
		   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		   curl_setopt($ch, CURLOPT_HEADER, 1);
		   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		   if(defined('CURL_PROXY_REQUIRED')) if (CURL_PROXY_REQUIRED == 'True')
		   {
		      $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
		      curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
		      curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		      curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
		   }
		
		   $response = curl_exec($ch);
		
		   $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		   $raw_header  = substr($response, 0, $header_size - 4);
		   $headerArray = explode("\r\n\r\n", $raw_header);
		   $header      = $headerArray[count($headerArray) - 1];
		
		   //transaction status
		   $elements = preg_split("/=/",substr($response, $header_size)); 
		   debugMessage($elements);
		   $status = !isArrayKeyAnEmptyString(1, $elements) ? $elements[1] : 'UNDEFINED'; 
		   $pesapalNotification .= " - ".$status;
		   debugMessage('new status is '.$status);
		   curl_close ($ch);
		   
			$resp="pesapal_notification_type=$pesapalNotification&pesapal_transaction_tracking_id=$pesapalTrackingId&pesapal_merchant_reference=$pesapal_merchant_reference";
			ob_start();
			echo $resp;
			ob_flush();
		} else {
			debugMessage('no params');
		}
		
		//UPDATE YOUR DB TABLE WITH NEW STATUS FOR TRANSACTION WITH pesapal_transaction_tracking_id $pesapalTrackingId
		$conn = Doctrine_Manager::connection(); 
		$insertquery = "INSERT INTO payment SET trxcode = '$pesapalTrackingId', ref = '$pesapal_merchant_reference', pesapalstatus='$pesapalNotification', description='$serialformvalues', datecreated='$entrydate' ";
		$result = $conn->execute($insertquery); 
		$paymentid = $conn->lastInsertId();	
		debugMessage('ipn pay id is '.$paymentid);
		
		// exit();
	}
}