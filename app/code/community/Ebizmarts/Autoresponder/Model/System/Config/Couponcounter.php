<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 10/27/13
 * Time   : 8:10 AM
 * File   : Pertype.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_System_Config_Couponcounter
{
    public function toOptionArray()
    {
        $options = array(
            array('value'=> Ebizmarts_Autoresponder_Model_Config::COUPON_PER_ORDER, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Per Order')),
            array('value'=> Ebizmarts_Autoresponder_Model_Config::COUPON_GENERAL, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('General'))
        );
        return $options;
    }
}