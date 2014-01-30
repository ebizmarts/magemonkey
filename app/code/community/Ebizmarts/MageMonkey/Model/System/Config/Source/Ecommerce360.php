<?php

/**
 * Ecommerce360 export orders config source options list
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Model_System_Config_Source_Ecommerce360
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('monkey')->__('Referred Orders')),
            array('value' => 2, 'label' => Mage::helper('monkey')->__('All Orders')),
            array('value' => 3, 'label' => Mage::helper('monkey')->__('By Cron depending on the Orders Status')),
            array('value' => 0, 'label' => Mage::helper('monkey')->__('-- Disabled --'))
        );
    }
}