<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/24/13
 * Time   : 5:27 PM
 * File   : EventObserver.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_EventObserver
{
    /**
     * @param Varien_Event_Observer $o
     */
    public function saveConfig(Varien_Event_Observer $o)
    {
        if(Mage::app()->getRequest()->getParam('store')) {
            $scope = 'store';
        }
        elseif(Mage::app()->getRequest()->getParam('website')) {
            $scope = 'website';
        }
        else {
            $scope = "default";
        }
        $store  = is_null($o->getEvent()->getStore()) ? Mage::app()->getDefaultStoreView()->getCode(): $o->getEvent()->getStore();
        if(!Mage::helper('mandrill')->useTransactionalService()) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
        if(!Mage::helper('mandrill')->useTransactionalService()) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
        if(!Mage::getStoreConfig('customer/address/dob_show')) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
        if(!Mage::getStoreConfig('customer/address/dob_show',$store)) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
        if(Mage::getStoreConfig('advanced/modules_disable_output/Mage_Wishlist',$store)) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
        if(Mage::getStoreConfig('advanced/modules_disable_output/Mage_Review',$store)) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
    }
    public function actionAfter(Varien_Event_Observer $o)
    {
        if($o->getEvent()->getControllerAction()->getFullActionName() == 'review_product_post') {
            Mage::dispatchEvent("review_product_post_after", array('request' => $o->getControllerAction()->getRequest()));
        }
    }
    public function reviewProductPostAfter(Varien_Event_Observer $o)
    {
        $params = Mage::app()->getRequest()->getParams();
        if(isset($params['token'])) {
            $token = $params['token'];
            Mage::log($token);
            $data = Mage::getModel('ebizmarts_autoresponder/review')->loadByToken($token);
            $counter = $data->getCounter();
            if($counter < $data->getItems()) {
                $counter++;
                $data->setCounter($counter)->save();
                if($counter == $data->getItems()) {
                    //generate coupon
                }
            }
        }
    }
}