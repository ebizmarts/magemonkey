<?php

class Ebizmarts_MageMonkey_Model_Observer
{
	/**
	 * Handle Subscriber object saving process
	 */
	public function handleSubscriber(Varien_Event_Observer $observer)
	{
		$subscriber = $observer->getEvent()->getSubscriber();

		$email  = $subscriber->getSubscriberEmail();
		$listId = Mage::helper('monkey')->getDefaultList($subscriber->getStoreId());

		//New subscriber, just add
		if( $subscriber->isObjectNew() ){

			Mage::getSingleton('monkey/api')
									->listSubscribe($listId, $email);

		}else{

			$status    = (int)$subscriber->getData('subscriber_status');
			$oldstatus = (int)$subscriber->getOrigData('subscriber_status');

			if( $status !== $oldstatus ){ //Status change

				//Unsubscribe customer
				if($status == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED){

					Mage::getSingleton('monkey/api')
									->listUnsubscribe($listId, $email);

				}else if($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){

					Mage::getSingleton('monkey/api')
									->listSubscribe($listId, $email);

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
		$subscriber = $observer->getEvent()->getSubscriber();

		$listId = Mage::helper('monkey')->getDefaultList($subscriber->getStoreId());
		Mage::getSingleton('monkey/api')
									->listUnsubscribe($listId, $subscriber->getSubscriberEmail(), TRUE);

	}
}