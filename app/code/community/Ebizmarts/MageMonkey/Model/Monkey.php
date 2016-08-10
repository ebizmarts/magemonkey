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
