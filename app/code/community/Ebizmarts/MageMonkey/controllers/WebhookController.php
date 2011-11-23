<?php

/**
 * MailChimp webhooks controller
 */
class Ebizmarts_MageMonkey_WebhookController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{

		Mage::app()->setCurrentStore(Mage::app()->getDefaultStoreView());

		$requestKey = $this->getRequest()->getParam('wkey');
		$myKey      = Mage::helper('core')->decrypt(Mage::helper('monkey')->config('webhooks_key'));

		//Validate "mkey" GET parameter
		if (($requestKey == $myKey) && ($this->getRequest()->getPost('type'))) {
			Mage::getModel('monkey/monkey')->processWebhookData($this->getRequest()->getPost());
		}

	}

}