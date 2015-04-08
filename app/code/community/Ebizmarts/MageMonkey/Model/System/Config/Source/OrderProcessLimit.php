<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/18/14
 * Time   : 2:33 PM
 * File   : OrderProcessLimit.php
 * Module : magemonkey
 */
class Ebizmarts_MageMonkey_Model_System_Config_Source_OrderProcessLimit
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 100, 'label' => Mage::helper('monkey')->__('100')),
            array('value' => 200, 'label' => Mage::helper('monkey')->__('200')),
            array('value' => 500, 'label' => Mage::helper('monkey')->__('500')),
            array('value' => 1000, 'label' => Mage::helper('monkey')->__('1000')),
        );
    }
}