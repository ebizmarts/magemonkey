<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Model_System_Config_Yesnovariation
{
    public function toOptionArray()
    {
        $code = Mage::getSingleton('adminhtml/config_data')->getStore();
        $storeId = Mage::getModel('core/store')->load($code)->getId();
        $hasCoupon = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SEND_COUPON, $storeId);
        if ($hasCoupon) {
            $active = -Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MAXTIMES, $storeId);
        } else {
            $active = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MAXTIMES, $storeId);
        }
        $options = array(
            array('value' => 0, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('No')),
            array('value' => ($active +($hasCoupon?-1:1) ), 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Yes'))
        );
        return $options;
    }


}