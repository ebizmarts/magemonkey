<?php

class Ebizmarts_MageMonkey_Model_Monkey
{
	public function processWebhookData(array $data)
	{
		Mage::helper('monkey')->log( print_r($data, true) );

		$listId = $data['data']['list_id']; //According to the docs, we events are always related to a list_id
		$store  = Mage::helper('monkey')->getStoreByList($listId);

		if(!is_null($store)){
			$curstore = Mage::app()->getCurrentStore();
			Mage::app()->setCurrentStore($store);
		}

	    switch($data['type']){
	        case 'subscribe'  :
	        	$this->_subscribe($data);
	        break;
	        case 'unsubscribe':
	        	$this->_unsubscribe($data);
	        break;
	        case 'cleaned':
	        	$this->_clean($data);
	        break;
	        case 'campaign':
	        	$this->_campaign($data);
	        break;
	        //case 'profile': Cuando se actualiza email en MC como merchant, te manda un upmail y un profile (no siempre en el mismo órden)
	        case 'upemail':
	        	$this->_updateEmail($data);
	        break;
	    }

		if(!is_null($store)){
			Mage::app()->setCurrentStore($curstore);
		}

	}

	protected function _updateEmail(array $data)
	{

		/*if($data['type'] == 'profile'){

			$email = $data['data']['email'];

			$subscriber = $this->_loadByEmail($email);
			if($subscriber->getId()){
				$subscriber->setSubscriberEmail($email)
									->save();
			}else{
				Mage::getModel('newsletter/subscriber')->subscribe($email);
			}

		}else{*/

	 		$old = $data['data']['old_email'];
			$new = $data['data']['new_email'];

	 		$oldSubscriber = $this->_loadByEmail($old);
	 		$newSubscriber = $this->_loadByEmail($new);

			if( !$newSubscriber->getId() && $oldSubscriber->getId() ){
				$oldSubscriber->setSubscriberEmail($new)
								->save();
			}elseif(!$newSubscriber->getId() && !$oldSubscriber->getId()){

				Mage::getModel('newsletter/subscriber')
					->setImportMode(TRUE)
					->setStoreId(Mage::app()->getStore()->getId())
						->subscribe($new);

			}/*else{
				Mage::getModel('newsletter/subscriber')
					->setStoreId(Mage::app()->getStore()->getId())
						->subscribe($new);
				$oldSubscriber->delete();
			}*/

		/*}*/

	}

	/**
	 * Add "Cleaned Emails" notification to Adminnotification Inbox
	 */
	protected function _clean(array $data)
	{
		$text = Mage::helper('monkey')->__('MailChimp Cleaned Emails: %s %s at %s reason: %s', $data['data']['email'], $data['type'], $data['fired_at'], $data['data']['reason']);

		$this->_getInbox()
			  ->setTitle($text)
			  ->setDescription($text)
			  ->save();

		//Delete subscriber from Magento
		$s = $this->_loadByEmail($data['data']['email']);

		if($s->getId()){
			try{
		    	$s->delete();
			}catch(Exception $e){
				Mage::logException($e);
			}
		}

	}

	/**
	 * Add "Campaign Sending Status" to Adminnotification Inbox
	 */
	protected function _campaign(array $data)
	{
		$text = Mage::helper('monkey')->__('MailChimp Campaign Send: %s %s at %s', $data['data']['subject'], $data['data']['status'], $data['fired_at']);

		$this->_getInbox()
			  ->setTitle($text)
			  ->setDescription($text)
			  ->save();
	}

	/**
	 *
	 * Subscribe email to Magento list, store aware
	 *
	 */
	protected function _subscribe(array $data)
	{
		try{

			//TODO: El método subscribe de Subscriber (Magento) hace un load by email
			// entonces si existe en un store, lo acutaliza y lo cambia de store, no lo agrega a otra store
			//VALIDAR si es lo que se requiere

			Mage::getModel('newsletter/subscriber')->setImportMode(TRUE)->subscribe($data['data']['email']);
		}catch(Exception $e){
			Mage::logException($e);
		}

	}

	/**
	 *
	 * Unsubscribe or delete email from Magento list, store aware
	 *
	 */
	protected function _unsubscribe(array $data)
	{

		$s = $this->_loadByEmail($data['data']['email']);

		if($s->getId()){

			try{

			    switch($data['data']['action']){
			        case 'delete'  :
			        	$s->delete();
			        break;
			        case 'unsub':
			        	$s->setImportMode(TRUE)->unsubscribe();
			        break;
			    }

			}catch(Exception $e){
				Mage::logException($e);
			}

		}

	}

	protected function _getInbox()
	{
		return Mage::getModel('adminnotification/inbox')
					->setSeverity(4)//Notice
					->setDateAdded(Mage::getModel('core/date')->gmtDate());
	}

	protected function _loadByEmail($email)
	{
		return Mage::getModel('newsletter/subscriber')
				->getCollection()
				->addFieldToFilter('subscriber_email', $email)
				->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
				->getFirstItem();
	}
}