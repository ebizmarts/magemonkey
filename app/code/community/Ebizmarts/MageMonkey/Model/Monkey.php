<?php

/**
 * Module's main multi-purpose model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Model_Monkey {
    /**
     * Webhooks request url path
     *
     * @const string
     */

    const WEBHOOKS_PATH = 'monkey/webhook/index/';

    /**
     * Process Webhook request
     *
     * @param array $data
     * @return void
     */
    public function processWebhookData(array $data) {
        $listId = $data['data']['list_id']; //According to the docs, the events are always related to a list_id
        $store = Mage::helper('monkey')->getStoreByList($listId);

        if (!is_null($store)) {
            $curstore = Mage::app()->getStore();
            Mage::app()->setCurrentStore($store);
        }

        //Object for cache clean
        $object = new stdClass();
        $object->requestParams = array();
        $object->requestParams['id'] = $listId;

        if( isset($data['data']['email']) ){
            $object->requestParams['email_address']  = $data['data']['email'];
        }
        $cacheHelper = Mage::helper('monkey/cache');

        switch ($data['type']) {
            case 'subscribe':
				$this->_subscribe($data);
                    $cacheHelper->clearCache('listSubscribe', $object);
                break;
            case 'unsubscribe':
                $this->_unsubscribe($data);
                    $cacheHelper->clearCache('listUnsubscribe', $object);
                break;
            case 'cleaned':
                $this->_clean($data);
                    $cacheHelper->clearCache('listUnsubscribe', $object);
                break;
            case 'campaign':
                $this->_campaign($data);
                break;
            //case 'profile': Cuando se actualiza email en MC como merchant, te manda un upmail y un profile (no siempre en el mismo órden)
            case 'upemail':
                $this->_updateEmail($data);
                    $cacheHelper->clearCache('listUpdateMember', $object);
                break;
        }

        if (!is_null($store)) {
            Mage::app()->setCurrentStore($curstore);
        }
    }

    /**
     * Update customer email <upemail>
     *
     * @param array $data
     * @return void
     */
    protected function _updateEmail(array $data) {

        $old = $data['data']['old_email'];
        $new = $data['data']['new_email'];

        $oldSubscriber = $this->loadByEmail($old);
        $newSubscriber = $this->loadByEmail($new);

        if (!$newSubscriber->getId() && $oldSubscriber->getId()) {
            $oldSubscriber->setSubscriberEmail($new)
                    ->save();
        } elseif (!$newSubscriber->getId() && !$oldSubscriber->getId()) {

            Mage::getModel('newsletter/subscriber')
                    ->setImportMode(TRUE)
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->subscribe($new);
        }
    }

    /**
     * Add "Cleaned Emails" notification to Adminnotification Inbox <cleaned>
     *
     * @param array $data
     * @return void
     */
    protected function _clean(array $data) {

        if(Mage::helper('monkey')->isAdminNotificationEnabled()) {
            $text = Mage::helper('monkey')->__('MailChimp Cleaned Emails: %s %s at %s reason: %s', $data['data']['email'], $data['type'], $data['fired_at'], $data['data']['reason']);

            $this->_getInbox()
            ->setTitle($text)
            ->setDescription($text)
            ->save();
        }

        //Delete subscriber from Magento
        $s = $this->loadByEmail($data['data']['email']);

        if ($s->getId()) {
            try {
                $s->delete();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Add "Campaign Sending Status" notification to Adminnotification Inbox <campaign>
     *
     * @param array $data
     * @return void
     */
    protected function _campaign(array $data) {

        if(Mage::helper('monkey')->isAdminNotificationEnabled()) {
            $text = Mage::helper('monkey')->__('MailChimp Campaign Send: %s %s at %s', $data['data']['subject'], $data['data']['status'], $data['fired_at']);

            $this->_getInbox()
                    ->setTitle($text)
                    ->setDescription($text)
                    ->save();
        }

    }

    /**
     * Subscribe email to Magento list, store aware
     *
     * @param array $data
     * @return void
     */
    protected function _subscribe(array $data) {
        try {

            //TODO: El método subscribe de Subscriber (Magento) hace un load by email
            // entonces si existe en un store, lo acutaliza y lo cambia de store, no lo agrega a otra store
            //VALIDAR si es lo que se requiere

            $subscriber = Mage::getModel('newsletter/subscriber')
                    ->loadByEmail($data['data']['email']);
            if ($subscriber->getId()) {
                $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
                        ->save();
            } else {
                Mage::getModel('newsletter/subscriber')->setImportMode(TRUE)
                        ->subscribe($data['data']['email']);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Unsubscribe or delete email from Magento list, store aware
     *
     * @param array $data
     * @return void
     */
    protected function _unsubscribe(array $data) {
	$subscriber = $this->loadByEmail($data['data']['email']);

	if(!$subscriber->getId()){
	$subscriber = Mage::getModel('newsletter/subscriber')
	                    ->loadByEmail($data['data']['email']);
	}

	if($subscriber->getId()){
	try {

        switch ($data['data']['action']) {
            case 'delete' :
                //if config setting "Webhooks Delete action" is set as "Delete customer account"
            	if(Mage::getStoreConfig("monkey/general/webhook_delete") == 1){
                	$subscriber->delete();
            	}else{
					$subscriber->setImportMode(TRUE)->unsubscribe();
				}
                break;
            case 'unsub':
                $subscriber->setImportMode(TRUE)->unsubscribe();
                break;
        }
    } catch (Exception $e) {
        Mage::logException($e);
    }
	}
    }

    /**
     * Return Inbox model instance
     *
     * @return Mage_AdminNotification_Model_Inbox
     */
    protected function _getInbox() {
        return Mage::getModel('adminnotification/inbox')
                        ->setSeverity(4)//Notice
                        ->setDateAdded(Mage::getModel('core/date')->gmtDate());
    }

    /**
     * Load newsletter_subscriber by email
     *
     * @param string $email
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function loadByEmail($email) {
        return Mage::getModel('newsletter/subscriber')
                        ->getCollection()
                        ->addFieldToFilter('subscriber_email', $email)
                        ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
                        ->getFirstItem();
    }

}
