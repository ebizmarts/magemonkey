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
        if (Mage::app()->getRequest()->getParam('store')) {
            $scope = 'store';
        } elseif (Mage::app()->getRequest()->getParam('website')) {
            $scope = 'website';
        } else {
            $scope = 'default';
        }

        $cleanCache = false;
        $store = is_null($observer->getEvent()->getStore()) ? Mage::app()->getDefaultStoreView()->getCode() : $observer->getEvent()->getStore();
        if (!Mage::helper('ebizmarts_mandrill')->useTransactionalService()) {
            $config = new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE, false, $scope, $store);
            $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::ENABLE_POPUP, false, $scope, $store);
            $cleanCache = true;
        }

        if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SEND_COUPON, $store)) {
            $couponActive = '-';
        } else {
            $couponActive = '';
        }

        if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_ACTIVE, $store)) {
            $stores = Mage::app()->getStores();

            foreach ($stores as $_store) {
                $storeId = Mage::app()->getStore($_store)->getId();
                $collection = Mage::getModel('ebizmarts_abandonedcart/abtesting')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => $storeId));
                if (count($collection) == 0) {
                    Mage::getModel('ebizmarts_abandonedcart/abtesting')
                        ->setStoreId($storeId)
                        ->setCurrentStatus(0)
                        ->save();
                }
            }
            //if AB Testing active and its value is different than max number if coupon disabled or different than -max if coupon enabled number change it in order to display the correct settings.
            if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_ACTIVE, $store) != $couponActive . Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MAXTIMES, $store)) {
                if (!$config) {
                    $config = new Mage_Core_Model_Config();
                }
                if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SEND_COUPON, $store)) {
                    $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_ACTIVE, -Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MAXTIMES, $store), $scope, $store);
                } else {
                    $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_ACTIVE, Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MAXTIMES, $store), $scope, $store);
                }
                $message = Mage::helper('ebizmarts_abandonedcart')->__('Displayed options on A/B Testing section have changed. Please verify everything is correctly set.');
                Mage::getSingleton('adminhtml/session')->addWarning($message);
                $cleanCache = true;
            }
        }

        if ($cleanCache) {
            Mage::getConfig()->cleanCache();
        }

    }

    public function loadCustomer(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!Mage::getSingleton('customer/session')->isLoggedIn() && Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ENABLE_POPUP, $quote->getStoreId())) {
            $action = Mage::app()->getRequest()->getActionName();
            $onCheckout = ($action == 'saveOrder' || $action == 'savePayment' || $action == 'saveShippingMethod' || $action == 'saveBilling');
            if (isset($_COOKIE['email']) && $_COOKIE['email'] != 'none' && !$onCheckout) {
                $email = str_replace(' ', '+', $_COOKIE['email']);
                if ($quote->getCustomerEmail() != $email) {
                    $quote->setCustomerEmail($email)
                        ->save();
                }
            }
        }
        return $observer;
    }

}