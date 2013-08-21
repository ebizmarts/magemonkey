<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/21/13
 * Time   : 1:50 AM
 * File   : EventObserver.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_AbandonedCart_Model_EventObserver
{
    public function saveConfig(Varien_Event_Observer $o)
    {
        $store  = is_null($o->getEvent()->getStore()) ? 'default': $o->getEvent()->getStore();
        if(!Mage::helper('mandrill')->useTransactionalService()) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE,false,"default",$store);
            Mage::getConfig()->cleanCache();
        }

    }
}