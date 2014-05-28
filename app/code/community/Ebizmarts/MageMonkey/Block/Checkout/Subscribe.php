<?php

/**
 * Checkout subscribe checkbox block renderer
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Checkout_Subscribe extends Ebizmarts_MageMonkey_Block_Lists
{
    protected function _construct()
    {
        parent::_construct();

        $key = array(
            'EbizMageMonkeyCheckoutSubscribe',
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getSingleton('customer/session')->isLoggedIn(),
            $this->getQuote()->getCustomer()->getId()
        );

        $this->addData(array(
            'cache_lifetime' => 60 * 60 * 4, // four hours valid
            'cache_tags'     => array('EbizMageMonkey_Checkout_Subscribe'),
            'cache_key'      => implode('_', $key),
        ));
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        /**
         * If you don't want to show the lists in the checkout when the user it's already subscribed.
         * Replace the code below for the condition below
         */

        /*
        $alreadySubscribed = Mage::getModel('newsletter/subscriber')
            ->loadByEmail($this->getQuote()->getCustomerEmail())
            ->isSubscribed();

         if ( !$this->helper('monkey')->canCheckoutSubscribe() OR $alreadySubscribed ) {
         */

        if (!$this->helper('monkey')->canCheckoutSubscribe()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Retrieve current quote object from session
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')
            ->getQuote();
    }

    /**
     * Retrieve from config the status of the checkbox
     *
     * @see Ebizmarts_MageMonkey_Model_System_Config_Source_Checkoutsubscribe
     * @return integer Config value possible values are 0,1,2,3
     */
    public function checkStatus()
    {
        return (int)$this->helper('monkey')->config('checkout_subscribe');
    }
}
