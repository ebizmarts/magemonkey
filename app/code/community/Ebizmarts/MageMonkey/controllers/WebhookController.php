<?php

/**
 * MailChimp webhooks controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_WebhookController extends Mage_Core_Controller_Front_Action
{

	/**
	 * Entry point for all webhook operations
	 */
	public function indexAction()
	{

		$requestKey = $this->getRequest()->getParam('wkey');

		//Checking if "wkey" para is present on request, we cannot check for !isPost()
		//because Mailchimp pings the URL (GET request) to validate webhook
		if( !$requestKey ){
			$this->getResponse()
            	->setHeader('HTTP/1.1', '403 Forbidden')
            	->sendResponse();
        	return $this;
		}
		
		Mage::helper('monkey')->log( print_r($this->getRequest()->getPost(), true) );

		Mage::app()->setCurrentStore(Mage::app()->getDefaultStoreView());

		$data  = $this->getRequest()->getPost('data');
		$myKey = Mage::helper('monkey')->getWebhooksKey(null, $data['list_id']);

		//Validate "wkey" GET parameter
		if ($this->getRequest()->getPost('type')) {
		        Mage::getModel('monkey/monkey')->processWebhookData($this->getRequest()->getPost());
		} else {
			if($myKey != $requestKey) {
		               Mage::helper('monkey')->log($this->__('Webhook Key invalid! Key Request: %s - My Key: %s', $requestKey, $myKey));
			}

                        Mage::helper('monkey')->log($this->__('Webhook call ended'));
                }



	}

}
