<?php

class Ebizmarts_MageMonkey_Model_Observer
{
	public function handleSubscriber($observer)
	{
		$subscriber = $observer->getEvent()->getSubscriber();

		//New subscriber, just add
		if( $subscriber->isObjectNew() ){

			$listId = Mage::helper('monkey')->getDefaultList($subscriber->getStoreId());
			$result = Mage::getSingleton('monkey/api')
									->listSubscribe($listId, $subscriber->getSubscriberEmail());

		}
	}
}