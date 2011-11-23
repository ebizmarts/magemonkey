<?php

class Ebizmarts_MageMonkey_Model_Monkey
{
	public function processWebhookData(array $data)
	{
		Mage::helper('monkey')->log( print_r($data, true) );

		$listId = $data['data']['list_id']; //According to the docs, we events are always related to a list_id
		$store  = Mage::helper('monkey')->getStoreByList($listId);

		if(!is_null($store)){
			Mage::app()->setCurrentStore($store);
		}

	    switch($data['type']){
	        case 'subscribe'  :
	        	$this->_subscribe($data);
	        break;
	        case 'unsubscribe':
	        	$this->_unsubscribe($data);
	        break;
	        /*case 'cleaned'    : $this->_subscribe($_POST['data']);     break;
	        case 'upemail'    : $this->_subscribe($_POST['data']);     break;
	        case 'profile'    : $this->_subscribe($_POST['data']);     break;*/
	    }

		if(!is_null($store)){
			Mage::app()->setCurrentStore(Mage::app()->getDefaultStoreView());
		}

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

			Mage::getModel('newsletter/subscriber')->subscribe($data['data']['email']);
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

		$s = Mage::getModel('newsletter/subscriber')
				->getCollection()
				->addFieldToFilter('subscriber_email', $data['data']['email'])
				->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
				->getFirstItem();

		if($s->getId()){

			try{

			    switch($data['data']['action']){
			        case 'delete'  :
			        	$s->delete();
			        break;
			        case 'unsub':
			        	$s->unsubscribe();
			        break;
			    }

			}catch(Exception $e){
				Mage::logException($e);
			}

		}

	}
}