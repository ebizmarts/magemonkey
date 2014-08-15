<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
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