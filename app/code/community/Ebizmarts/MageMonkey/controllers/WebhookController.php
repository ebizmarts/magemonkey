<?php

/**
 * MailChimp webhooks controller
 */
class Ebizmarts_MageMonkey_WebhookController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{

		Mage::app()->setCurrentStore(Mage::app()->getDefaultStoreView());

		$data = $this->getRequest()->getPost('data');

		$requestKey = $this->getRequest()->getParam('wkey');
		$myKey      = Mage::helper('monkey')->getWebhooksKey(null, $data['list_id']);

		//Validate "wkey" GET parameter
		if (($requestKey == $myKey) && ($this->getRequest()->getPost('type'))) {
			Mage::getModel('monkey/monkey')->processWebhookData($this->getRequest()->getPost());
		}

	}

}