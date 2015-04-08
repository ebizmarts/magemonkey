<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Model_System_Config_Generaltype
{
    public function toOptionArray()
    {
        $options = array(
            array('value' => Ebizmarts_Autoresponder_Model_Config::TYPE_EACH, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Each')),
            array('value' => Ebizmarts_Autoresponder_Model_Config::TYPE_ONCE, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Only once')),
            array('value' => Ebizmarts_Autoresponder_Model_Config::TYPE_SPECIFIC, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Specific'))
        );
        return $options;
    }
}