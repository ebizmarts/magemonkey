<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_AbandonedCart_Model_EventObserver
{
    public function saveConfig(Varien_Event_Observer $observer)
    {
        if(Mage::app()->getRequest()->getParam('store')) {
            $scope = 'store';
        }
        elseif(Mage::app()->getRequest()->getParam('website')) {
            $scope = 'website';
        }
        else {
            $scope = 'default';
        }

        $store = is_null($observer->getEvent()->getStore()) ? Mage::app()->getDefaultStoreView()->getCode(): $observer->getEvent()->getStore();
        if(!Mage::helper('ebizmarts_mandrill')->useTransactionalService()) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE,false,$scope,$store);
            $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::ENABLE_POPUP,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }

    }

    public function loadCustomer(Varien_Event_Observer $observer){
        if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $quote = $observer->getEvent()->getQuote();
            $action = Mage::app()->getRequest()->getActionName();
            $onCheckout = $action == 'saveOrder' || $action == 'savePayment' || $action == 'saveShippingMethod' || $action == 'saveBilling';
            if (isset($_COOKIE['email']) && $_COOKIE['email'] != 'none' && !$onCheckout) {
                $email = str_replace(' ', '+', $_COOKIE['email']);
                if($quote->getCustomerEmail() != $email){
                    $quote->setCustomerEmail($email);
                    $quote->save();
                }
            }
        }
        return $observer;
    }

}