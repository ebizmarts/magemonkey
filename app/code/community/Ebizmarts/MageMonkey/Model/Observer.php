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
				$subscriber->setSubscriberStatus(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE);
			}
			Mage::getSingleton('monkey/api')
								->listSubscribe($listId, $email, NULL, 'html', $isConfirmNeed);

		}else{

			$status    = (int)$subscriber->getData('subscriber_status');
			$oldstatus = (int)$subscriber->getOrigData('subscriber_status');

			if( $status !== $oldstatus ){ //Status change

				//Unsubscribe customer
				if($status == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED){

					Mage::getSingleton('monkey/api')
									->listUnsubscribe($listId, $email);

				}else if($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){

					if( $oldstatus == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE ){
						Mage::getSingleton('monkey/api')
									->listSubscribe($listId, $email, NULL, 'html', $isConfirmNeed);
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
}