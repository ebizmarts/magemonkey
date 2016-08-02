<?php

/**
 * Module's main multi-purpose model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Model_Monkey
{
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
    public function processWebhookData(array $data)
    {


          Mage::getModel('monkey/asyncwebhooks')
              ->setWebhookType($data['type'])
              ->setWebhookData(json_encode($data))
              ->setProcessed(0)
              ->save();

    }

    /**
     * Update customer email <upemail>
     *
     * @param array $data
     * @return void
     */
    protected function _updateEmail(array $data)
    {

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
    protected function _clean(array $data)
    {

        if (Mage::helper('monkey')->isAdminNotificationEnabled()) {
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
    protected function _campaign(array $data)
    {

        if (Mage::helper('monkey')->isAdminNotificationEnabled()) {
            $text = Mage::helper('monkey')->__('MailChimp Campaign Send: %s %s at %s', $data['data']['subject'], $data['data']['status'], $data['fired_at']);

            $this->_getInbox()
                ->setTitle($text)
                ->setDescription($text)
                ->save();
        }

    }

    
    protected function _profile(array $data)
    {
        $email = $data['data']['email'];
        $subscriber = $this->loadByEmail($email);
        $storeId = $subscriber->getStoreId();

        $customerCollection = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('email', array('eq' => $email));
        if (count($customerCollection) > 0) {
            $toUpdate = $customerCollection->getFirstItem();
        } else {
            $toUpdate = $subscriber;
        }
        $toUpdate->setFirstname($data['data']['merges']['FNAME']);
        $toUpdate->setLastname($data['data']['merges']['LNAME']);
        $toUpdate->save();


    }

    /**
     * Return Inbox model instance
     *
     * @return Mage_AdminNotification_Model_Inbox
     */
    protected function _getInbox()
    {
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
    public function loadByEmail($email)
    {
        return Mage::getModel('newsletter/subscriber')
            ->getCollection()
            ->addFieldToFilter('subscriber_email', $email)
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ->getFirstItem();
    }

}
