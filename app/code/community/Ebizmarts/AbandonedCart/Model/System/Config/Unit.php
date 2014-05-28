<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_AbandonedCart_Model_System_Config_Unit
{
    public function toOptionArray()
    {
        $options = array(
            array('value'=> Ebizmarts_AbandonedCart_Model_Config::IN_DAYS, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Days')),
            array('value'=> Ebizmarts_AbandonedCart_Model_Config::IN_HOURS, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Hours'))
        );
        return $options;
    }

}