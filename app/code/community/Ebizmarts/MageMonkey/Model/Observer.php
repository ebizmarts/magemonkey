<?php

class Ebizmarts_MageMonkey_Model_Observer
{
	/**
	 * Handle Subscriber object saving process
	 */
	public function handleSubscriber(Varien_Event_Observer $observer)
	{

		if( TRUE === Mage::helper('monkey')->isWebhookRequest()){
			return $observer;
		}

		$subscriber = $observer->getEvent()->getSubscriber();

		$subscriber->setImportMode(TRUE);

		$email  = $subscriber->getSubscriberEmail();
		$listId = Mage::helper('monkey')->getDefaultList($subscriber->getStoreId());
		$isConfirmNeed = (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRMATION_FLAG, $subscriber->getStoreId()) == 1) ? TRUE : FALSE;

		//New subscriber, just add
		if( $subscriber->isObjectNew() ){

			if( TRUE === $isConfirmNeed ){
				$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
			}

			$mergeVars = $this->_mergeVars($subscriber);
			Mage::getSingleton('monkey/api')
								->listSubscribe($listId, $email, $mergeVars, 'html', $isConfirmNeed);

		}else{

			$oldSubscriber = Mage::getModel('newsletter/subscriber')
								->load($subscriber->getId());

			$status        = (int)$subscriber->getData('subscriber_status');
			$oldstatus     = (int)$oldSubscriber->getData('subscriber_status');

			if( $status !== $oldstatus ){ //Status change

				//Unsubscribe customer
				if($status == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED){

					$rs = Mage::getSingleton('monkey/api')
									->listUnsubscribe($listId, $email);
					if($rs !== TRUE){
						Mage::throwException($rs);
					}

				}else if($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){

					if( TRUE === $isConfirmNeed ){
						$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
					}

					$rs = Mage::getSingleton('monkey/api')
									->listSubscribe($listId, $email, $this->_mergeVars($subscriber), 'html', $isConfirmNeed);
					if($rs !== TRUE){
						Mage::throwException($rs);
					}

				}

			}

		}
	}

	/**
	 * Handle Subscriber deletion from Magento, unsubcribes email from MailChimp
	 * and sends the delete_member flag so the subscriber gets deleted.
	 */
	public function handleSubscriberDeletion(Varien_Event_Observer $observer)
	{
		if( TRUE === Mage::helper('monkey')->isWebhookRequest()){
			return $observer;
		}

		$subscriber = $observer->getEvent()->getSubscriber();
		$subscriber->setImportMode(TRUE);

		$listId = Mage::helper('monkey')->getDefaultList($subscriber->getStoreId());

		Mage::getSingleton('monkey/api', array('store' => $subscriber->getStoreId()))
									->listUnsubscribe($listId, $subscriber->getSubscriberEmail(), TRUE);

	}


	public function saveConfig(Varien_Event_Observer $observer)
	{
		$store  = is_null($observer->getEvent()->getStore()) ? 'default': $observer->getEvent()->getStore();
		$post   = Mage::app()->getRequest()->getPost();
		$apiKey = (string)$post['groups']['general']['fields']['apikey']['value'];

		if(!$apiKey){
			return $observer;
		}

		$selectedLists = array();
		$selectedLists []= $post['groups']['general']['fields']['list']['value'];

		$additionalLists = $post['groups']['general']['fields']['additional_lists']['value'];
		if(is_array($additionalLists)){
			$selectedLists = array_merge($selectedLists, $additionalLists);
		}

		$webhooksKey = Mage::helper('monkey')->getWebhooksKey($store);
		$hookUrl  = Mage::app()->getStore($store)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, FALSE);
		$hookUrl .= Ebizmarts_MageMonkey_Model_Monkey::WEBHOOKS_PATH . $webhooksKey;

		$api   = Mage::getSingleton('monkey/api', array('apikey' => $apiKey));
		$lists = $api->lists();

		foreach($lists['data'] as $list){

			$webHooks = $api->listWebhooks($list['id']);

			if(!empty($webHooks)){
				foreach($webHooks as $whook){
					$chunk = (string)substr($whook['url'], strrpos($whook['url'], '/')+1, strlen($whook['url']));

					if((string)$webhooksKey === $chunk){
						$api->listWebhookDel($list['id'], $whook['url']);
					}
				}
			}

			if(in_array($list['id'], $selectedLists)){
				$api->listWebhookAdd($list['id'], $hookUrl);
			}

		}

	}

	public function updateCustomer(Varien_Event_Observer $observer)
	{
		$customer = $observer->getEvent()->getCustomer();

		$mergeVars = $this->_mergeVars($customer, TRUE);

		$api   = Mage::getSingleton('monkey/api', array('store' => $customer->getStoreId()));

		$oldEmail = $customer->getOrigData('email');
		if(!$oldEmail){
			return $observer;
		}

		$lists = $api->listsForEmail($oldEmail);

		if(is_array($lists)){
			foreach($lists as $listId){
				$api->listUpdateMember($listId, $oldEmail, $mergeVars);
			}
		}

		return $observer;
	}

	/**
	 * Add flag on session to tell the module if on success page should subscribe customer
	 */
	public function registerCheckoutSubscribe(Varien_Event_Observer $observer)
	{
		$subscribe = Mage::app()->getRequest()->getPost('magemonkey_subscribe');

		if(!is_null($subscribe)){
			Mage::getSingleton('core/session')->setMonkeyCheckout($subscribe);
		}
	}

	/**
	 * Subscribe customer to Newsletter if flag on session is present
	 */
	public function registerCheckoutSuccess(Varien_Event_Observer $observer)
	{
		$sessionFlag = Mage::getSingleton('core/session')->getMonkeyCheckout(TRUE);

		if($sessionFlag){
			$orderId = (int)current($observer->getEvent()->getOrderIds());

			if($orderId){
				$order = Mage::getModel('sales/order')->load($orderId);
				if( $order->getId() ){
						$subscriber = Mage::getModel('newsletter/subscriber')
							->subscribe($order->getCustomerEmail());
				}
			}
		}
	}

	protected function _mergeVars($object = NULL, $includeEmail = FALSE)
	{
		//Initialize as GUEST customer
		$customer = new Varien_Object;

		$regCustomer = Mage::registry('current_customer');

		if( Mage::helper('customer')->isLoggedIn() ){
			$customer = Mage::helper('customer')->getCustomer();
		}elseif($regCustomer){
			$customer = $regCustomer;
		}else{
			if(is_null($object)){
				$customer->setEmail($object->getSubscriberEmail())
					 ->setStoreId($object->getStoreId());
			}else{
				$customer = $object;
			}

		}

		$mergeVars = Mage::helper('monkey')->getMergeVars($customer, $includeEmail);

		return $mergeVars;
	}

}