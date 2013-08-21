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
        $store  = is_null($o->getEvent()->getStore()) ? 'default': $o->getEvent()->getStore();
        if(!Mage::helper('mandrill')->useTransactionalService()) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_ACTIVE,false,"default",$store);
            Mage::getConfig()->cleanCache();
        }
        if(!Mage::helper('mandrill')->useTransactionalService()) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE,false,"default",$store);
            Mage::getConfig()->cleanCache();
        }
        if(!Mage::getStoreConfig('customer/address/dob_show')) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_ACTIVE,false,"default",$store);
            Mage::getConfig()->cleanCache();
        }
        if(!Mage::getStoreConfig('customer/address/dob_show',$store)) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_ACTIVE,false,"default",$store);
            Mage::getConfig()->cleanCache();
        }
        if(Mage::getStoreConfig('advanced/modules_disable_output/Mage_Wishlist',$store)) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_ACTIVE,false,"default",$store);
            Mage::getConfig()->cleanCache();
        }
        if(Mage::getStoreConfig('advanced/modules_disable_output/Mage_Review',$store)) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_ACTIVE,false,"default",$store);
            Mage::getConfig()->cleanCache();
        }

    }
}