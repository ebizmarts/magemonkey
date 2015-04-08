<?php

/**
 * Subscriber status config source options model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Model_System_Config_Source_Status
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'subscribed', 'label' => Mage::helper('monkey')->__('Subscribed')),
            array('value' => 'unsubscribed', 'label' => Mage::helper('monkey')->__('Unsubscribed')),
            array('value' => 'cleaned', 'label' => Mage::helper('monkey')->__('Cleaned')),
            array('value' => 'updated', 'label' => Mage::helper('monkey')->__('Updated')),
        );
    }

}